<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderDispatch;
use App\Models\OrderStatusHistory;
use App\Models\TopupInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderCancellationService
{
    public function __construct(
        protected WalletService $walletService,
        protected PenaltyService $penaltyService
    ) {}

    /**
     * Cancel an order requested by the customer according to Rule Matrix 4.7 (CUS-12).
     *
     * @return array{refund_amount: float, deduction_amount: float, message: string}
     */
    public function cancelByCustomer(Order $order, User $customer, ?string $reason = null): array
    {
        if ($order->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Anda bukan pemilik pesanan ini.');
        }

        if ($order->status->isTerminal()) {
            throw new InvalidArgumentException('Pesanan ini sudah selesai atau telah dibatalkan sebelumnya.');
        }

        $reason = trim((string) ($reason ?: 'Dibatalkan oleh pelanggan'));

        return DB::transaction(function () use ($order, $customer, $reason) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();
            $status = $lockedOrder->status;
            $refundAmount = 0.0;
            $deductionAmount = 0.0;

            if ($status === OrderStatus::MenungguPembayaran) {
                // Belum dibayar, tidak ada refund
                $refundAmount = 0.0;
                $deductionAmount = 0.0;
            } elseif ($status === OrderStatus::MencariMitra) {
                // Masih mencari mitra: refund 100%
                $refundAmount = (float) $lockedOrder->total_price;
                $deductionAmount = 0.0;
            } elseif ($status === OrderStatus::Dijadwalkan) {
                // Pesanan terjadwal
                $scheduledTime = $lockedOrder->scheduled_start_time ? Carbon::parse($lockedOrder->scheduled_start_time) : null;
                $hoursBefore = $scheduledTime ? Carbon::now()->diffInHours($scheduledTime, false) : 999;

                if ($hoursBefore >= 3) {
                    // >= 3 jam sebelum jadwal: refund 100%
                    $refundAmount = (float) $lockedOrder->total_price;
                    $deductionAmount = 0.0;
                } else {
                    // < 3 jam sebelum jadwal: potongan 50%, refund 50%
                    $deductionAmount = round((float) $lockedOrder->total_price * 0.50, 2);
                    $refundAmount = round((float) $lockedOrder->total_price - $deductionAmount, 2);

                    // Berikan kompensasi ke mitra jika pesanan sudah diambil
                    if ($lockedOrder->mitra_id) {
                        $mitra = User::find($lockedOrder->mitra_id);
                        if ($mitra) {
                            $this->walletService->credit(
                                $mitra,
                                $deductionAmount,
                                'partner_compensation',
                                (string) $lockedOrder->id,
                                "Kompensasi 50% pembatalan mendadak pesanan {$lockedOrder->order_code}",
                                $lockedOrder->region_id
                            );
                        }
                    }
                }
            } elseif ($status === OrderStatus::MenujuLokasi) {
                // Diperbolehkan batal hanya jika mitra terlambat > 30 menit dari waktu estimasi
                $otwHistory = $lockedOrder->statusHistories()
                    ->where('to_status', OrderStatus::MenujuLokasi->value)
                    ->latest()
                    ->first();

                // Standar estimasi perjalanan rata-rata 30 menit + toleransi 30 menit = total 60 menit
                $isLateOver30Mins = $otwHistory && Carbon::now()->diffInMinutes($otwHistory->created_at) > 60;

                if (! $isLateOver30Mins) {
                    throw new InvalidArgumentException('Pesanan tidak dapat dibatalkan karena terapis sedang dalam perjalanan menuju lokasi Anda.');
                }

                // Terlambat parah: refund 100% dan penalti mitra
                $refundAmount = (float) $lockedOrder->total_price;
                $deductionAmount = 0.0;

                if ($lockedOrder->mitra_id) {
                    $mitra = User::find($lockedOrder->mitra_id);
                    if ($mitra) {
                        $this->penaltyService->applyViolation(
                            $mitra,
                            15,
                            "Keterlambatan parah (> 30 menit dari estimasi) pada pesanan {$lockedOrder->order_code}",
                            null,
                            $customer,
                            $lockedOrder
                        );
                    }
                }
            } else {
                throw new InvalidArgumentException('Pesanan pada status ini tidak dapat dibatalkan mandiri.');
            }

            // Eksekusi refund jika ada dana yang harus dikembalikan
            if ($refundAmount > 0) {
                $this->walletService->credit(
                    $customer,
                    $refundAmount,
                    'order_refund',
                    (string) $lockedOrder->id,
                    "Refund pembatalan pesanan {$lockedOrder->order_code} (".($deductionAmount > 0 ? 'Potongan 50%' : '100%').')',
                    $lockedOrder->region_id
                );
            }

            // Expire dispatches if instant
            if ($lockedOrder->order_type === OrderType::Instan) {
                OrderDispatch::where('order_id', $lockedOrder->id)
                    ->where('status', OrderDispatch::STATUS_OFFERED)
                    ->update(['status' => OrderDispatch::STATUS_EXPIRED]);
            }

            $lockedOrder->update([
                'status' => OrderStatus::Dibatalkan,
                'cancellation_reason' => $reason,
                'cancelled_by_id' => $customer->id,
                'cancelled_by_role' => 'pelanggan',
                'mitra_id' => null,
            ]);

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $status->value,
                'to_status' => OrderStatus::Dibatalkan->value,
                'actor_id' => $customer->id,
                'actor_role' => 'pelanggan',
                'note' => $reason.($deductionAmount > 0 ? ' (Dikenakan potongan denda 50%: Rp '.number_format($deductionAmount, 0, ',', '.').')' : ' (Refund 100%)'),
            ]);

            $message = $deductionAmount > 0
                ? 'Pesanan berhasil dibatalkan. Pengembalian dana 50% (Rp '.number_format($refundAmount, 0, ',', '.').') telah masuk ke saldo dompet Anda.'
                : 'Pesanan berhasil dibatalkan. Pengembalian dana 100% (Rp '.number_format($refundAmount, 0, ',', '.').') telah masuk ke saldo dompet Anda.';

            return [
                'refund_amount' => $refundAmount,
                'deduction_amount' => $deductionAmount,
                'message' => $message,
            ];
        });
    }

    /**
     * Reschedule a scheduled order requested by the customer (CUS-13).
     */
    public function rescheduleByCustomer(Order $order, User $customer, Carbon|string $newScheduleTime): Order
    {
        if ($order->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Anda bukan pemilik pesanan ini.');
        }

        if ($order->status !== OrderStatus::Dijadwalkan) {
            throw new InvalidArgumentException('Reschedule hanya tersedia untuk pesanan terjadwal berstatus Dijadwalkan.');
        }

        if ($order->reschedule_count >= 2) {
            throw new InvalidArgumentException('Batas maksimal reschedule telah tercapai (maksimal 2 kali per pesanan).');
        }

        $currentSchedule = $order->scheduled_start_time ? Carbon::parse($order->scheduled_start_time) : null;
        if (! $currentSchedule || Carbon::now()->diffInHours($currentSchedule, false) < 3) {
            throw new InvalidArgumentException('Reschedule hanya dapat diajukan minimal 3 jam sebelum jadwal layanan.');
        }

        // Parse new schedule time
        $region = $order->region;
        $tz = $region?->timezone ?? 'Asia/Jakarta';
        $newTime = is_string($newScheduleTime) ? Carbon::parse($newScheduleTime, $tz) : $newScheduleTime->copy()->setTimezone($tz);

        $nowInRegion = Carbon::now($tz);
        $minAllowed = $nowInRegion->copy()->addHours(3);
        $maxAllowed = $nowInRegion->copy()->addDays(7)->endOfDay();

        if ($newTime->lt($minAllowed)) {
            throw new InvalidArgumentException('Jadwal baru harus minimal 3 jam dari waktu saat ini.');
        }

        if ($newTime->gt($maxAllowed)) {
            throw new InvalidArgumentException('Jadwal baru maksimal 7 hari ke depan.');
        }

        $hour = (int) $newTime->format('H');
        if ($hour < 8 || $hour >= 21) {
            throw new InvalidArgumentException('Layanan hanya tersedia antara pukul 08:00 hingga 21:00.');
        }

        return DB::transaction(function () use ($order, $customer, $newTime) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();
            $newScheduleUtc = $newTime->copy()->utc();

            $previousCount = $lockedOrder->reschedule_count;
            $newCount = $previousCount + 1;

            $lockedOrder->update([
                'scheduled_start_time' => $newScheduleUtc,
                'reschedule_count' => $newCount,
                'mitra_id' => null,
                'status' => OrderStatus::MencariMitra, // Ditempatkan kembali ke bursa terbuka
            ]);

            $newFormatted = $newTime->translatedFormat('d M Y, H:i').' '.$order->timezone_abbr;

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => OrderStatus::Dijadwalkan->value,
                'to_status' => OrderStatus::MencariMitra->value,
                'actor_id' => $customer->id,
                'actor_role' => 'pelanggan',
                'note' => "Reschedule ke-{$newCount} oleh pelanggan ke waktu {$newFormatted}. Pesanan kembali dipublikasikan ke bursa jadwal mitra.",
            ]);

            return $lockedOrder->fresh();
        });
    }

    /**
     * Cancel order by partner (MIT-11, MIT-12).
     *
     * @param  string  $type  regular|customer_not_present|gender_mismatch|medical_condition
     * @return array{status: OrderStatus, message: string}
     */
    public function cancelByMitra(Order $order, User $mitra, string $reason, string $type = 'regular'): array
    {
        if ($order->mitra_id !== $mitra->id) {
            throw new InvalidArgumentException('Anda bukan mitra penanggung jawab untuk pesanan ini.');
        }

        if ($order->status->isTerminal()) {
            throw new InvalidArgumentException('Pesanan sudah selesai atau dibatalkan sebelumnya.');
        }

        $reason = trim($reason);
        if (empty($reason)) {
            throw new InvalidArgumentException('Alasan pembatalan wajib diisi.');
        }

        return DB::transaction(function () use ($order, $mitra, $reason, $type) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();
            $currentStatus = $lockedOrder->status;
            $customer = $lockedOrder->customer;

            if ($type === 'regular') {
                // MIT-12: Pembatalan sepihak oleh mitra
                // Mitra terkena penalti poin (15 poin) per DEC-12
                $this->penaltyService->applyViolation(
                    $mitra,
                    15,
                    "Pembatalan sepihak pesanan {$lockedOrder->order_code} oleh mitra: {$reason}",
                    null,
                    $mitra,
                    $lockedOrder
                );

                // Pesanan dilempar ulang ke bursa pencarian
                $lockedOrder->update([
                    'mitra_id' => null,
                    'status' => OrderStatus::MencariMitra,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => $currentStatus->value,
                    'to_status' => OrderStatus::MencariMitra->value,
                    'actor_id' => $mitra->id,
                    'actor_role' => 'mitra',
                    'note' => "Pembatalan sepihak oleh mitra ({$reason}). Poin sanksi diterapkan. Pesanan dikembalikan ke bursa.",
                ]);

                return [
                    'status' => OrderStatus::MencariMitra,
                    'message' => 'Pesanan berhasil dibatalkan dan dikembalikan ke bursa. Poin sanksi telah dicatat pada profil Anda.',
                ];
            }

            // MIT-11: Pembatalan Khusus
            if ($type === 'customer_not_present') {
                if ($currentStatus !== OrderStatus::Tiba) {
                    throw new InvalidArgumentException('Opsi pelanggan tidak ada hanya dapat dipilih setelah status Tiba.');
                }

                // Pelanggan kena potongan 50%, mitra dapat kompensasi 50%
                $deduction = round((float) $lockedOrder->total_price * 0.50, 2);
                $refund = round((float) $lockedOrder->total_price - $deduction, 2);

                if ($refund > 0 && $customer) {
                    $this->walletService->credit(
                        $customer,
                        $refund,
                        'order_refund',
                        (string) $lockedOrder->id,
                        "Refund 50% pembatalan pelanggan tidak ada di lokasi ({$lockedOrder->order_code})",
                        $lockedOrder->region_id
                    );
                }

                $this->walletService->credit(
                    $mitra,
                    $deduction,
                    'partner_compensation',
                    (string) $lockedOrder->id,
                    "Kompensasi transport pelanggan tidak di lokasi ({$lockedOrder->order_code})",
                    $lockedOrder->region_id
                );

                // Pelanggan diberi penalti 15 poin karena tidak hadir di lokasi
                if ($customer) {
                    $this->penaltyService->applyViolation(
                        $customer,
                        15,
                        "Pelanggan tidak hadir di lokasi layanan pada pesanan {$lockedOrder->order_code}",
                        null,
                        $mitra,
                        $lockedOrder
                    );
                }

                $lockedOrder->update([
                    'status' => OrderStatus::Dibatalkan,
                    'cancellation_reason' => 'Pelanggan tidak ada di lokasi setelah ditunggu: '.$reason,
                    'cancelled_by_id' => $mitra->id,
                    'cancelled_by_role' => 'mitra',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => $currentStatus->value,
                    'to_status' => OrderStatus::Dibatalkan->value,
                    'actor_id' => $mitra->id,
                    'actor_role' => 'mitra',
                    'note' => 'Pembatalan khusus: Pelanggan tidak ada di lokasi. Mitra menerima kompensasi Rp '.number_format($deduction, 0, ',', '.'),
                ]);

                return [
                    'status' => OrderStatus::Dibatalkan,
                    'message' => 'Pesanan dibatalkan karena pelanggan tidak hadir. Kompensasi transport telah dikreditkan ke dompet Anda.',
                ];
            }

            if ($type === 'gender_mismatch') {
                if ($currentStatus !== OrderStatus::Tiba) {
                    throw new InvalidArgumentException('Opsi ketidaksesuaian gender hanya dapat diajukan setelah tiba di lokasi.');
                }

                $deduction = round((float) $lockedOrder->total_price * 0.50, 2);
                $refund = round((float) $lockedOrder->total_price - $deduction, 2);

                if ($refund > 0 && $customer) {
                    $this->walletService->credit(
                        $customer,
                        $refund,
                        'order_refund',
                        (string) $lockedOrder->id,
                        "Refund 50% pembatalan ketidaksesuaian gender penerima ({$lockedOrder->order_code})",
                        $lockedOrder->region_id
                    );
                }

                $this->walletService->credit(
                    $mitra,
                    $deduction,
                    'partner_compensation',
                    (string) $lockedOrder->id,
                    "Kompensasi pembatalan ketidaksesuaian gender penerima ({$lockedOrder->order_code})",
                    $lockedOrder->region_id
                );

                if ($customer) {
                    $this->penaltyService->applyViolation(
                        $customer,
                        15,
                        "Ketidaksesuaian gender penerima layanan di lapangan pada pesanan {$lockedOrder->order_code}",
                        null,
                        $mitra,
                        $lockedOrder
                    );
                }

                $lockedOrder->update([
                    'status' => OrderStatus::Dibatalkan,
                    'cancellation_reason' => 'Ketidaksesuaian gender penerima: '.$reason,
                    'cancelled_by_id' => $mitra->id,
                    'cancelled_by_role' => 'mitra',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => $currentStatus->value,
                    'to_status' => OrderStatus::Dibatalkan->value,
                    'actor_id' => $mitra->id,
                    'actor_role' => 'mitra',
                    'note' => 'Pembatalan khusus: Gender penerima tidak sesuai. Mitra bebas sanksi dan menerima kompensasi transport.',
                ]);

                return [
                    'status' => OrderStatus::Dibatalkan,
                    'message' => 'Pesanan dibatalkan karena gender penerima tidak sesuai. Anda bebas sanksi dan menerima kompensasi transport.',
                ];
            }

            if ($type === 'medical_condition') {
                // Mitra menemukan kontraindikasi medis: refund 100% ke customer, mitra BEBAS sanksi (DEC-03)
                if ($customer) {
                    $this->walletService->credit(
                        $customer,
                        (float) $lockedOrder->total_price,
                        'order_refund',
                        (string) $lockedOrder->id,
                        "Refund 100% pembatalan kondisi medis ({$lockedOrder->order_code})",
                        $lockedOrder->region_id
                    );
                }

                $lockedOrder->update([
                    'status' => OrderStatus::Dibatalkan,
                    'cancellation_reason' => 'Kondisi medis penerima tidak memungkinkan: '.$reason,
                    'cancelled_by_id' => $mitra->id,
                    'cancelled_by_role' => 'mitra',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => $currentStatus->value,
                    'to_status' => OrderStatus::Dibatalkan->value,
                    'actor_id' => $mitra->id,
                    'actor_role' => 'mitra',
                    'note' => 'Pembatalan khusus: Kondisi medis tidak memungkinkan. Refund 100% pelanggan, mitra bebas sanksi (DEC-03).',
                ]);

                return [
                    'status' => OrderStatus::Dibatalkan,
                    'message' => 'Pesanan dibatalkan demi keselamatan medis pelanggan. Pelanggan menerima refund 100% dan Anda bebas sanksi.',
                ];
            }

            throw new InvalidArgumentException('Tipe pembatalan tidak dikenal.');
        });
    }

    /**
     * Automatic cancellation of stuck orders and expired invoices (SYS-02).
     *
     * @return array{cancelled_instant: int, cancelled_scheduled: int, expired_unpaid: int, expired_invoices: int}
     */
    public function autoCancelStuckOrders(): array
    {
        $now = Carbon::now();
        $instantCount = 0;
        $scheduledCount = 0;
        $unpaidCount = 0;

        // 1. Pesanan instan tanpa mitra > 30 menit
        $stuckInstant = Order::where('order_type', OrderType::Instan)
            ->where('status', OrderStatus::MencariMitra)
            ->whereNull('mitra_id')
            ->where('created_at', '<=', $now->copy()->subMinutes(30))
            ->get();

        foreach ($stuckInstant as $order) {
            DB::transaction(function () use ($order, &$instantCount) {
                if ($order->customer) {
                    $this->walletService->credit(
                        $order->customer,
                        (float) $order->total_price,
                        'order_refund',
                        (string) $order->id,
                        "Refund 100% pembatalan otomatis pesanan instan tanpa mitra {$order->order_code}",
                        $order->region_id
                    );
                }

                OrderDispatch::where('order_id', $order->id)
                    ->where('status', OrderDispatch::STATUS_OFFERED)
                    ->update(['status' => OrderDispatch::STATUS_EXPIRED]);

                $order->update([
                    'status' => OrderStatus::Dibatalkan,
                    'cancellation_reason' => 'Pembatalan otomatis sistem: Tidak ada mitra terapis tersedia dalam 30 menit.',
                    'cancelled_by_id' => null,
                    'cancelled_by_role' => 'system',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => OrderStatus::MencariMitra->value,
                    'to_status' => OrderStatus::Dibatalkan->value,
                    'actor_id' => null,
                    'actor_role' => 'system',
                    'note' => 'Pembatalan otomatis sistem: Melebihi batas waktu pencarian terapis 30 menit. Dana refund 100%.',
                ]);

                $instantCount++;
            });
        }

        // 2. Pesanan terjadwal tanpa mitra hingga H-2 jam sebelum jadwal
        $stuckScheduled = Order::where('order_type', OrderType::Terjadwal)
            ->where('status', OrderStatus::MencariMitra)
            ->whereNull('mitra_id')
            ->where('scheduled_start_time', '<=', $now->copy()->addHours(2))
            ->get();

        foreach ($stuckScheduled as $order) {
            DB::transaction(function () use ($order, &$scheduledCount) {
                if ($order->customer) {
                    $this->walletService->credit(
                        $order->customer,
                        (float) $order->total_price,
                        'order_refund',
                        (string) $order->id,
                        "Refund 100% pembatalan otomatis bursa jadwal hingga batas H-2 jam {$order->order_code}",
                        $order->region_id
                    );
                }

                $order->update([
                    'status' => OrderStatus::Dibatalkan,
                    'cancellation_reason' => 'Pembatalan otomatis sistem: Slot bursa tidak terisi hingga batas waktu H-2 jam.',
                    'cancelled_by_id' => null,
                    'cancelled_by_role' => 'system',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => OrderStatus::MencariMitra->value,
                    'to_status' => OrderStatus::Dibatalkan->value,
                    'actor_id' => null,
                    'actor_role' => 'system',
                    'note' => 'Pembatalan otomatis sistem: Batas waktu klaim bursa H-2 jam terlampaui. Dana refund 100%.',
                ]);

                $scheduledCount++;
            });
        }

        // 3. Pesanan belum dibayar (MenungguPembayaran) > 15 menit
        $unpaidOrders = Order::where('status', OrderStatus::MenungguPembayaran)
            ->where('created_at', '<=', $now->copy()->subMinutes(15))
            ->get();

        foreach ($unpaidOrders as $order) {
            DB::transaction(function () use ($order, &$unpaidCount) {
                $order->update([
                    'status' => OrderStatus::Dibatalkan,
                    'cancellation_reason' => 'Pesanan kedaluwarsa: Pembayaran saldo tidak diselesaikan dalam 15 menit.',
                    'cancelled_by_id' => null,
                    'cancelled_by_role' => 'system',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => OrderStatus::MenungguPembayaran->value,
                    'to_status' => OrderStatus::Dibatalkan->value,
                    'actor_id' => null,
                    'actor_role' => 'system',
                    'note' => 'Pesanan kedaluwarsa otomatis setelah 15 menit tanpa penyelesaian pembayaran.',
                ]);

                $unpaidCount++;
            });
        }

        // 4. Kedaluwarsa invoice top-up (SYS-02: kedaluwarsa top-up 15 menit)
        $expiredInvoicesCount = TopupInvoice::where('status', 'PENDING')
            ->where('expires_at', '<=', $now)
            ->update(['status' => 'EXPIRED']);

        return [
            'cancelled_instant' => $instantCount,
            'cancelled_scheduled' => $scheduledCount,
            'expired_unpaid' => $unpaidCount,
            'expired_invoices' => $expiredInvoicesCount,
        ];
    }
}
