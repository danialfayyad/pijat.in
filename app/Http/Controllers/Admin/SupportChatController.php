<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportChatController extends Controller
{
    /**
     * Display a listing of regional support chat tickets (Admin Wilayah).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();

        // Scope to admin's assigned active regions, or all active regions for Super Admin
        $regionIds = $admin->isSuperadmin()
            ? Region::active()->pluck('id')->all()
            : $admin->assignedRegions()->where('status', 'aktif')->pluck('regions.id')->all();

        $accessibleRegions = Region::whereIn('id', $regionIds)->orderBy('name')->get();

        $currentStatus = $request->input('status', 'semua');
        $currentRole = $request->input('role', 'semua');
        $selectedRegionId = $request->input('region_id');
        $search = trim($request->input('search', ''));

        $baseQuery = SupportTicket::inRegions($regionIds)->with(['user', 'region', 'admin']);

        // Tab counts
        $counts = [
            'aktif' => (clone $baseQuery)->whereIn('status', [SupportTicket::STATUS_MENUNGGU, SupportTicket::STATUS_BERLANGSUNG])->count(),
            'menunggu' => (clone $baseQuery)->where('status', SupportTicket::STATUS_MENUNGGU)->count(),
            'berlangsung' => (clone $baseQuery)->where('status', SupportTicket::STATUS_BERLANGSUNG)->count(),
            'selesai' => (clone $baseQuery)->where('status', SupportTicket::STATUS_SELESAI)->count(),
            'semua' => (clone $baseQuery)->count(),
        ];

        $query = clone $baseQuery;

        if ($currentStatus === 'aktif') {
            $query->whereIn('status', [SupportTicket::STATUS_MENUNGGU, SupportTicket::STATUS_BERLANGSUNG]);
        } elseif (in_array($currentStatus, [SupportTicket::STATUS_MENUNGGU, SupportTicket::STATUS_BERLANGSUNG, SupportTicket::STATUS_SELESAI], true)) {
            $query->where('status', $currentStatus);
        }

        if ($currentRole !== 'semua' && in_array($currentRole, ['pelanggan', 'mitra'], true)) {
            $query->where('user_role', $currentRole);
        }

        if ($selectedRegionId && in_array((int) $selectedRegionId, $regionIds, true)) {
            $query->where('region_id', (int) $selectedRegionId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->orderByRaw("CASE WHEN status = 'menunggu' THEN 1 WHEN status = 'berlangsung' THEN 2 ELSE 3 END")
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.support-chats.index', [
            'tickets' => $tickets,
            'accessibleRegions' => $accessibleRegions,
            'counts' => $counts,
            'currentStatus' => $currentStatus,
            'currentRole' => $currentRole,
            'selectedRegionId' => $selectedRegionId,
            'search' => $search,
        ]);
    }

    /**
     * Display the live chat room for a support ticket.
     */
    public function show(Request $request, SupportTicket $ticket): View
    {
        $admin = $request->user();
        $this->authorizeAdminAccess($admin, $ticket);

        $ticket->load(['user', 'region', 'admin', 'messages.sender']);

        // Automatically assign admin if ticket is in waiting status
        if ($ticket->status === SupportTicket::STATUS_MENUNGGU) {
            $ticket->update([
                'admin_id' => $admin->id,
                'status' => SupportTicket::STATUS_BERLANGSUNG,
            ]);
        }

        // Mark incoming messages as read
        $ticket->messages()
            ->where('sender_id', '!=', $admin->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('admin.support-chats.show', [
            'ticket' => $ticket,
            'user' => $ticket->user,
        ]);
    }

    /**
     * Send a reply message from the admin in the ticket chat.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        $admin = $request->user();
        $this->authorizeAdminAccess($admin, $ticket);

        if ($ticket->status === SupportTicket::STATUS_SELESAI) {
            return back()->with('error', 'Tiket ini telah ditandai selesai. Buka kembali tiket untuk mengirim pesan.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $msg = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $admin->id,
            'sender_role' => 'admin',
            'message' => trim($validated['message']),
            'is_read' => true,
            'read_at' => now(),
        ]);

        $ticket->update([
            'admin_id' => $admin->id,
            'status' => SupportTicket::STATUS_BERLANGSUNG,
            'last_message_at' => now(),
        ]);

        try {
            app(NotificationService::class)->notifySupportTicketReply($ticket, $msg);
        } catch (\Throwable $e) {
            // Silently continue if notification fails
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $admin->name,
                    'sender_role' => 'admin',
                    'created_at' => $msg->created_at->format('H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Pesan balasan berhasil dikirim.');
    }

    /**
     * Mark a support ticket as resolved / closed.
     */
    public function close(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $admin = $request->user();
        $this->authorizeAdminAccess($admin, $ticket);

        $ticket->update([
            'status' => SupportTicket::STATUS_SELESAI,
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);

        return redirect()->route('admin.support-chats.index')
            ->with('success', "Tiket {$ticket->ticket_code} berhasil diselesaikan dan ditutup.");
    }

    /**
     * Reopen a closed support ticket.
     */
    public function reopen(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $admin = $request->user();
        $this->authorizeAdminAccess($admin, $ticket);

        $ticket->update([
            'status' => SupportTicket::STATUS_BERLANGSUNG,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return back()->with('success', "Tiket {$ticket->ticket_code} dibuka kembali.");
    }

    /**
     * Polling endpoint to fetch latest messages in the chat room.
     */
    public function fetchMessages(Request $request, SupportTicket $ticket): JsonResponse
    {
        $admin = $request->user();
        $this->authorizeAdminAccess($admin, $ticket);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $ticket->messages()
            ->with('sender')
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->get();

        // Mark user messages as read
        $ticket->messages()
            ->where('sender_id', '!=', $admin->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name ?? 'User',
                'sender_role' => $m->sender_role,
                'is_admin' => $m->sender_role === 'admin',
                'message' => $m->message,
                'created_at' => $m->created_at->format('H:i'),
            ]),
            'ticket_status' => $ticket->status,
        ]);
    }

    /**
     * Authorize that the admin has access to the ticket's region.
     */
    protected function authorizeAdminAccess(User $admin, SupportTicket $ticket): void
    {
        if ($admin->isSuperadmin()) {
            return;
        }

        if (! $admin->canAccessRegion($ticket->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses untuk menangani tiket bantuan di luar wilayah penugasan Anda.');
        }
    }
}
