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

        $invoiceNumber = 'TOPUP-'.date('Ymd').'-'.strtoupper(Str::random(6));

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
                }
            } catch (\Throwable $e) {
                Log::warning('Xendit invoice creation error: '.$e->getMessage());
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

        $externalId = $payload['external_id'] ?? $payload['invoice_number'] ?? null;
        if (! $externalId) {
            return false;
        }

        $invoice = TopupInvoice::where('invoice_number', $externalId)->first();
        if (! $invoice) {
            return false;
        }

        $status = strtoupper($payload['status'] ?? '');
        if ($status === 'PAID' || $status === 'SETTLED') {
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
