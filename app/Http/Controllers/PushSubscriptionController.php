<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Get VAPID public key for Web Push initialization.
     */
    public function publicKey(NotificationService $notificationService): JsonResponse
    {
        return response()->json([
            'publicKey' => $notificationService->getVapidPublicKey(),
        ]);
    }

    /**
     * Store or refresh a Web Push subscription for the authenticated user.
     */
    public function subscribe(Request $request, NotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'auth_token' => ['nullable', 'string', 'max:255'],
            'content_encoding' => ['nullable', 'string', 'max:32'],
        ]);

        $subscription = $notificationService->subscribe($request->user(), $validated);

        return response()->json([
            'message' => 'Langganan Web Push berhasil didaftarkan.',
            'subscription_id' => $subscription->id,
        ], 201);
    }

    /**
     * Unsubscribe a Web Push subscription.
     */
    public function unsubscribe(Request $request, NotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $notificationService->unsubscribe($request->user(), $validated['endpoint']);

        return response()->json([
            'message' => 'Langganan Web Push berhasil dihapus.',
        ]);
    }
}
