<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\PenaltyAppeal;
use App\Models\PushSubscription;
use App\Models\Region;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * VAPID Public Key for Web Push (SYS-06, CUS-21, MIT-21).
     */
    public function getVapidPublicKey(): string
    {
        return config('services.vapid.public_key', env('VAPID_PUBLIC_KEY', 'BKp5T8WcZ9-MockVapidPublicKeyForWebPushInDevelopmentAndTesting-1234567890abcdef'));
    }

    /**
     * Register or update a user's Web Push subscription.
     *
     * @param  array{endpoint: string, public_key?: ?string, auth_token?: ?string, content_encoding?: ?string, user_agent?: ?string}  $data
     */
    public function subscribe(User $user, array $data): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            [
                'endpoint' => $data['endpoint'],
            ],
            [
                'user_id' => $user->id,
                'public_key' => $data['public_key'] ?? null,
                'auth_token' => $data['auth_token'] ?? null,
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
                'user_agent' => $data['user_agent'] ?? request()->header('User-Agent'),
            ]
        );
    }

    /**
     * Remove a Web Push subscription.
     */
    public function unsubscribe(User $user, string $endpoint): bool
    {
        return (bool) $user->pushSubscriptions()->where('endpoint', $endpoint)->delete();
    }

    /**
     * Send in-app and Web Push notification to a specific user.
     *
     * @param  array<string, mixed>  $data
     */
    public function sendToUser(
        User $user,
        string $eventCode,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'normal'
    ): SystemNotification {
        // 1. Create In-App System Notification record
        $notification = SystemNotification::create([
            'user_id' => $user->id,
            'event_code' => $eventCode,
            'title' => $title,
            'body' => $body,
            'priority' => $priority,
            'data' => $data,
        ]);

        // 2. Dispatch Web Push to all registered device subscriptions for this user
        $subscriptions = $user->pushSubscriptions;
        foreach ($subscriptions as $subscription) {
            $this->dispatchWebPushPayload($subscription, [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/icons/icon-192x192.png',
                    'badge' => '/icons/badge-72x72.png',
                    'vibrate' => $priority === 'high' ? [200, 100, 200, 100, 200] : [100, 50, 100],
                    'tag' => "event_{$eventCode}_{$notification->id}",
                    'data' => array_merge($data, [
                        'notification_id' => $notification->id,
                        'event_code' => $eventCode,
                        'priority' => $priority,
                    ]),
                ],
            ]);
        }

        return $notification;
    }

    /**
     * Send strictly isolated notification to all admins assigned to a specific region (SYS-06).
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, SystemNotification>
     */
    public function sendToRegionAdmins(
        int $regionId,
        string $eventCode,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'normal'
    ): Collection {
        // Strict Scoping: Select ONLY admins assigned to this region
        $assignedAdmins = User::where('role', Role::Admin)
            ->whereHas('assignedRegions', function ($q) use ($regionId) {
                $q->where('regions.id', $regionId)
                    ->where('regions.status', 'aktif');
            })
            ->get();

        $notifications = collect();
        $notificationData = array_merge($data, ['region_id' => $regionId]);

        foreach ($assignedAdmins as $admin) {
            $notifications->push(
                $this->sendToUser($admin, $eventCode, $title, $body, $notificationData, $priority)
            );
        }

        return $notifications;
    }

    /**
     * Dispatch Web Push payload to browser endpoint.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchWebPushPayload(PushSubscription $subscription, array $payload): void
    {
        try {
            // In testing or without a live remote push service, log or simulate payload transmission
            if (app()->environment('testing')) {
                // In test suite, we record the dispatch
                return;
            }

            Http::timeout(3)
                ->withHeaders([
                    'TTL' => '86400',
                    'Urgency' => ($payload['notification']['data']['priority'] ?? 'normal') === 'high' ? 'high' : 'normal',
                ])
                ->post($subscription->endpoint, $payload);
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim Web Push ke endpoint {$subscription->endpoint}: {$e->getMessage()}");
        }
    }

    /**
     * Summarize pending task queues for an admin according to assigned regions (ADM-14).
     *
     * @return array{pending_verifications: int, open_complaints: int, pending_appeals: int, total_tasks: int}
     */
    public function getAdminQueueSummary(User $admin): array
    {
        if ($admin->isSuperadmin()) {
            $regionIds = Region::active()->pluck('id')->all();
        } else {
            $regionIds = $admin->assignedRegions()->where('status', 'aktif')->pluck('regions.id')->all();
        }

        if (empty($regionIds)) {
            return [
                'pending_verifications' => 0,
                'open_complaints' => 0,
                'pending_appeals' => 0,
                'total_tasks' => 0,
            ];
        }

        $pendingVerifications = User::where('role', Role::Mitra)
            ->where('status', UserStatus::MenungguVerifikasi)
            ->where(function ($query) use ($regionIds) {
                $query->whereIn('region_id', $regionIds)
                    ->orWhereHas('partnerProfile', function ($sub) use ($regionIds) {
                        $sub->whereIn('region_id', $regionIds);
                    });
            })
            ->count();

        $openComplaints = Complaint::whereIn('region_id', $regionIds)
            ->whereIn('status', ['open', 'investigating'])
            ->count();

        $pendingAppeals = PenaltyAppeal::whereIn('region_id', $regionIds)
            ->where('status', 'pending')
            ->count();

        return [
            'pending_verifications' => $pendingVerifications,
            'open_complaints' => $openComplaints,
            'pending_appeals' => $pendingAppeals,
            'total_tasks' => $pendingVerifications + $openComplaints + $pendingAppeals,
        ];
    }
}
