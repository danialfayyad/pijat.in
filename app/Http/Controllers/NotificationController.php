<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display or return JSON list of notifications for the authenticated user.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $user->systemNotifications()->latest();

        if ($filter === 'unread') {
            $query->unread();
        }

        $unreadCount = $user->systemNotifications()->unread()->count();

        if ($request->wantsJson()) {
            $notifications = $query->take(30)->get()->map(function (SystemNotification $n) {
                return [
                    'id' => $n->id,
                    'event_code' => $n->event_code,
                    'title' => $n->title,
                    'body' => $n->body,
                    'priority' => $n->priority,
                    'read' => $n->read_at !== null,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at_human' => $n->created_at->diffForHumans(),
                    'action_url' => $n->action_url,
                    'action_label' => $n->action_label,
                ];
            });

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        $notifications = $query->paginate(20)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'currentFilter' => $filter,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, SystemNotification $notification): RedirectResponse|JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->systemNotifications()->unread()->count(),
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    /**
     * Mark all unread notifications as read for current user.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->systemNotifications()->unread()->update([
            'read_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
