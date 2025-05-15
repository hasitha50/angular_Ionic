<?php

namespace App\Http\Controllers\order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $FirebaseService;
    public function __construct(FirebaseService $FirebaseService)
    {
        $this->FirebaseService = $FirebaseService;
    }

    public function index(Request $request)
    {
        try {
            $authUser = Auth::user();

            // if ($authUser->role === 'seller') {
            //     $orders = Order::whereHas('product', function ($query) use ($authUser) {
            //             $query->where('seller_id', $authUser->id);
            //         })
            //         ->with([
            //             'product' => function ($query) use ($authUser) {
            //                 $query->where('seller_id', $authUser->id);
            //             },
            //             'product.seller',
            //             'customer',
            //         ])
            //         ->get();
            // } else {
            $orders = Order::where('user_id', $authUser->id)
                ->with('product', 'product.seller', 'customer')
                ->get();
            // }


            return response()->json(['data' => $orders]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch orders', 'details' => $e->getMessage()], 500);
        }
    }

    public function createOrder(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'product_id'    => ['required', 'exists:products,id'],
                'qty'           => ['required', 'integer', 'min:1'],
                'inv_no'        => ['required'],
                'paid_amount'   => ['required', 'numeric'],
                'total_amount'  => ['required', 'numeric'],

            ]);

            $user = Auth::user();

            // Fetch product and seller info
            $product = Product::findOrFail($request->product_id);
            $seller = $product->seller ?? null; // Ensure relationship exists

            // Create order
            $order = Order::create([
                'user_id'         => $user->id,
                'product_id'      => $product->id,
                'qty'             => $request->qty,
                'inv_no'          => $request->inv_no,
                'paid_amount'     => $request->paid_amount,
                'total_amount'    => $request->total_amount,
                'order_status_id' => 1,
            ]);

            // Generate notification content
            $title = 'New Order Placed';
            $body = "You have placed an order for {$product->name} (Qty: {$request->qty}). Invoice #: {$request->inv_no}";

            // Optional custom data
            $customData = [
                'order_id' => $order->id,
                'product_name' => $product->name,
                'qty' => $request->qty
            ];

            $user = Auth::user();
            $token = $user->fcm_token;

            // Notify buyer
            $this->FirebaseService->sendNotification(
                $token,
                $title,
                $body,
                $customData
            );

            // Notify seller (if token exists)
            if ($seller && $seller->fcm_token) {
                $sellerTitle = 'New Order Received';
                $sellerBody = "{$user->name} ordered {$product->name} (Qty: {$request->qty}).";

                $this->FirebaseService->sendNotification(
                    $seller->device_token,
                    $sellerTitle,
                    $sellerBody,
                    $customData
                );
            }

            return response()->json([
                'message' => 'Order created and notifications sent',
                'order' => $order->load('product')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function updateOrder(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            $request->validate([
                'product_id'    => ['nullable', 'exists:products,id'],
                'qty'           => ['nullable', 'integer', 'min:1'],
                'paid_amount'   => ['nullable', 'numeric'],
                'total_amount'  => ['nullable', 'numeric']
            ]);

            // Backup original product and qty before update
            $originalProduct = $order->product;
            $originalQty = $order->qty;

            $order->update($request->only('product_id', 'paid_amount', 'total_amount', 'qty'));

            // Reload with updated relationships
            $order->refresh()->load('product');

            $product = $order->product ?? $originalProduct;
            $qty = $order->qty ?? $originalQty;
            $user = $order->customer; // use correct relation
            $seller = $product->seller ?? null;

            $title = 'Order Updated';
            $body = "Your order for {$product->name} (Qty: {$qty}) has been updated.";

            $customData = [
                'order_id' => $order->id,
                'product_name' => $product->name,
                'qty' => $qty,
                'updated' => true,
            ];

            $user = Auth::user();
            $buyerToken = $order->customer->fcm_token ?? null;

            if ($buyerToken) {
                $this->FirebaseService->sendNotification(
                    $buyerToken,
                    $title,
                    $body,
                    $customData
                );
            }

            // Notify seller if exists
            if ($seller && $seller->fcm_token) {
                $sellerTitle = 'Order Updated';
                $sellerBody = "{$user->name} updated the order for {$product->name} (Qty: {$qty}).";

                $this->FirebaseService->sendNotification(
                    $seller->fcm_token,
                    $sellerTitle,
                    $sellerBody,
                    $customData
                );
            }

            return response()->json([
                'message' => 'Order updated and notifications sent',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update order',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function getOrderById($id)
    {
        try {
            $order = Order::with(['product.seller', 'customer', 'status'])->findOrFail($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Order not found', 'details' => $e->getMessage()], 404);
        }
    }


    public function cancelOrder(Request $request, $id)
    {
        try {
            // Load order with related product and customer
            $order = Order::with('product', 'customer')->findOrFail($id);
            $order->update(['order_status_id' => 2]);
    
            $product = $order->product;
            $customer = $order->customer; // use the correct relation name
            $seller = $product->seller ?? null;
    
            // Notification content
            $title = 'Order Cancelled';
            $body = "Your order for {$product->name} (Qty: {$order->qty}) has been cancelled.";
    
            $customData = [
                'order_id' => $order->id,
                'product_name' => $product->name,
                'qty' => $order->qty,
                'cancelled' => true
            ];
    
            // Authenticated user (assumed to be buyer)
            $authUser = Auth::user();
            $buyerToken = $order->customer->fcm_token ?? null;
    
            // Notify buyer
            if ($buyerToken) {
                $this->FirebaseService->sendNotification(
                    $buyerToken,
                    $title,
                    $body,
                    $customData
                );
            }
    
            // Notify seller
            if ($seller && $seller->fcm_token) {
                $sellerTitle = 'Order Cancelled';
                $sellerBody = "{$authUser->name} cancelled the order for {$product->name} (Qty: {$order->qty}).";
    
                $this->FirebaseService->sendNotification(
                    $seller->fcm_token,
                    $sellerTitle,
                    $sellerBody,
                    $customData
                );
            }
    
            return response()->json(['message' => 'Order cancelled and notifications sent']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to cancel order',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
