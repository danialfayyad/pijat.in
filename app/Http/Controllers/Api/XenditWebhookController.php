<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function __construct(
        protected TopupService $topupService
    ) {}

    /**
     * Handle incoming top-up payment gateway webhook notification (SYS-05).
     */
    public function handleTopup(Request $request): JsonResponse
    {
        $token = $request->header('x-callback-token') ?? $request->header('X-CALLBACK-TOKEN');

        $handled = $this->topupService->handleWebhook(
            payload: $request->all(),
            token: $token
        );

        if (! $handled) {
            return response()->json(['success' => false, 'message' => 'Invoice not found or invalid token'], 400);
        }

        return response()->json(['success' => true, 'message' => 'Webhook processed successfully']);
    }
}
