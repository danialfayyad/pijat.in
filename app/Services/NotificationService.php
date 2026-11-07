<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\PenaltyAppeal;
use App\Models\PushSubscription;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Carbon;
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
        return config('services.vapid.public_key', env('VAPID_PUBLIC_KEY', 'BABvODv5LBkv3E_oOkCsH6ntg108NeciREk2_RSES_QCZ9iKbM6b1XqcO628KjLgteCcqMzKFYKOOkutMQGiEys'));
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
        // 1. Deduplication guard: ignore duplicate identical notification created within the last 5 seconds
        $recentDuplicate = SystemNotification::where('user_id', $user->id)
            ->where('event_code', $eventCode)
            ->where('title', $title)
            ->where('created_at', '>=', Carbon::now()->subSeconds(5))
            ->first();

        if ($recentDuplicate) {
            return $recentDuplicate;
        }

        // 2. Create In-App System Notification record
        $notification = SystemNotification::create([
            'user_id' => $user->id,
            'event_code' => $eventCode,
            'title' => $title,
            'body' => $body,
            'priority' => $priority,
            'data' => $data,
        ]);

        // 3. Dispatch Web Push to all registered device subscriptions for this user
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

    /**
     * Notify regional admins when a new support ticket is created.
     *
     * @return Collection<int, SystemNotification>
     */
    public function notifyNewSupportTicket(SupportTicket $ticket): Collection
    {
        $roleLabel = ucfirst($ticket->user_role);
        $userName = $ticket->user?->name ?? 'Pengguna';

        return $this->sendToRegionAdmins(
            $ticket->region_id,
            'NOTIF_ADMIN_QUEUE',
            "Pesan Bantuan Baru: {$roleLabel} {$userName}",
            "Tiket #{$ticket->ticket_code}: {$ticket->subject}",
            [
                'ticket_id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'action_url' => route('admin.support-chats.show', $ticket),
            ],
            'high'
        );
    }

    /**
     * Notify customer or partner when an admin replies to their support ticket.
     */
    public function notifySupportTicketReply(SupportTicket $ticket, SupportMessage $message): ?SystemNotification
    {
        if (! $ticket->user) {
            return null;
        }

        $actionUrl = $ticket->user_role === 'mitra'
            ? route('mitra.help.ticket', $ticket)
            : route('pelanggan.help.ticket', $ticket);

        return $this->sendToUser(
            $ticket->user,
            'NOTIF_SUPPORT_REPLY',
            'Balasan Bantuan dari Admin Wilayah',
            "Admin membalas tiket #{$ticket->ticket_code}: {$ticket->subject}",
            [
                'ticket_id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'action_url' => $actionUrl,
            ],
            'high'
        );
    }

    /**
     * Notify regional admins when a partner submits a withdrawal request (MIT-16).
     *
     * @return Collection<int, SystemNotification>
     */
    public function notifyWithdrawalRequested(WithdrawalRequest $withdrawal): Collection
    {
        $regionId = $withdrawal->region_id ?? $withdrawal->user?->partnerProfile?->region_id ?? $withdrawal->user?->region_id;

        if (! $regionId) {
            return collect();
        }

        $isCustomer = $withdrawal->user?->isPelanggan();
        $userRoleLabel = $isCustomer ? 'Pelanggan' : 'Mitra';
        $eventCode = $isCustomer ? 'customer_withdrawal_pending' : 'partner_withdrawal_pending';
        $userName = $withdrawal->user?->name ?? $userRoleLabel;

        return $this->sendToRegionAdmins(
            $regionId,
            $eventCode,
            "Antrean Penarikan Dana {$userRoleLabel}",
            "{$userRoleLabel} {$userName} mengajukan penarikan dana sebesar Rp ".number_format($withdrawal->amount, 0, ',', '.')." (#{$withdrawal->withdrawal_code})",
            [
                'withdrawal_id' => $withdrawal->id,
                'withdrawal_code' => $withdrawal->withdrawal_code,
                'action_url' => route('admin.finance.index'),
            ],
            'high'
        );
    }

    /**
     * Notify regional admins when a new complaint is filed (CUS-19, MIT-19).
     *
     * @return Collection<int, SystemNotification>
     */
    public function notifyNewComplaint(Complaint $complaint): Collection
    {
        return $this->sendToRegionAdmins(
            $complaint->region_id,
            'new_complaint',
            'Laporan Aduan Baru Masuk',
            "Tiket aduan #{$complaint->complaint_code}: {$complaint->title}",
            [
                'complaint_id' => $complaint->id,
                'complaint_code' => $complaint->complaint_code,
                'action_url' => route('admin.complaints.index'),
            ],
            $complaint->priority === 'darurat' ? 'high' : 'normal'
        );
    }

    /**
     * Notify regional admins when a partner submits verification or profile change (MIT-01, MIT-20).
     *
     * @return Collection<int, SystemNotification>
     */
    public function notifyPartnerVerificationSubmitted(User $user): Collection
    {
        $regionId = $user->partnerProfile?->region_id ?? $user->region_id;

        if (! $regionId) {
            return collect();
        }

        return $this->sendToRegionAdmins(
            $regionId,
            'partner_verification_submitted',
            'Pendaftaran Mitra Baru Menunggu Verifikasi',
            "Mitra {$user->name} telah memperbarui data dan membutuhkan verifikasi admin wilayah.",
            [
                'mitra_id' => $user->id,
                'action_url' => route('admin.mitra.index'),
            ],
            'high'
        );
    }

    /**
     * Notify regional admins when a penalty appeal is submitted (MIT-18, CUS-20).
     *
     * @return Collection<int, SystemNotification>
     */
    public function notifyNewPenaltyAppeal(PenaltyAppeal $appeal): Collection
    {
        $userName = $appeal->user?->name ?? 'Pengguna';

        return $this->sendToRegionAdmins(
            $appeal->region_id,
            'new_penalty_appeal',
            'Pengajuan Banding Sanksi Poin Baru',
            "Pengajuan banding sanksi sebesar {$appeal->points_to_reverse} poin dari mitra {$userName}.",
            [
                'appeal_id' => $appeal->id,
                'action_url' => route('admin.appeals.index'),
            ],
            'high'
        );
    }

    /**
     * Notify customer when order is successfully created.
     */
    public function notifyOrderCreated(Order $order): ?SystemNotification
    {
        if (! $order->customer) {
            return null;
        }

        return $this->sendToUser(
            $order->customer,
            'order_created',
            'Pesanan Berhasil Dibuat',
            "Pesanan #{$order->order_code} ({$order->service_name}) berhasil dibuat. Sistem sedang memproses pencarian mitra.",
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'action_url' => route('pelanggan.orders.show', $order),
                'action_label' => 'Lihat Pesanan',
            ],
            'normal'
        );
    }

    /**
     * Notify partner when an instant order offer is dispatched to them (MIT-05 & SYS-01).
     */
    public function notifyInstantOrderOffered(Order $order, User $partner): ?SystemNotification
    {
        $formattedPrice = number_format((float) $order->total_price, 0, ',', '.');

        return $this->sendToUser(
            $partner,
            'instant_order_offered',
            'Tawaran Pesanan Baru Masuk! ⚡',
            "Pesanan instan #{$order->order_code} ({$order->service_name}) - Rp {$formattedPrice}. Segera konfirmasi dalam 60 detik!",
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'action_url' => route('mitra.dashboard'),
                'action_label' => 'Buka Dashboard',
            ],
            'high'
        );
    }

    /**
     * Notify customer and partner when order is confirmed / taken.
     */
    public function notifyOrderConfirmed(Order $order): void
    {
        $order->loadMissing(['customer', 'mitra']);

        // 1. Notify Customer
        if ($order->customer) {
            $partnerName = $order->mitra?->name ?? 'Terapis';
            $this->sendToUser(
                $order->customer,
                'order_confirmed',
                'Terapis Ditemukan! 🎉',
                "Terapis {$partnerName} telah menerima pesanan #{$order->order_code} Anda dan bersiap melayani.",
                [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'action_url' => route('pelanggan.orders.show', $order),
                    'action_label' => 'Pantau Pesanan',
                ],
                'high'
            );
        }

        // 2. Notify Partner
        if ($order->mitra) {
            $this->sendToUser(
                $order->mitra,
                'order_taken',
                'Pesanan Berhasil Diterima ✓',
                "Anda telah menerima pesanan #{$order->order_code} ({$order->service_name}). Silakan cek rincian dan jadwal layanan.",
                [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'action_url' => route('mitra.orders.show', $order),
                    'action_label' => 'Rincian Tugas',
                ],
                'high'
            );
        }
    }

    /**
     * Notify customer when order status progresses (Menuju Lokasi, Tiba, Berlangsung, Selesai).
     */
    public function notifyOrderStatusChanged(Order $order, OrderStatus $newStatus): ?SystemNotification
    {
        $order->loadMissing(['customer', 'mitra']);
        if (! $order->customer) {
            return null;
        }

        $partnerName = $order->mitra?->name ?? 'Terapis';

        $statusPayloads = [
            OrderStatus::MenujuLokasi->value => [
                'event_code' => 'order_on_the_way',
                'title' => 'Terapis Menuju Lokasi 🛵',
                'body' => "Terapis {$partnerName} sedang dalam perjalanan ke lokasi Anda.",
            ],
            OrderStatus::Tiba->value => [
                'event_code' => 'order_arrived',
                'title' => 'Terapis Telah Tiba 📍',
                'body' => "Terapis {$partnerName} sudah sampai di alamat tujuan Anda.",
            ],
            OrderStatus::SedangBerlangsung->value => [
                'event_code' => 'order_in_progress',
                'title' => 'Sesi Pijat Dimulai 💆',
                'body' => "Layanan #{$order->order_code} sedang berlangsung. Selamat menikmati!",
            ],
            OrderStatus::Selesai->value => [
                'event_code' => 'order_completed',
                'title' => 'Layanan Telah Selesai ✨',
                'body' => 'Sesi pijat telah selesai. Berikan penilaian bintang untuk terapis Anda!',
            ],
        ];

        if (! isset($statusPayloads[$newStatus->value])) {
            return null;
        }

        $config = $statusPayloads[$newStatus->value];

        return $this->sendToUser(
            $order->customer,
            $config['event_code'],
            $config['title'],
            $config['body'],
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'action_url' => route('pelanggan.orders.show', $order),
                'action_label' => 'Lihat Pesanan',
            ],
            'high'
        );
    }

    /**
     * Notify parties when an order is cancelled.
     */
    public function notifyOrderCancelled(Order $order, ?string $reason = null, ?string $cancelledByRole = null): void
    {
        $order->loadMissing(['customer', 'mitra']);
        $reasonText = $reason ? " Alasan: {$reason}" : '';

        // Notify customer if not cancelled by customer
        if ($order->customer && $cancelledByRole !== 'pelanggan') {
            $this->sendToUser(
                $order->customer,
                'order_cancelled',
                'Pesanan Dibatalkan',
                "Pesanan #{$order->order_code} telah dibatalkan.{$reasonText}",
                [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'action_url' => route('pelanggan.orders.show', $order),
                    'action_label' => 'Rincian Pesanan',
                ],
                'high'
            );
        }

        // Notify partner if partner was assigned and cancellation was not by partner
        if ($order->mitra && $cancelledByRole !== 'mitra') {
            $this->sendToUser(
                $order->mitra,
                'order_cancelled',
                'Pesanan Dibatalkan',
                "Pesanan #{$order->order_code} telah dibatalkan oleh {$cancelledByRole}.{$reasonText}",
                [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'action_url' => route('mitra.orders.my-schedule'),
                    'action_label' => 'Jadwal Saya',
                ],
                'high'
            );
        }
    }

    /**
     * Delete notifications older than specified retention days (default 30 days / 1 month).
     */
    public function pruneOldNotifications(int $days = 30): int
    {
        $cutoff = Carbon::now()->subDays($days);

        return SystemNotification::where('created_at', '<', $cutoff)->delete();
    }
}
