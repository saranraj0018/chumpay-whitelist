<?php

namespace App\Http\Controllers\web;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipping;
use App\Models\Tax;
use App\Models\User;
use App\Models\VariantAttributeValue;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'grant_total' => 'required',
            'address_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()], 422);
        }

        $user = Auth::user();

        $address = Address::where('id', $request->address_id)
            ->where('created_by', $user->id)
            ->first();

        if (! $address) {
            return response()->json(['status' => 400, 'message' => 'Invalid address selected'], 400);
        }

        if (! env('CASHFREE_APP_ID') || ! env('CASHFREE_SECRET_KEY')) {
            Log::error('Cashfree order create aborted: CASHFREE_APP_ID/CASHFREE_SECRET_KEY not configured.');

            return response()->json([
                'status' => 500,
                'message' => 'Payment gateway is not configured. Please contact support.',
            ], 500);
        }

        $orderId = 'order_'.time();
        $baseUrl = env('CASHFREE_ENV') === 'sandbox'
            ? 'https://sandbox.cashfree.com'
            : 'https://api.cashfree.com';

        $headers = [
            'Content-Type: application/json',
            'x-api-version: 2023-08-01',
            'x-client-id: '.env('CASHFREE_APP_ID'),
            'x-client-secret: '.env('CASHFREE_SECRET_KEY'),
        ];

        $amount = (float) preg_replace('/[^\d.]/', '', $request->grant_total);

        $orderPayload = [
            'order_id' => $orderId,
            'order_amount' => $amount,
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => (string) $user->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->mobile_number,
            ],
            'order_meta' => [
                'notify_url' => url('/api/user/cashfree/webhook'),
                'return_url' => url('/shop/payment/return?order_id={order_id}'),
            ],
        ];

        $ch = curl_init("$baseUrl/pg/orders");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($orderPayload),
        ]);

        $rawResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        $orderResponse = json_decode($rawResponse, true);

        if (! isset($orderResponse['payment_session_id'])) {
            Log::error('Cashfree order create failed', [
                'curl_error' => $curlError ?: null,
                'response' => $orderResponse ?? $rawResponse,
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Could not start payment. Please try again in a moment.',
            ], 500);
        }

        $payment = new Payment;
        $payment->user_id = $user->id;
        $payment->address_id = $request->address_id;
        $payment->order_id = $orderId;
        $payment->amount = $amount;
        $payment->status = 'PENDING';
        $payment->is_buy_now = $request->boolean('is_buy_now') ? 1 : 0;
        $payment->checkout_id = $request->checkout_id;
        $payment->save();

        return response()->json([
            'status' => 200,
            'message' => 'Payment session created',
            'order_id' => $orderResponse['order_id'],
            'payment_session_id' => $orderResponse['payment_session_id'],
        ]);
    }

    public function saveOrder(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'transactionId' => 'required',
                'orderId' => 'required',
                'address_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 422, 'message' => $validator->errors()->first()], 422);
            }

            $userId = Auth::id();

            /* -------- SNAPSHOT FULL CART BEFORE ANY FILTERING -------- */
            $fullCart = Cache::get('cart_'.$userId, []);
            if (empty($fullCart)) {
                return response()->json(['status' => 400, 'message' => 'Cart is empty'], 400);
            }

            $isBuyNow = $request->boolean('is_buy_now');
            $checkoutId = $request->checkout_id;
            if (! $isBuyNow && ! empty($checkoutId)) {
                $isBuyNow = true;
            }

            /* -------- FILTER ITEMS TO CHECKOUT -------- */
            if ($isBuyNow) {
                $checkoutCart = array_values(array_filter($fullCart, function ($item) use ($checkoutId) {
                    if (empty($item['is_buy_now'])) {
                        return false;
                    }
                    if (! empty($checkoutId)) {
                        return ($item['checkout_id'] ?? null) === $checkoutId;
                    }

                    return true;
                }));
            } else {
                $checkoutCart = array_values(array_filter($fullCart, function ($item) {
                    return empty($item['is_buy_now']) && ! empty($item['selected_for_checkout']);
                }));
            }

            if (empty($checkoutCart)) {
                return response()->json(['status' => 400, 'message' => 'No items to checkout'], 400);
            }
            $orderedCheckoutIds = [];
            if ($isBuyNow) {
                foreach ($checkoutCart as $ci) {
                    if (! empty($ci['checkout_id'])) {
                        $orderedCheckoutIds[] = $ci['checkout_id'];
                    }
                }
            }
            $mergedCart = [];
            foreach ($checkoutCart as $item) {
                $key = $item['product_id'].'_'.($item['variant_id'] ?? 0)
                    .'_'.($item['color_variant_id'] ?? 0)
                    .'_'.($item['size_variant_id'] ?? 0);

                if (isset($mergedCart[$key])) {
                    $mergedCart[$key]['quantity'] += (int) $item['quantity'];
                    if (! empty($item['matrix'])) {
                        $mergedCart[$key]['matrix'] = array_merge($mergedCart[$key]['matrix'] ?? [], $item['matrix']);
                    }
                } else {
                    $mergedCart[$key] = $item;
                }
            }
            $mergedCart = array_values($mergedCart);

            /* -------- ADDRESS -------- */
            $address = Address::where('id', $request->address_id)
                ->where('created_by', $userId)
                ->first();

            if (! $address) {
                return response()->json(['status' => 400, 'message' => 'Invalid address selected'], 400);
            }

            /* -------- TAX / FEE -------- */
            $tax = Tax::first();
            $taxPercentage = $tax ? (float) $tax->percent : 0;
            $defaultShipping = $tax ? (float) $tax->default_shipping : 0;
            $platformFee = $tax ? (float) $tax->platform_fee : 0;

            /* -------- PROCESS ITEMS -------- */
            $orderDetails = [];
            $netTotal = 0;

            foreach ($mergedCart as $item) {
                $product = Product::find($item['product_id']);
                if (! $product) {
                    throw new \Exception("Product not found: ID {$item['product_id']}");
                }

                $qty = (int) $item['quantity'];
                $variantId = null;
                $bulkProductId = null;
                $variantSizeId = null;
                $variantColorId = null;

                if ($product->product_type === 'variant') {

                    $variantId = $item['variant_id'] ?? null;
                    if (! $variantId) {
                        throw new \Exception("Variant ID missing for: {$product->name}");
                    }

                    $variant = ProductVariant::find($variantId);
                    if (! $variant || $variant->product_id != $product->id) {
                        throw new \Exception("Invalid variant for product: {$product->name}");
                    }
                    if ($variant->stock < $qty) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $variant->stock -= $qty;
                    $variant->save();

                    $variantSizeId = $item['size_variant_id'] ?? null;
                    $variantColorId = $item['color_variant_id'] ?? null;
                    $price = $variant->sale_price > 0 ? (float) $variant->sale_price : (float) $variant->regular_price;
                    $itemNetAmount = $price * $qty;
                } elseif ($product->product_type === 'single') {

                    if ($product->stock < $qty) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $product->stock -= $qty;
                    $product->save();

                    $price = $product->sale_price > 0 ? (float) $product->sale_price : (float) $product->regular_price;
                    $itemNetAmount = $price * $qty;
                } elseif ($product->product_type === 'bulk') {
                    $tiers = $product->bulk_product()->orderBy('minimum')->get();
                    $perPiece = (float) ($product->per_piece_price ?? 0);
                    $matched = $tiers->first(function ($t) use ($qty) {
                        $min = $t->minimum ?? 0;
                        $max = $t->maximum ?? null;

                        return $qty >= $min && ($max === null || $qty <= $max);
                    });

                    if ($matched) {
                        $bulkProductId = $matched->id; //  Store bulk_product_id
                        $itemNetAmount = (float) ($matched->sale_price ?? $matched->regular_price ?? 0);
                    } else {
                        $rangedTiers = $tiers->filter(fn ($t) => ! is_null($t->maximum))->values();
                        $topTier = $rangedTiers->last();
                        if ($topTier && $qty > $topTier->maximum) {
                            $bulkProductId = $topTier->id; //  Store top tier bulk_product_id
                            $topPrice = (float) ($topTier->sale_price ?? $topTier->regular_price ?? 0);
                            $extra = $qty - $topTier->maximum;
                            $itemNetAmount = $topPrice + ($extra * $perPiece);
                        } else {
                            $itemNetAmount = $perPiece * $qty;
                        }
                    }
                    $price = $qty > 0 ? $itemNetAmount / $qty : 0;
                    $matrix = $item['matrix'] ?? [];
                    if (count($matrix) === 1) {
                        $combo = $matrix[0];
                        $colorName = $combo['color'] ?? null;
                        $sizeName = $combo['size'] ?? null;

                        if (! empty($colorName)) {
                            $variantColorId = VariantAttributeValue::where('value', $colorName)
                                ->value('id');
                        }

                        if (! empty($sizeName)) {
                            $variantSizeId = VariantAttributeValue::where('value', $sizeName)
                                ->value('id');
                        }
                    }
                } else {
                    throw new \Exception("Invalid product type for: {$product->name}");
                }

                $netTotal += $itemNetAmount;

                $orderDetails[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'bulk_product_id' => $bulkProductId,
                    'variant_size_id' => $variantSizeId,
                    'variant_color_id' => $variantColorId,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'net_amount' => $itemNetAmount,
                    'matrix' => ! empty($item['matrix']) ? $item['matrix'] : null,
                    'gst_type' => 0,
                    'gst_percentage' => $taxPercentage,
                    'gst_amount' => 0, //  calculated after coupon below
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            /* -------- COUPON -------- */
            $coupon = null;
            $coupon_amount = 0;
            $cachedCouponId = $request->coupon_id ?: Cache::get('coupon_id_'.$userId);
            if (! empty($cachedCouponId)) {
                $coupon = Coupon::find($cachedCouponId);
                if ($coupon) {
                    $coupon_amount = self::getCouponDetails($coupon, $netTotal);
                }
            }

            /* -------- SHIPPING -------- */
            $shippingRule = Shipping::where('minimum_delivery_amount', '<=', $netTotal)
                ->where('maximum_delivery_amount', '>=', $netTotal)
                ->where('status', 1)->first();
            $finalDeliveryCharge = $shippingRule ? (float) $shippingRule->delivery_fee : $defaultShipping;

            /* -------- TOTALS -------- */
            $taxableBase = max(0, $netTotal - $coupon_amount);
            $gstAmount = ($taxableBase * $taxPercentage) / 100;
            $totalAmount = $taxableBase + $finalDeliveryCharge + $platformFee + $gstAmount;

            /* -------- UPDATE PER-ITEM GST PROPORTIONALLY -------- */
            if ($netTotal > 0) {
                foreach ($orderDetails as &$detail) {
                    $itemShare = $detail['net_amount'] / $netTotal;
                    $itemTaxable = $itemShare * $taxableBase;
                    $detail['gst_amount'] = number_format(($itemTaxable * $taxPercentage) / 100, 2);
                }
                unset($detail);
            }

            /* -------- CREATE ORDER -------- */
            $order = new Order;
            $order->order_id = $request->orderId;
            $order->user_id = $userId;
            $order->address_id = $address->id;
            $order->phone = Auth::user()->mobile_number;
            $order->status = 1;
            $order->net_amount = $netTotal;
            $order->gst_amount = number_format($gstAmount, 2);
            $order->gross_amount = $totalAmount;
            $order->shipping_amount = $finalDeliveryCharge;
            $order->notes = 'Order Created';
            $order->coupon_id = $coupon?->id;
            $order->coupon_amount = $coupon_amount;
            $order->created_by = $userId;
            $order->save();

            foreach ($orderDetails as $detail) {
                $order->orderDetails()->create($detail);
            }

            $get_user = User::find($userId);

            event(new NewNotification($userId, 'Order Created', "$get_user->name has created an Order!", 1, 1));

            /* -------- PAYMENT UPDATE -------- */
            $payment = Payment::where('order_id', $request->orderId)->first();
            if ($payment) {
                $payment->status = 'PAID';
                $payment->other = $request->others ?? null;
                $payment->save();
            }

            /* -------- CLEAR CART -------- */
            /* -------- CLEAR CART -------- */

            if ($isBuyNow) {

                $remainingCart = [];

                foreach ($fullCart as $item) {

                    if (
                        ! empty($item['is_buy_now']) &&
                        in_array(
                            $item['checkout_id'] ?? null,
                            $orderedCheckoutIds,
                            true
                        )
                    ) {

                        // Buy Now order completed
                        // remove from cache

                        continue;
                    }

                    $remainingCart[] = $item;
                }

                Cache::put(
                    'cart_'.$userId,
                    array_values($remainingCart),
                    now()->addDays(7)
                );
            } else {

                $remainingCart = [];

                foreach ($fullCart as $item) {

                    if (
                        empty($item['is_buy_now']) &&
                        ! empty($item['selected_for_checkout'])
                    ) {
                        continue;
                    }

                    $remainingCart[] = $item;
                }

                Cache::put(
                    'cart_'.$userId,
                    array_values($remainingCart),
                    now()->addDays(7)
                );
            }

            Cache::forget('coupon_id_'.$userId);
            Cache::forget('address_id_'.$userId);
            Cache::forget('order_id_'.$userId);

            DB::commit();

            return response()->json(['status' => 200, 'message' => 'Order placed successfully!']);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(['status' => 500, 'message' => $th->getMessage()], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $order_id = $request->order_id;
        $baseUrl = env('CASHFREE_ENV') === 'sandbox'
            ? 'https://sandbox.cashfree.com'
            : 'https://api.cashfree.com';

        try {
            $client = new Client;
            $response = $client->get("$baseUrl/pg/orders/$order_id/payments", [
                'headers' => [
                    'x-client-id' => env('CASHFREE_APP_ID'),
                    'x-client-secret' => env('CASHFREE_SECRET_KEY'),
                    'x-api-version' => '2023-08-01',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (! empty($data) && isset($data[0])) {
                return response()->json($data[0]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment not completed or not found',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public static function getCouponDetails($coupon, float $total_amount): float
    {
        if (! $coupon || $total_amount <= 0) {
            return 0;
        }

        $discountValue = (float) $coupon->discount_value;
        $minPrice = (float) ($coupon->min_price ?? 0);
        $maxPrice = (float) ($coupon->max_price ?? 0);

        if ($total_amount < $minPrice) {
            return 0;
        }
        if ($maxPrice > 0 && $total_amount > $maxPrice) {
            return 0;
        }

        if ($coupon->apply_for == 2) {
            $orderCount = Order::where('user_id', auth()->id())
                ->where('status', 4)->count();
            if (! empty($coupon->order_count) && ($orderCount + 1) != $coupon->order_count) {
                return 0;
            }
        }

        if ($coupon->discount_type == 1) {
            return ($total_amount * $discountValue) / 100;
        }

        return $discountValue;
    }

    public function paymentReturn(Request $request)
    {
        $orderId = $request->query('order_id');

        $paymentRow = Payment::where('order_id', $orderId)->first();
        if (! $paymentRow) {
            return redirect('/shop/delivery?step=3')->with('error', 'Payment record not found.');
        }

        $baseUrl = env('CASHFREE_ENV') === 'sandbox'
            ? 'https://sandbox.cashfree.com'
            : 'https://api.cashfree.com';

        try {
            $client = new Client;
            $resp = $client->get("$baseUrl/pg/orders/$orderId/payments", [
                'headers' => [
                    'x-client-id' => env('CASHFREE_APP_ID'),
                    'x-client-secret' => env('CASHFREE_SECRET_KEY'),
                    'x-api-version' => '2023-08-01',
                ],
            ]);
            $data = json_decode($resp->getBody(), true);
        } catch (\Throwable $e) {
            return redirect('/shop/delivery?step=3')->with('error', 'Could not verify payment.');
        }

        $payment = $data[0] ?? null;
        $status = $payment['payment_status'] ?? null;

        if ($status === 'SUCCESS') {
            if ($paymentRow->status !== 'PAID') {
                $paymentRow->status = 'PAID';
                $paymentRow->other = json_encode($payment);
                $paymentRow->save();
            }

            $user = User::find($paymentRow->user_id);
            if (! $user) {
                Log::error('paymentReturn: user not found for payment row', ['payment_id' => $paymentRow->id]);

                return redirect('/shop/delivery?step=3')->with('error', 'Could not restore your session.');
            }

            Auth::guard('web')->login($user, true);
            request()->session()->save();

            $saveReq = new Request([
                'transactionId' => $payment['cf_payment_id'] ?? $orderId,
                'orderId' => $orderId,
                'address_id' => $paymentRow->address_id,
                'is_buy_now' => $paymentRow->is_buy_now ? 1 : 0,
                'checkout_id' => $paymentRow->checkout_id,
            ]);
            $saveResponse = $this->saveOrder($saveReq);
            $saveData = json_decode($saveResponse->getContent(), true);

            if ($paymentRow->is_buy_now) {
                $cartKey = 'cart_'.$paymentRow->user_id;
                $cartItems = Cache::get($cartKey, []);
                $remaining = array_values(array_filter($cartItems, function ($item) use ($paymentRow) {
                    if (empty($item['is_buy_now'])) {
                        return true;
                    }
                    if (! empty($paymentRow->checkout_id)) {
                        return ($item['checkout_id'] ?? null) !== $paymentRow->checkout_id;
                    }

                    return false;
                }));
                Cache::put($cartKey, $remaining, now()->addDays(7));
            }

            if (($saveData['status'] ?? null) === 200) {
                return redirect('/shop/delivery?step=4');
            }

            Log::error('Order save failed after successful payment', [
                'order_id' => $orderId,
                'reason' => $saveData['message'] ?? 'unknown',
            ]);

            return redirect('/shop/delivery?step=4');
        }

        return redirect('/shop/delivery?step=3')->with('error', 'Payment not completed.');
    }
}
