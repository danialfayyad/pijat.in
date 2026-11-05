<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerHelpController extends Controller
{
    /**
     * Display Help Center & FAQ for Partners (Mitra).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedCategory = $request->query('category');
        $search = trim($request->query('search', ''));

        $query = Faq::active()->forAudience('mitra');

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->latest()->get();

        $categories = Faq::active()
            ->forAudience('mitra')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $activeTicket = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', [SupportTicket::STATUS_MENUNGGU, SupportTicket::STATUS_BERLANGSUNG])
            ->latest('last_message_at')
            ->first();

        return view('mitra.help.index', [
            'faqs' => $faqs,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'activeTicket' => $activeTicket,
        ]);
    }

    /**
     * Entry point to Live Chat: redirect to active ticket or show portal.
     */
    public function chat(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $activeTicket = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', [SupportTicket::STATUS_MENUNGGU, SupportTicket::STATUS_BERLANGSUNG])
            ->latest('last_message_at')
            ->first();

        if ($activeTicket) {
            return redirect()->route('mitra.help.ticket', $activeTicket);
        }

        $pastTickets = SupportTicket::where('user_id', $user->id)
            ->where('status', SupportTicket::STATUS_SELESAI)
            ->with(['region', 'admin'])
            ->latest('closed_at')
            ->take(5)
            ->get();

        return view('mitra.help.create', [
            'pastTickets' => $pastTickets,
        ]);
    }

    /**
     * Open a new support ticket and send initial message.
     */
    public function storeTicket(Request $request): RedirectResponse
    {
        $user = $request->user();

        $existingActive = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', [SupportTicket::STATUS_MENUNGGU, SupportTicket::STATUS_BERLANGSUNG])
            ->first();

        if ($existingActive) {
            return redirect()->route('mitra.help.ticket', $existingActive)
                ->with('info', 'Anda masih memiliki sesi live chat yang sedang berjalan.');
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $regionId = $user->partnerProfile?->region_id
            ?? Region::where('status', 'aktif')->value('id');

        $ticket = SupportTicket::create([
            'ticket_code' => SupportTicket::generateTicketCode(),
            'user_id' => $user->id,
            'region_id' => $regionId,
            'user_role' => 'mitra',
            'subject' => trim($validated['subject']),
            'status' => SupportTicket::STATUS_MENUNGGU,
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $user->id,
            'sender_role' => 'mitra',
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        try {
            app(NotificationService::class)->notifyNewSupportTicket($ticket);
        } catch (\Throwable $e) {
            // Silently continue if notification fails
        }

        return redirect()->route('mitra.help.ticket', $ticket)
            ->with('success', 'Tiket bantuan berhasil dibuka. Admin wilayah akan segera merespon.');
    }

    /**
     * Show real-time live chat room for a specific ticket.
     */
    public function showTicket(Request $request, SupportTicket $ticket): View
    {
        abort_if($ticket->user_id !== $request->user()->id, 403);

        $ticket->load(['messages.sender', 'region', 'admin']);

        // Mark unread admin messages as read
        $ticket->messages()
            ->where('sender_role', 'admin')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('mitra.help.chat', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Send a partner reply to the support ticket.
     */
    public function replyTicket(Request $request, SupportTicket $ticket): JsonResponse|RedirectResponse
    {
        abort_if($ticket->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $msg = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'sender_role' => 'mitra',
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        $ticket->update([
            'status' => $ticket->status === SupportTicket::STATUS_SELESAI
                ? SupportTicket::STATUS_MENUNGGU
                : $ticket->status,
            'last_message_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $request->user()->name,
                    'sender_role' => 'mitra',
                    'created_at' => $msg->created_at->format('H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Pesan berhasil terkirim.');
    }

    /**
     * Polling endpoint to fetch latest messages in the chat room.
     */
    public function fetchMessages(Request $request, SupportTicket $ticket): JsonResponse
    {
        abort_if($ticket->user_id !== $request->user()->id, 403);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $ticket->messages()
            ->with('sender')
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->get();

        // Mark unread admin messages as read
        $ticket->messages()
            ->where('sender_role', 'admin')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'ticket_status' => $ticket->status,
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender_role === 'admin' ? ($ticket->admin?->name ?? 'Admin Wilayah') : $m->sender?->name,
                'sender_role' => $m->sender_role,
                'created_at' => $m->created_at->format('H:i'),
                'is_me' => $m->sender_id === $request->user()->id,
            ]),
        ]);
    }
}
