<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Services\NotificationService;
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

        $notifications = $query->paginate(15)->withQueryString();

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
     * Mark all notifications as read for current user.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->systemNotifications()->unread()->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
