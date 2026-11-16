<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\TopupInvoice;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TopupService
{
    public const MINIMUM_TOPUP_AMOUNT = 10000;

    public const INVOICE_EXPIRY_MINUTES = 15;

    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Create a top-up invoice for customer with regional attribution (CUS-16, DEC-02).
     */
    public function createInvoice(User $user, float $amount, string $paymentMethod = 'QRIS'): TopupInvoice
    {
        // Enforce DEC-02: Customer MUST have a primary address set before top-up
        $primaryAddress = $user->primaryAddress;
        if (! $primaryAddress || ! $primaryAddress->region_id) {
            throw new DomainException('Anda wajib menyimpan minimal satu Alamat Utama sebelum dapat melakukan pengisian saldo.');
        }

        if ($amount < self::MINIMUM_TOPUP_AMOUNT) {
            throw new DomainException('Nominal pengisian saldo minimal adalah Rp '.number_format(self::MINIMUM_TOPUP_AMOUNT, 0, ',', '.').'.');
        }

        // Batalkan / kedaluwarsakan tagihan topup dompet sebelumnya yang masih PENDING
        // agar timer countdown ter-reset penuh 15 menit untuk tagihan baru ini (gaya Tokopedia / Shopee)
        TopupInvoice::where('user_id', $user->id)
            ->whereNull('order_id')
            ->where('status', 'PENDING')
            ->update(['status' => 'EXPIRED']);

        $invoiceNumber = 'TOPUP-'.date('Ymd').'-'.strtoupper(Str::random(6));

        $paymentUrl = null;
        $xenditInvoiceId = null;
        $qrisString = null;
        $vaNumber = null;
        $vaBankCode = null;
        $availableBanks = null;

        $secretKey = config('services.xendit.secret_key');
        if (! empty($secretKey)) {
            // Selalu buat Standard Xendit Invoice agar tercatat resmi di menu Transaksi & Tautan Pembayaran Xendit
            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->timeout(10)
                    ->post('https://api.xendit.co/v2/invoices', [
                        'external_id' => $invoiceNumber,
                        'amount' => (int) $amount,
                        'payer_email' => $user->email,
                        'description' => "Pengisian saldo dompet Pijat.In ({$invoiceNumber})",
                        'invoice_duration' => self::INVOICE_EXPIRY_MINUTES * 60,
                        'success_redirect_url' => route('pelanggan.wallet.invoice', $invoiceNumber),
                        'failure_redirect_url' => route('pelanggan.wallet.invoice', $invoiceNumber),
                    ]);

                if ($response->successful()) {
                    $xenditData = $response->json();
                    $xenditInvoiceId = $xenditData['id'] ?? null;
                    $paymentUrl = $xenditData['invoice_url'] ?? null;

                    // Ambil daftar Virtual Account resmi yang otomatis dibuatkan oleh Xendit Invoice
                    if (! empty($xenditData['available_banks']) && is_array($xenditData['available_banks'])) {
                        $bankMap = [];
                        foreach ($xenditData['available_banks'] as $ab) {
                            $bCode = strtoupper((string) ($ab['bank_code'] ?? ''));
                            $accNum = (string) ($ab['bank_account_number'] ?? '');
                            if ($bCode !== '' && $accNum !== '') {
                                $bankMap[$bCode] = $accNum;
                            }
                        }
                        $availableBanks = $bankMap;

                        // Jika metode pembayaran yang dipilih pelanggan adalah bank VA tertentu, set nomor VA-nya langsung
                        $methodCode = strtoupper($paymentMethod);
                        if (isset($bankMap[$methodCode])) {
                            $vaNumber = $bankMap[$methodCode];
                            $vaBankCode = $methodCode;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Xendit invoice creation error: '.$e->getMessage());
            }

            // Coba ambil nomor VA atau QRIS resmi jika metode spesifik dipilih
            $isVa = in_array(strtoupper($paymentMethod), ['BCA', 'MANDIRI', 'BRI', 'BNI', 'PERMATA', 'CIMB', 'BSI']);
            if ($isVa && empty($vaNumber)) {
                try {
                    $vaResponse = Http::withBasicAuth($secretKey, '')
                        ->timeout(10)
                        ->post('https://api.xendit.co/callback_virtual_accounts', [
                            'external_id' => $invoiceNumber,
                            'bank_code' => strtoupper($paymentMethod),
                            'name' => 'PIJATIN - '.substr($user->name, 0, 15),
                            'expected_amount' => (int) $amount,
                            'is_closed' => true,
                            'expiration_date' => now()->addMinutes(self::INVOICE_EXPIRY_MINUTES)->toIso8601String(),
                        ]);

                    if ($vaResponse->successful()) {
                        $vaData = $vaResponse->json();
                        $vaNumber = $vaData['account_number'] ?? null;
                        $vaBankCode = strtoupper($paymentMethod);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Xendit VA creation error: '.$e->getMessage());
                }
            } elseif (! $isVa) {
                try {
                    $qrResponse = Http::withBasicAuth($secretKey, '')
                        ->timeout(10)
                        ->post('https://api.xendit.co/qr_codes', [
                            'external_id' => $invoiceNumber,
                            'type' => 'DYNAMIC',
                            'amount' => (int) $amount,
                            'callback_url' => route('api.webhooks.xendit.topup'),
                        ]);

                    if ($qrResponse->successful()) {
                        $qrData = $qrResponse->json();
                        $qrisString = $qrData['qr_string'] ?? null;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Xendit QRIS creation error: '.$e->getMessage());
                }
            }
        }

        return TopupInvoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $user->id,
            'region_id' => $primaryAddress->region_id,
            'amount' => $amount,
            'status' => 'PENDING',
            'payment_method' => $paymentMethod,
            'xendit_invoice_id' => $xenditInvoiceId,
            'payment_url' => $paymentUrl,
            'qris_string' => $qrisString,
            'va_number' => $vaNumber,
            'va_bank_code' => $vaBankCode,
            'available_banks' => $availableBanks,
            'expires_at' => now()->addMinutes(self::INVOICE_EXPIRY_MINUTES),
        ]);
    }

    /**
     * Create a top-up invoice linked to pay for an order deficit (CUS-09).
     */
    public function createOrderDeficitInvoice(Order $order, float $amount, string $paymentMethod = 'QRIS'): TopupInvoice
    {
        if ($amount <= 0) {
            throw new DomainException('Nominal tagihan pembayaran tidak valid.');
        }

        $invoiceNumber = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(6));

        $paymentUrl = null;
        $xenditInvoiceId = null;

        $secretKey = config('services.xendit.secret_key');
        if (! empty($secretKey)) {
            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->timeout(10)
                    ->post('https://api.xendit.co/v2/invoices', [
                        'external_id' => $invoiceNumber,
                        'amount' => (int) $amount,
                        'payer_email' => $order->customer?->email ?? 'customer@pijat.in',
                        'description' => "Pembayaran pesanan {$order->order_code} ({$invoiceNumber})",
                        'invoice_duration' => self::INVOICE_EXPIRY_MINUTES * 60,
                        'success_redirect_url' => route('pelanggan.wallet.invoice', $invoiceNumber),
                        'failure_redirect_url' => route('pelanggan.wallet.invoice', $invoiceNumber),
                    ]);

                if ($response->successful()) {
                    $xenditData = $response->json();
                    $xenditInvoiceId = $xenditData['id'] ?? null;
                    $paymentUrl = $xenditData['invoice_url'] ?? null;
                }
            } catch (\Throwable $e) {
                Log::warning('Xendit invoice creation error: '.$e->getMessage());
            }
        }

        return TopupInvoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $order->customer_id,
            'region_id' => $order->region_id,
            'order_id' => $order->id,
            'amount' => $amount,
            'status' => 'PENDING',
            'payment_method' => $paymentMethod,
            'xendit_invoice_id' => $xenditInvoiceId,
            'payment_url' => $paymentUrl,
            'expires_at' => now()->addMinutes(self::INVOICE_EXPIRY_MINUTES),
        ]);
    }

    /**
     * Mark an invoice as paid and atomically credit the customer's wallet (SYS-05, CUS-09).
     */
    public function markPaid(TopupInvoice $invoice, ?string $xenditPaymentId = null): void
    {
        if ($invoice->isPaid()) {
            return;
        }

        if ($invoice->isExpired()) {
            $invoice->update(['status' => 'EXPIRED']);
            throw new DomainException('Invoice pembayaran ini telah kedaluwarsa.');
        }

        DB::transaction(function () use ($invoice, $xenditPaymentId) {
            $invoice->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'xendit_invoice_id' => $xenditPaymentId ?? $invoice->xendit_invoice_id,
            ]);

            $this->walletService->credit(
                user: $invoice->user,
                amount: (float) $invoice->amount,
                referenceType: 'topup',
                referenceId: $invoice->invoice_number,
                description: "Pengisian saldo dompet via {$invoice->payment_method} ({$invoice->invoice_number})",
                regionId: $invoice->region_id
            );

            // CUS-09: If invoice is linked to an order waiting for payment, settle the order atomically
            if ($invoice->order_id) {
                $order = $invoice->order;
                if ($order && $order->status === OrderStatus::MenungguPembayaran) {
                    $wallet = $invoice->user->getOrCreateWallet();
                    if ((float) $wallet->balance >= (float) $order->total_price) {
                        $this->walletService->debit(
                            $invoice->user,
                            (float) $order->total_price,
                            'order_payment',
                            (string) $order->id,
                            'Pembayaran pesanan '.$order->order_code,
                            $order->region_id
                        );

                        $order->update(['status' => OrderStatus::MencariMitra]);

                        OrderStatusHistory::create([
                            'order_id' => $order->id,
                            'from_status' => OrderStatus::MenungguPembayaran->value,
                            'to_status' => OrderStatus::MencariMitra->value,
                            'actor_id' => $invoice->user->id,
                            'actor_role' => 'pelanggan',
                            'note' => 'Pembayaran lunas via Xendit / QRIS. Pesanan masuk antrean pencarian mitra.',
                        ]);

                        if ($order->order_type === OrderType::Instan) {
                            app(InstantOrderMatchingService::class)->dispatchNextOffer($order);
                        }
                    }
                }
            }
        });
    }

    /**
     * Handle incoming payment gateway webhook notification (SYS-05).
     */
    public function handleWebhook(array $payload, ?string $token = null): bool
    {
        $expectedToken = config('services.xendit.webhook_token', 'pijatin_webhook_secret_key');
        if ($token && $token !== $expectedToken) {
            return false;
        }

        // Cek jika ini adalah tes webhook dari Dashboard Xendit (tombol "Tes dan Simpan")
        // Xendit biasanya mengirim external_id fiktif atau event 'test'
        $externalId = $payload['external_id'] ?? $payload['invoice_number'] ?? $payload['qr_code']['external_id'] ?? null;
        if (! $externalId || str_contains(strtolower((string) $externalId), 'test') || str_contains(strtolower((string) ($payload['event'] ?? '')), 'test')) {
            // Berikan response sukses (200) agar verifikasi tes di dashboard Xendit berhasil
            return true;
        }

        // 1. Tangani Format QR Code Callback (Xendit QR API: qr_code / event === 'qr.payment')
        if (isset($payload['qr_code']) || (isset($payload['event']) && str_starts_with((string) $payload['event'], 'qr.'))) {
            $qrData = $payload['qr_code'] ?? $payload['data'] ?? [];
            $externalId = $qrData['external_id'] ?? null;
            $status = strtoupper($qrData['status'] ?? ($payload['status'] ?? ''));

            if ($externalId && ($status === 'COMPLETED' || $status === 'PAID')) {
                $invoice = TopupInvoice::where('invoice_number', $externalId)->first();
                if ($invoice) {
                    $this->markPaid($invoice, $qrData['id'] ?? null);

                    return true;
                }
            }
        }

        // 2. Tangani Format Virtual Account Callback (Xendit FVA API: payment_id / callback_virtual_account_id)
        if (isset($payload['payment_id']) || isset($payload['callback_virtual_account_id'])) {
            $externalId = $payload['external_id'] ?? null;
            if ($externalId) {
                $invoice = TopupInvoice::where('invoice_number', $externalId)->first();
                if ($invoice) {
                    $this->markPaid($invoice, $payload['payment_id'] ?? $payload['id'] ?? null);

                    return true;
                }
            }
        }

        // 3. Tangani Format Invoice Callback Standar (Xendit Invoice API)
        $externalId = $payload['external_id'] ?? $payload['invoice_number'] ?? null;
        if (! $externalId) {
            return false;
        }

        $invoice = TopupInvoice::where('invoice_number', $externalId)->first();
        if (! $invoice) {
            // Jika invoice tidak ditemukan di database, tetap kembalikan true (200)
            // agar payload test/invoice lain dari Xendit tidak menyebabkan status 400
            Log::info("Xendit Webhook: Invoice {$externalId} not found in database, acknowledging 200 OK.");

            return true;
        }

        $status = strtoupper($payload['status'] ?? '');
        if ($status === 'PAID' || $status === 'SETTLED' || $status === 'COMPLETED') {
            $this->markPaid($invoice, $payload['id'] ?? null);

            return true;
        } elseif ($status === 'EXPIRED') {
            $invoice->update(['status' => 'EXPIRED']);

            return true;
        }

        return false;
    }

    /**
     * Actively query Xendit API to check and sync payment status without relying on webhooks (Localhost / Fallback).
     */
    public function checkAndSyncXenditStatus(TopupInvoice $invoice): bool
    {
        if ($invoice->isPaid()) {
            return true;
        }

        $secretKey = config('services.xendit.secret_key');
        if (empty($secretKey)) {
            return false;
        }

        try {
            if ($invoice->xendit_invoice_id) {
                $response = Http::withBasicAuth($secretKey, '')
                    ->timeout(10)
                    ->get("https://api.xendit.co/v2/invoices/{$invoice->xendit_invoice_id}");
            } else {
                $response = Http::withBasicAuth($secretKey, '')
                    ->timeout(10)
                    ->get('https://api.xendit.co/v2/invoices', [
                        'external_id' => $invoice->invoice_number,
                    ]);
            }

            if (! $response->successful()) {
                return false;
            }

            $data = $response->json();
            $xenditInvoice = isset($data[0]) ? $data[0] : $data;

            if (empty($xenditInvoice) || ! isset($xenditInvoice['status'])) {
                return false;
            }

            $status = strtoupper($xenditInvoice['status']);
            if ($status === 'PAID' || $status === 'SETTLED') {
                $this->markPaid($invoice, $xenditInvoice['id'] ?? null);

                return true;
            } elseif ($status === 'EXPIRED') {
                $invoice->update(['status' => 'EXPIRED']);

                return false;
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning("Gagal sinkronisasi status Xendit ({$invoice->invoice_number}): ".$e->getMessage());

            return false;
        }
    }
}
