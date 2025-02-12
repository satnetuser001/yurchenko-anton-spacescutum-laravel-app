<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrderController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::query();

        // Apply filter for current user's purchases
        if ($request->has('purchased_by_user')) {
            $userId = auth()->id();
            $query->where('user_id', $userId);
        }

        $paginatedOrders = $query->paginate(5);

        // Add _links for each order
        $ordersWithLinks = $paginatedOrders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'product_id' => $order->product_id,
                'order_id' => $order->order_id,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('orders.show', ['order' => $order->id]),
                    ],
                ],
            ];
        });

        // Replace the original collection with a new one with _links
        $paginatedOrders->setCollection($ordersWithLinks);

        // Add filter links
        $filterLinks = [
            'purchased_by_user' => [
                'href' => route('orders.index', ['purchased_by_user' => 'true']),
            ],
        ];

        return response()->json([
            'data' => $paginatedOrders,
            '_links_filters' => $filterLinks,
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required',
        ]);

        $order = Order::create([
            'user_id' => auth()->id(),
            'product_id' => $validated['product_id'],
            'order_id' => $validated['order_id'],
        ]);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'product_id' => $order->product_id,
                'order_id' => $order->order_id,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('orders.show', ['order' => $order->id]),
                    ],
                ],
            ],
        ], Response::HTTP_CREATED)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return response()->json([
            'id' => $order->id,
            'user_id' => $order->user_id,
            'product_id' => $order->product_id,
            'order_id' => $order->order_id,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            '_links' => [
                'self' => [
                    'href' => route('orders.show', ['order' => $order->id]),
                ],
            ],
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }
}
