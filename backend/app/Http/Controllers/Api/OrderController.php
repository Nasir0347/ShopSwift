<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['items', 'user']);
        
        // If user is NOT admin/super_admin, filter by own ID
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            $query->where('user_id', $request->user()->id);
        }

        return $this->success(OrderResource::collection($query->paginate(20))->response()->getData(true));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_code' => 'nullable|string|exists:discounts,code',
            'payment_method' => 'required|in:cod,stripe',
        ]);

        try {
            $order = $this->orderService->createOrder($validated, $request->user());
            return $this->success(new OrderResource($order->load('items')), 'Order placed successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items', 'payments'])->find($id);

        if (!$order) return $this->error('Order not found', 404);

        if ($request->user()->role === 'customer' && $order->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        return $this->success(new OrderResource($order));
    }
}
