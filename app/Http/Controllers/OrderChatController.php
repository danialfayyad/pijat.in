<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderChatController extends Controller
{
    /**
     * Retrieve chat messages for an order.
     */
    public function getMessages(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id && $order->mitra_id !== $user->id) {
            abort(403, 'Akses tidak sah ke percakapan pesanan ini.');
        }

        $isActive = in_array($order->status, [
            OrderStatus::Dijadwalkan,
            OrderStatus::MenujuLokasi,
            OrderStatus::Tiba,
            OrderStatus::SedangBerlangsung,
        ], true);

        $messages = $order->messages()
            ->with('sender:id,name,role')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'is_me' => $msg->sender_id === $user->id,
                    'sender_name' => $msg->sender_id === $user->id ? 'Saya' : ($msg->sender?->isMitra() ? 'Mitra Terapis' : 'Pelanggan'),
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'messages' => $messages,
            'is_active' => $isActive,
            'order_status' => $order->status->value,
            'status_label' => $order->status->label(),
        ]);
    }

    /**
     * Send a new chat message in an active order.
     */
    public function sendMessage(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id && $order->mitra_id !== $user->id) {
            abort(403, 'Akses tidak sah ke percakapan pesanan ini.');
        }

        $isActive = in_array($order->status, [
            OrderStatus::Dijadwalkan,
            OrderStatus::MenujuLokasi,
            OrderStatus::Tiba,
            OrderStatus::SedangBerlangsung,
        ], true);

        if (! $isActive) {
            return response()->json([
                'error' => 'Sesi chat telah dinonaktifkan karena status pesanan telah '.$order->status->label().'.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $order->messages()->create([
            'sender_id' => $user->id,
            'message' => trim($validated['message']),
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'is_me' => true,
                'sender_name' => 'Saya',
                'message' => $message->message,
                'time' => $message->created_at->format('H:i'),
            ],
        ]);
    }
}

