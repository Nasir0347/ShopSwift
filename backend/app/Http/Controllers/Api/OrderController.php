<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
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
        $this->authorize('viewAny', Order::class);
        $query = Order::with(['items', 'user']);
        
        // If user is NOT admin/super_admin, filter by own ID
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            $query->where('user_id', $request->user()->id);
        }

        return $this->success(OrderResource::collection($query->orderByDesc('created_at')->paginate(20))->response()->getData(true), 'Orders retrieved', 200, [], 'orders');
    }

    public function store(StoreOrderRequest $request)
    {
        $this->authorize('create', Order::class);
        try {
            $order = $this->orderService->createOrder($request->validated(), $request->user());
            return $this->success(new OrderResource($order->load('items')), 'Order placed successfully', 201, [], 'order');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400); // Bad Request for logical errors
        }
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.variant.product.images', 'payments', 'shippingAddress', 'user'])->find($id);

        if (!$order) return $this->error('Order not found', 404);
        
        $this->authorize('view', $order);

        return $this->success(new OrderResource($order), 'Order details', 200, [], 'order');
    }
}
