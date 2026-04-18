<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Status;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    // Показать все заказы
    public function index()
    {
        $orders = Order::query()
            ->with(['user', 'status', 'items'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    // Показать один заказ по id
    public function show(int $id)
    {
        $order = Order::query()
            ->with(['user', 'status', 'items'])
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status_id' => ['required', 'integer', 'exists:statuses,id'],
        ]);

        $order = Order::query()->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $status = Status::query()->find($data['status_id']);

        if (!$status) {
            return response()->json([
                'message' => 'Status not found',
            ], 404);
        }

        $order->status_id = $status->id;
        $order->save();

        $order->load(['user', 'status', 'items']);

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => $order,
        ]);
    }
}


