<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    /**
     * Display list of notifications and active task queue summary for this admin (ADM-14).
     */
    public function index(Request $request, NotificationService $notificationService): View
    {
        $admin = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $admin->systemNotifications()->latest();

        if ($filter === 'unread') {
            $query->unread();
        }

        $notifications = $query->paginate(10)->withQueryString();

        $queueSummary = $notificationService->getAdminQueueSummary($admin);
        $unreadCount = $admin->systemNotifications()->unread()->count();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'queueSummary' => $queueSummary,
            'unreadCount' => $unreadCount,
            'currentFilter' => $filter,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, SystemNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->markAsRead();

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Mark notification as read and navigate directly to its action URL.
     */
    public function navigate(Request $request, SystemNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->markAsRead();

        return redirect()->to($notification->action_url ?? route('admin.notifications.index'));
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->systemNotifications()->unread()->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    /**
     * Polling endpoint to check for latest unread notifications for desktop OS alerts.
     */
    public function checkUnread(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['unread_count' => 0, 'latest' => null]);
        }

        $unreadCount = $user->systemNotifications()->unread()->count();
        $latest = $user->systemNotifications()->unread()->latest()->first();

        return response()->json([
            'unread_count' => $unreadCount,
            'latest' => $latest ? [
                'id' => $latest->id,
                'title' => $latest->title,
                'body' => $latest->body,
                'action_url' => $latest->action_url ?? route('admin.notifications.index'),
                'created_at' => $latest->created_at->toIso8601String(),
            ] : null,
        ]);
    }
}
