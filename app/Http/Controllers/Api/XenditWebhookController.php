<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use App\Services\TopupService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __construct(
        protected TopupService $topupService,
        protected WalletService $walletService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle incoming top-up payment gateway webhook notification (SYS-05).
     */
    public function handleTopup(Request $request): JsonResponse
    {
        $token = $request->header('x-callback-token') ?? $request->header('X-CALLBACK-TOKEN');

        // Log incoming webhook payload untuk memudahkan debugging
        Log::info('Xendit Webhook Received', [
            'token_match' => ! empty($token) && $token === config('services.xendit.webhook_token'),
            'payload' => $request->all(),
        ]);

        $handled = $this->topupService->handleWebhook(
            payload: $request->all(),
            token: $token
        );

        if (! $handled) {
            return response()->json(['success' => false, 'message' => 'Invoice not found or invalid token'], 400);
        }

        return response()->json(['success' => true, 'message' => 'Webhook processed successfully']);
    }

    /**
     * Handle incoming Xendit disbursement callback for withdrawal payouts.
     */
    public function handleDisbursement(Request $request): JsonResponse
    {
        $token = $request->header('x-callback-token') ?? $request->header('X-CALLBACK-TOKEN');
        $expectedToken = config('services.xendit.webhook_token');

        if (! empty($expectedToken) && $token !== $expectedToken) {
            return response()->json(['success' => false, 'message' => 'Invalid callback token'], 401);
        }

        $payload = $request->all();
        $disbursementId = (string) ($payload['id'] ?? '');
        $externalId = (string) ($payload['external_id'] ?? '');
        $status = strtoupper((string) ($payload['status'] ?? ''));

        $withdrawal = WithdrawalRequest::query()
            ->when($disbursementId !== '', fn ($q) => $q->where('xendit_disbursement_id', $disbursementId))
            ->when($externalId !== '', fn ($q) => $q->orWhere('withdrawal_code', $externalId))
            ->first();

        if (! $withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
        }

        if ($status === 'COMPLETED') {
            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_APPROVED,
                'disbursement_status' => 'COMPLETED',
                'xendit_disbursement_id' => $withdrawal->xendit_disbursement_id ?: ($disbursementId ?: null),
            ]);

            return response()->json(['success' => true, 'message' => 'Disbursement completed']);
        }

        if ($status === 'FAILED') {
            $failureCode = (string) ($payload['failure_code'] ?? 'DISBURSEMENT_FAILED');
            $reason = "Transfer otomatis Xendit gagal ({$failureCode}).";

            DB::transaction(function () use ($withdrawal, $disbursementId, $reason) {
                $locked = WithdrawalRequest::whereKey($withdrawal->id)->lockForUpdate()->first();
                if (! $locked || $locked->status === WithdrawalRequest::STATUS_REJECTED) {
                    return;
                }

                $locked->update([
                    'status' => WithdrawalRequest::STATUS_REJECTED,
                    'disbursement_status' => 'FAILED',
                    'xendit_disbursement_id' => $locked->xendit_disbursement_id ?: ($disbursementId ?: null),
                    'rejection_reason' => $reason,
                ]);

                if ($locked->user) {
                    $this->walletService->credit(
                        $locked->user,
                        (float) $locked->amount,
                        'withdrawal_refund',
                        (string) $locked->id,
                        "Pengembalian dana penarikan {$locked->withdrawal_code} karena transfer Xendit gagal.",
                        $locked->region_id
                    );

                    $this->notificationService->sendToUser(
                        $locked->user,
                        'withdrawal_rejected',
                        'Transfer Penarikan Gagal',
                        "Penarikan {$locked->withdrawal_code} gagal diproses oleh bank ({$reason}). Saldo telah dikembalikan ke dompet Anda.",
                        ['withdrawal_id' => $locked->id, 'withdrawal_code' => $locked->withdrawal_code],
                        'high'
                    );
                }
            });

            return response()->json(['success' => true, 'message' => 'Disbursement failure refunded']);
        }

        if ($status !== '') {
            $withdrawal->update(['disbursement_status' => $status]);
        }

        return response()->json(['success' => true, 'message' => 'Webhook acknowledged']);
    }
}
