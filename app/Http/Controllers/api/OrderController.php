<?php

namespace App\Http\Controllers\api;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Shipping;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function getOrders(Request $request)
    {
        try {
            $userId = auth()->id();
            $orders = Order::with(['orderDetails.product.product_variant'])->where('id', $request['order_id'])
                ->where('user_id', $userId)
                ->latest()
                ->get()
                ->map(function ($order) {
                    $order->orderDetails->map(function ($detail) {
                        $variant = $detail->product->product_variant
                            ->where('id', $detail->variant_id)
                            ->first();
                        if (!empty($variant)) {
                            $detail->product_image = url(
                                'storage/' . $variant && $variant->cover_image
                                    ? url('/storage/' . $variant->cover_image)
                                    : ($detail->product->main_image
                                        ? url('/storage/' . $detail->product->main_image)
                                        : null)
                            );
                        }
                        return $detail;
                    });

                    return [
                        'order_id'     => $order->order_id,
                        'order_status' => self::getOrderStatusText($order->status),
                        'order_amount' => (string)$order->gross_amount,
                        'order_date'   => $order->created_at
                            ? $order->created_at->format('Y-m-d')
                            : null,
                        'orderDetails' => $order->orderDetails
                    ];
                });

            return response()->json([
                'status' => 200,
                'msg'    => 'Order data fetched successfully!',
                'data'   => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'msg'    => 'Something went wrong',
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    public function getOrderDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'order_detail_id' => 'required|integer|exists:order_details,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $order = Order::with([
                'orderDetails.product.product_variant',
                'Address'
            ])->findOrFail($request->order_id);

            /* ================= ORDER ITEMS ================= */
            $set_product_id = 0;
            $items = $order->orderDetails->where('id', $request->order_detail_id)->map(function ($item) use (&$set_product_id) {
                $variant = $item->product->product_variant
                    ->where('id', $item->variant_id)
                    ->first();
                $set_product_id = $variant->product_id;
                return [
                    'product_id'        => $item->product_id,
                    'product_name'      => $item->product->name ?? null,
                    'image'             => url(
                        'storage/' . $variant && $variant->cover_image
                            ? url('/storage/' . $variant->cover_image)
                            : ($item->product->main_image
                                ? url('/storage/' . $item->product->main_image)
                                : null)
                    ),
                    'original_price'    => $variant->regular_price,
                    'discounted_price'  => $variant->sale_price,
                    'net_amount'         => $item->net_amount,
                    'description'       => $item->product->description ?? '',
                    'quantity'          => $item->quantity,

                    'variation' => $item->variant ? [
                        [
                            'id'     => $item->variant->id,
                            'weight' => $item->variant->weight ?? null,
                            'price'  => $item->variant->sale_price ?? null,
                        ]
                    ] : []
                ];
            });

            /* ================= PRODUCT NAMES ================= */

            $productNames = $order->orderDetails
                ->pluck('product.name')
                ->filter()
                ->implode(', ');

            /* ================= ADDRESS ================= */

            $address = $order->Address ? [
                'id'            => $order->Address->id,
                'name'          => $order->Address->name,
                'phone_number'  => $order->Address->phone_number,
                'address'       => $order->Address->address,
                'pincode'       => $order->Address->pincode,
                'state'         => $order->Address->state,
                'city'          => $order->Address->city,
                'landmark'      => $order->Address->landmark,
                'address_type'  => $order->Address->address_type,
                'latitude'      => $order->Address->latitude,
                'longitude'     => $order->Address->longitude,
            ] : null;

            /* ================= ORDER TRACKING ================= */

            $status = self::getOrderStatusText($order->status);
            $tracking = [
                [
                    'status'  => 'Ordered',
                    'date'    => $order->created_at
                        ? $order->created_at->format('Y-m-d')
                        : null,
                    'is_done' => true
                ],
                [
                    'status'  => 'Order Shipped',
                    'date'    => $order->shipped_at
                        ? $order->shipped_at->format('Y-m-d')
                        : null,
                    'is_done' =>  !empty($order->shipped_at)
                ],
                [
                    'status'  => 'Order Delivered',
                    'date'    => $order->delivered_at
                        ? $order->delivered_at->format('Y-m-d')
                        : null,
                    'is_done' => !empty($order->delivered_at)
                ],
                [
                    'status'  => 'Order Cancelled',
                    'date'    => $order->cancelled_at
                        ? $order->cancelled_at->format('Y-m-d')
                        : null,
                    'is_done' => !empty($order->cancelled_at)
                ],
                [
                    'status'  => 'Order Refunded',
                    'date'    => $order->refunded_at
                        ? $order->refunded_at->format('Y-m-d')
                        : null,
                    'is_done' => !empty($order->refunded_at)
                ],
            ];


            /* ================= FINAL RESPONSE ================= */
            return response()->json([
                'status' => 200,
                'msg'    => 'order details Screen',
                'data'   => [
                    'order_id'        => $order->order_id,
                    'order_date'      => optional($order->created_at)->format('Y-m-d'),
                    'order_status'    => $status,
                    'order_amount'    => (string) $order->net_amount,
                    'total_amount'    => (string) $order->gross_amount,
                    'order_items'     => $items->values(),
                    'product_names'   => $productNames,
                    'rating_status'   => Review::where('user_id', $order->user_id)
                        ->where('product_id', $set_product_id)
                        ->exists(), // you can update later
                    'delivery_address' => $address,
                    'invoice_url'     => $order->invoice_url
                        ? asset('storage/' . $order->invoice_url)
                        : null,
                    'payment_method'  => $order->payment_method,
                    'payment_status'  => $order->payment_status,
                    'order_tracking'  => $tracking,
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 500,
                'msg'    => 'Something went wrong',
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grant_total' => 'required',
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = Auth::user();
        $orderId = 'order_' . time();
        $baseUrl = env('CASHFREE_ENV') === 'sandbox'
            ? 'https://sandbox.cashfree.com'
            : 'https://api.cashfree.com';
        $headers = [
            "Content-Type: application/json",
            "x-api-version: 2023-08-01",
            "x-client-id: " . env('CASHFREE_APP_ID'),
            "x-client-secret: " . env('CASHFREE_SECRET_KEY'),
        ];
        $amount = (float) preg_replace('/[^\d.]/', '', $request->grant_total);
        $orderPayload = [
            "order_id" => $orderId,
            "order_amount" => $amount,
            "order_currency" => "INR",
            "customer_details" => [
                "customer_id" => (string) $user->id,
                "customer_name" => $user->name,
                "customer_email" => $user->email,
                "customer_phone" => $user->mobile_number,
            ],
            "order_meta" => [
                "notify_url" => url('/api/user/cashfree/webhook'),
            ]
        ];

        $ch = curl_init("$baseUrl/pg/orders");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($orderPayload),
        ]);

        $orderResponse = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($orderResponse['payment_session_id'])) {
            return response()->json($orderResponse, 500);
        }

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->address_id = $request['address_id'];
        $payment->order_id = $orderId;
        $payment->amount = $amount;
        $payment->status = 'PENDING';
        $payment->save();


        return response()->json([
            'status' => 200,
            'message' => 'Payment successful',
            'order_id' => $orderResponse['order_id'],
            'payment_session_id' => $orderResponse['payment_session_id'],
        ]);
    }

    public function saveOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            /* -------------------- VALIDATE -------------------- */
            $validator = Validator::make($request->all(), [
                "transactionId" => "required",
                "orderId"       => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 422,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $userId = auth()->id();

            /* -------------------- GET & MERGE CART -------------------- */
            $cart = Cache::get("cart_" . $userId);

            if (empty($cart)) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Cart is empty',
                ], 400);
            }

            $mergedCart = [];

            foreach ($cart as $item) {
                $key = $item['product_id']
                    . '_' . $item['variant_id']
                    . '_' . $item['color_variant_id']
                    . '_' . $item['size_variant_id'];

                if (isset($mergedCart[$key])) {
                    $mergedCart[$key]['quantity'] += (int) $item['quantity'];
                    $mergedCart[$key]['selected_for_checkout'] =
                        $mergedCart[$key]['selected_for_checkout'] || $item['selected_for_checkout'];
                    $mergedCart[$key]['is_buy_now'] =
                        $mergedCart[$key]['is_buy_now'] || $item['is_buy_now'];
                    if (!empty($item['checkout_id'])) {
                        $mergedCart[$key]['checkout_id'] = $item['checkout_id'];
                    }
                } else {
                    $mergedCart[$key] = $item;
                }
            }

            $mergedCart = array_values($mergedCart);

            if (empty($mergedCart)) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Cart is empty',
                ], 400);
            }

            /* -------------------- ADDRESS -------------------- */
            $address = Address::where('created_by', $userId)
                ->where('is_default', 1)
                ->first();

            if (!$address) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Default address not found',
                ], 400);
            }

            /* -------------------- TAX / FEE CONFIG (fetch once) -------------------- */
            $tax            = Tax::first();
            $taxPercentage  = $tax ? (float) $tax->percent          : 0;
            $defaultShipping = $tax ? (float) $tax->default_shipping : 0;
            $platformFee    = $tax ? (float) $tax->platform_fee      : 0;

            /* -------------------- PROCESS CART ITEMS -------------------- */
            $orderDetails = [];
            $netTotal     = 0;

            foreach ($mergedCart as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: ID {$item['product_id']}");
                }

                if ($product->product_type === 'variant') {
                    $variant = ProductVariant::find($item['variant_id']);
                    if (!$variant || $variant->product_id != $product->id) {
                        throw new \Exception("Invalid variant for product: {$product->name}");
                    }
                    if ($variant->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $variant->stock -= $item['quantity'];
                    $variant->save();
                    $price = $variant->sale_price > 0
                        ? (float) $variant->sale_price
                        : (float) $variant->regular_price;
                } elseif ($product->product_type === 'single') {
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $product->stock -= $item['quantity'];
                    $product->save();
                    $price = $product->sale_price > 0
                        ? (float) $product->sale_price
                        : (float) $product->regular_price;
                } else {
                    throw new \Exception("Invalid product type for: {$product->name}");
                }

                $itemNetAmount = $price * (int) $item['quantity'];
                $itemGstAmount = ($itemNetAmount * $taxPercentage) / 100;
                $netTotal += $itemNetAmount;
                // Use consistent key names from the cart merge above
                $orderDetails[] = [
                    'product_id'       => $product->id,
                    'variant_id'       => $item['variant_id']       ?? null,
                    'variant_size_id'  => $item['size_variant_id']  ?? null,  // fixed key
                    'variant_color_id' => $item['color_variant_id'] ?? null,  // fixed key
                    'product_name'     => $product->name,
                    'quantity'         => (int) $item['quantity'],
                    'unit_price'       => $price,
                    'net_amount'       => $itemNetAmount,
                    'gst_type'         => 0,
                    'gst_percentage'   => $taxPercentage,
                    'gst_amount'       => number_format($itemGstAmount, 2),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            /* -------------------- COUPON (applied once on full net total) -------------------- */
            $coupon        = null;
            $coupon_amount = 0;

            if (!empty($request->coupon_id)) {
                $coupon = Coupon::find($request->coupon_id);
                if ($coupon) {
                    $coupon_amount = self::getCouponDetails($coupon, $netTotal);
                }
            }

            /* -------------------- SHIPPING (based on full net total) -------------------- */
            $shippingRule = Shipping::where('minimum_delivery_amount', '<=', $netTotal)
                ->where('maximum_delivery_amount', '>=', $netTotal)
                ->where('status', 1)
                ->first();

            $finalDeliveryCharge = $shippingRule
                ? (float) $shippingRule->delivery_fee
                : $defaultShipping;

            /* -------------------- TOTALS -------------------- */
            $gstAmount   = ($netTotal * $taxPercentage) / 100;
            $totalAmount = $netTotal
                + $finalDeliveryCharge
                + $platformFee
                + $gstAmount
                - $coupon_amount;

            /* -------------------- CREATE ORDER -------------------- */
            $order                  = new Order();
            $order->order_id        = $request->orderId;
            $order->user_id         = $userId;
            $order->address_id      = $address->id;
            $order->phone           = auth()->user()->mobile_number;
            $order->status          = 1;
            $order->net_amount      = $netTotal;
            $order->gst_amount      = number_format($gstAmount, 2);
            $order->gross_amount    = $totalAmount;
            $order->shipping_amount = $finalDeliveryCharge;
            $order->notes           = 'Order Created';
            $order->coupon_id       = $coupon?->id;
            $order->coupon_amount   = $coupon_amount;
            $order->created_by      = $userId;
            $order->save();

            /* -------------------- INSERT ORDER ITEMS -------------------- */
            foreach ($orderDetails as $detail) {
                $order->orderDetails()->create($detail);
            }
            $get_user = User::where('id', $userId)->first();
            event(new NewNotification($userId, "Order Create", "$get_user->name has created an Order!", 1, 1));
            /* -------------------- PAYMENT UPDATE -------------------- */
            $payment = Payment::where('order_id', $request->orderId)->first();

            if ($payment) {
                $payment->status = 'PAID';
                $payment->other  = $request->others ?? null;
                $payment->save();
            }

            DB::commit();

            /* -------------------- CLEAR CACHE -------------------- */
            Cache::forget("cart_"      . $userId);
            Cache::forget("coupon_id_" . $userId);
            Cache::forget("address_id_" . $userId);
            Cache::forget("order_id_"  . $userId);

            return response()->json([
                'status'  => 200,
                'message' => 'Order placed successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public static function getCouponDetails($coupon, float $total_amount): float
    {
        if (!$coupon || $total_amount <= 0) {
            return 0;
        }
        $discountValue = (float) $coupon->discount_value;
        $minPrice      = (float) ($coupon->min_price ?? 0);
        $maxPrice      = (float) ($coupon->max_price ?? 0);
        if ($total_amount < $minPrice) {
            return 0;
        }
        if ($maxPrice > 0 && $total_amount > $maxPrice) {
            return 0;
        }
        if ($coupon->apply_for == 2) {
            $orderCount = \App\Models\Order::where('user_id', auth()->id())
                ->where('status', 4)
                ->count();
            if (!empty($coupon->order_count) && ($orderCount + 1) != $coupon->order_count) {
                return 0;
            }
        }
        if ($coupon->discount_type == 1) {
            return ($total_amount * $discountValue) / 100;
        }
        return $discountValue;
    }

    public function verifyPayment(Request $request)
    {
        $order_id = $request->order_id;

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(
                "https://sandbox.cashfree.com/pg/orders/$order_id/payments",
                [
                    'headers' => [
                        'x-client-id' => env('CASHFREE_APP_ID'),
                        'x-client-secret' => env('CASHFREE_SECRET_KEY'),
                        'x-api-version' => '2023-08-01'
                    ]
                ]
            );

            $data = json_decode($response->getBody(), true);

            if (!empty($data) && isset($data[0])) {
                return response()->json($data[0]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment not completed or not found',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getOrderStatusText($status)
    {
        return match ((int) $status) {
            1 => 'Ordered',
            2 => 'On InProgress',
            3 => 'Order Shipped',
            4 => 'Order Delivered',
            5 => 'Order Cancelled',
            6 => 'Order Refunded',
            default => 'Unknown',
        };
    }

    public function allOrders()
    {
        $userId = Auth::id();

        $orders = OrderDetail::with(['order', 'product.product_variant'])
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('order_id')
            ->map(function ($items) {

                $firstItem = $items->first();

                return [
                    'id'      => $firstItem->order->order_id,
                    'order_id'      => $firstItem->order_id,
                    'orderStatus'  => $this->getOrderStatusText($firstItem->order->status),
                    'orderAmount'  => (string) $firstItem->order->gross_amount,
                    'orderDate'    => $firstItem->order->created_at,
                    'product_name'  => $firstItem->product->name ?? '',
                    // total products count in this order
                    'productCount' => $items->count(),
                    // all products in this order
                    'products' => $items->map(function ($item) {
                        $variant = $item->product->product_variant
                            ->where('id', $item->variant_id)
                            ->first();
                        return [
                            'product_image' => $variant && $variant->cover_image
                                ? url('/storage/' . $variant->cover_image)
                                : ($item->product->main_image
                                    ? url('/storage/' . $item->product->main_image)
                                    : null),
                        ];
                    })->values(),
                ];
            })
            ->values();
        return response()->json([
            'status' => 200,
            'data'   => $orders
        ]);
    }
}
