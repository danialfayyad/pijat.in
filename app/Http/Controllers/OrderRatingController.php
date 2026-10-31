<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderRatingController extends Controller
{
    public function __construct(
        protected OrderRatingService $orderRatingService
    ) {}

    /**
     * Submit rating from customer to partner (CUS-14).
     */
    public function rateByCustomer(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Anda bukan pelanggan untuk pesanan ini.');
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        try {
            $result = $this->orderRatingService->submitRating($order, $user, (int) $validated['score']);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Terima kasih telah memberikan ulasan!',
                    'score' => $result['rating']->score,
                    'prompt_complaint' => $result['rating']->score <= 3,
                ]);
            }

            return redirect()->back()->with('status', 'Terima kasih atas penilaian rating Anda!');
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit rating from partner to customer (MIT-14).
     */
    public function rateByMitra(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($order->mitra_id !== $user->id) {
            abort(403, 'Anda bukan mitra untuk pesanan ini.');
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        try {
            $result = $this->orderRatingService->submitRating($order, $user, (int) $validated['score']);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Terima kasih telah memberikan penilaian pelanggan!',
                    'score' => $result['rating']->score,
                ]);
            }

            return redirect()->back()->with('status', 'Terima kasih atas penilaian rating Anda!');
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

