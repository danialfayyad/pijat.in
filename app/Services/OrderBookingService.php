<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderBookingService
{
    public const RISK_AGREEMENT_VERSION = 'v1.0';

    /**
     * Validate and create an order from customer booking request.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function createOrder(User $customer, array $data): Order
    {
        $package = ServicePackage::with('service')->findOrFail($data['package_id']);

        $address = CustomerAddress::with('region')
            ->where('user_id', $customer->id)
            ->findOrFail($data['customer_address_id']);

        if (! $address->region || $address->region->status !== 'aktif') {
            throw ValidationException::withMessages([
                'customer_address_id' => 'Alamat yang dipilih berada di luar wilayah operasional yang aktif.',
            ]);
        }

        $regionTz = $address->region->timezone ?? 'Asia/Jakarta';

        // 1. Validate Recipient (CUS-04)
        $recipientType = $data['recipient_type'] ?? 'myself';
        if ($recipientType === 'myself') {
            $recipientName = $customer->name;
            $recipientPhone = $customer->phone ?? ($data['recipient_phone'] ?? '');
            $recipientGender = strtolower((string) $customer->gender);

            if (! in_array($recipientGender, ['pria', 'wanita'], true)) {
                throw ValidationException::withMessages([
                    'recipient_gender' => 'Profil Anda belum memiliki data jenis kelamin yang valid (pria/wanita).',
                ]);
            }
        } else {
            $recipientName = trim((string) ($data['recipient_name'] ?? ''));
            $recipientPhone = trim((string) ($data['recipient_phone'] ?? ''));
            $recipientGender = strtolower(trim((string) ($data['recipient_gender'] ?? '')));

            $recipientErrors = [];
            if ($recipientName === '') {
                $recipientErrors['recipient_name'] = 'Nama lengkap penerima layanan wajib diisi.';
            }

            if ($recipientPhone === '') {
                $recipientErrors['recipient_phone'] = 'Nomor telepon penerima layanan wajib diisi.';
            }

            if (! in_array($recipientGender, ['pria', 'wanita'], true)) {
                $recipientErrors['recipient_gender'] = 'Pilih jenis kelamin penerima layanan (pria/wanita).';
            }

            if (! empty($recipientErrors)) {
                throw ValidationException::withMessages($recipientErrors);
            }
        }

        // 2. Validate Order Type & Time (CUS-06)
        $orderTypeInput = $data['order_type'] ?? 'terjadwal';
        $orderType = $orderTypeInput === 'instan' ? OrderType::Instan : OrderType::Terjadwal;
        $scheduledStartTime = null;

        if ($orderType === OrderType::Terjadwal) {
            $scheduledDate = $data['scheduled_date'] ?? null;
            $scheduledTime = $data['scheduled_time'] ?? null;

            if (! $scheduledDate || ! $scheduledTime) {
                throw ValidationException::withMessages([
                    'scheduled_time' => 'Tanggal dan jam pemesanan terjadwal wajib dipilih.',
                ]);
            }

            try {
                $scheduledStartTime = Carbon::parse("{$scheduledDate} {$scheduledTime}", $regionTz);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'scheduled_time' => 'Format waktu atau tanggal terjadwal tidak valid.',
                ]);
            }

            $nowInRegion = Carbon::now($regionTz);
            $minAllowed = $nowInRegion->copy()->addHours(3);
            $maxAllowed = $nowInRegion->copy()->addDays(7)->endOfDay();

            if ($scheduledStartTime->lt($minAllowed)) {
                throw ValidationException::withMessages([
                    'scheduled_time' => 'Pemesanan terjadwal minimal 3 jam dari waktu sekarang.',
                ]);
            }

            if ($scheduledStartTime->gt($maxAllowed)) {
                throw ValidationException::withMessages([
                    'scheduled_time' => 'Pemesanan terjadwal maksimal 7 hari ke depan.',
                ]);
            }

            // Check operational hours 08:00 - 21:00 in region local timezone
            $startHourMinute = $scheduledStartTime->format('H:i');
            $scheduledEndTime = $scheduledStartTime->copy()->addMinutes($package->duration_minutes);
            $endHourMinute = $scheduledEndTime->format('H:i');

            if ($startHourMinute < '08:00' || $startHourMinute > '21:00') {
                throw ValidationException::withMessages([
                    'scheduled_time' => 'Waktu mulai layanan harus berada dalam jam operasional (08:00 - 21:00 waktu setempat).',
                ]);
            }

            if ($endHourMinute > '21:00' || $scheduledEndTime->toDateString() !== $scheduledStartTime->toDateString()) {
                throw ValidationException::withMessages([
                    'scheduled_time' => "Estimasi selesai layanan ({$endHourMinute}) melebihi jam tutup operasional (21:00). Silakan pilih jam mulai yang lebih awal.",
                ]);
            }
        }

        // 3. Validate Risk Consent (CUS-07 & SYS-08)
        if (empty($data['risk_agreement'])) {
            throw ValidationException::withMessages([
                'risk_agreement' => 'Anda wajib menyetujui syarat & ketentuan risiko layanan sebelum melanjutkan.',
            ]);
        }

        $medicalNotes = ! empty($data['medical_notes']) ? trim((string) $data['medical_notes']) : null;

        // Generate Order inside database transaction
        return DB::transaction(function () use (
            $customer,
            $package,
            $address,
            $recipientType,
            $recipientName,
            $recipientPhone,
            $recipientGender,
            $orderType,
            $scheduledStartTime,
            $medicalNotes
        ) {
            $orderCode = $this->generateUniqueOrderCode();

            $fullAddress = $address->address_text;
            if (! empty($address->benchmark)) {
                $fullAddress .= ' (Patokan: '.$address->benchmark.')';
            }

            $order = Order::create([
                'order_code' => $orderCode,
                'order_type' => $orderType,
                'customer_id' => $customer->id,
                'customer_address_id' => $address->id,
                'mitra_id' => null,
                'region_id' => $address->region_id,
                'service_id' => $package->service_id,
                'service_package_id' => $package->id,
                'service_name' => $package->service->name.' ('.$package->duration_minutes.' Menit)',
                'duration_minutes' => $package->duration_minutes,
                'total_price' => $package->price,
                'commission_percent' => 10.00,
                'recipient_type' => $recipientType,
                'recipient_name' => $recipientName,
                'recipient_gender' => $recipientGender,
                'recipient_phone' => $recipientPhone,
                'service_address_text' => $fullAddress,
                'service_coordinate' => ['lat' => (float) $address->latitude, 'lng' => (float) $address->longitude],
                'scheduled_start_time' => $scheduledStartTime ? $scheduledStartTime->utc() : null,
                'medical_notes' => $medicalNotes,
                'risk_agreement_version' => self::RISK_AGREEMENT_VERSION,
                'consent_timestamp' => Carbon::now(),
                'status' => OrderStatus::MenungguPembayaran,
                'reschedule_count' => 0,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::MenungguPembayaran->value,
                'actor_id' => $customer->id,
                'actor_role' => 'pelanggan',
                'note' => 'Pesanan baru dibuat oleh pelanggan.',
            ]);

            return $order;
        });
    }

    /**
     * Generate unique human-readable order code.
     */
    protected function generateUniqueOrderCode(): string
    {
        do {
            $code = 'ORD-'.Carbon::now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
