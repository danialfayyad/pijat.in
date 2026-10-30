<?php

namespace App\Services;

use App\Models\TopupInvoice;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
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

        return TopupInvoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $user->id,
            'region_id' => $primaryAddress->region_id,
            'amount' => $amount,
            'status' => 'PENDING',
            'payment_method' => $paymentMethod,
            'expires_at' => now()->addMinutes(self::INVOICE_EXPIRY_MINUTES),
        ]);
    }

    /**
     * Mark an invoice as paid and atomically credit the customer's wallet (SYS-05).
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
}
