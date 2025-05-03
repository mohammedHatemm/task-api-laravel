<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrderController implements HasMiddleware
{
    /**
     * Define the middleware for this controller.
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(
                'auth:sanctum',
                except: ['index', 'show']
            )
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $orders = Order::all();
            return response()->json([
                'success' => true,
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|integer',
                'price' => 'required',
                'status' => 'required|in:pending,shipped',
            ]);

            $order = $request->user()->orders()->create($validatedData);

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order created successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order)
    {
        try {
            Gate::authorize('modify', $order);

            $validatedData = $request->validate([
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|integer',
                'price' => 'required',
                'status' => 'required|in:pending,shipped',
            ]);

            $order->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order updated successfully'
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
                'error' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        try {
            Gate::authorize('modify', $order);

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
                'error' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders grouped by status with count and revenue totals.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus()
    {
        try {
            $orders = Order::select('status')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(price * quantity) as total_revenue')
                ->groupBy('status')
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No orders found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $orders->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'total_orders' => $item->total,
                        'total_revenue' => number_format($item->total_revenue, 2)
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
