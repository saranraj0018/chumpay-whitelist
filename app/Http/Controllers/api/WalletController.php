<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wallet;
use App\Models\WalletOffer;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function addBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactionId' => 'required',
            'orderId'       => 'required',
            'others'        => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        // Find the pending payment record
        $payment = Payment::where('user_id', auth()->id())
            ->where('order_id', $request->orderId)
            ->where('status', 'PENDING')
            ->where('type', 'wallet')
            ->first();

        if (!$payment) {
            return response()->json([
                'status'  => 404,
                'message' => 'No pending wallet payment found',
            ], 200);
        }

        $result = $this->walletService->saveWallet(
            userId: auth()->id(),
            amount: $payment->amount,   // use amount from Payment table
            transactionId: $request->transactionId,
            orderId: $request->orderId,
        );

        // Mark payment as SUCCESS after wallet updated
        $payment->status         = 'PAID';
        $payment->transaction_id = $request->transactionId;
        $payment->save();

        return response()->json([
            'status'        => 200,
            'message'       => 'Wallet updated successfully',
            'balance'       => $result['wallet']->balance,
            'bonus_balance' => $result['wallet']->bonus_balance,
            'bonus_until'   => $result['bonus']['bonus_valid_until'],
        ]);
    }

    public function createWalletOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user    = Auth::user();
        $orderId = 'wallet_order_' . $user->id . '_' . time();

        $baseUrl = env('CASHFREE_ENV') === 'sandbox'
            ? 'https://sandbox.cashfree.com'
            : 'https://api.cashfree.com';

        $headers = [
            "Content-Type: application/json",
            "x-api-version: 2023-08-01",
            "x-client-id: "  . env('CASHFREE_APP_ID'),
            "x-client-secret: " . env('CASHFREE_SECRET_KEY'),
        ];

        $amount = (float) $request->amount;

        $orderPayload = [
            "order_id"         => $orderId,
            "order_amount"     => $amount,
            "order_currency"   => "INR",
            "customer_details" => [
                "customer_id"    => (string) $user->id,
                "customer_name"  => $user->name,
                "customer_email" => $user->email,
                "customer_phone" => $user->mobile_number,
            ],
            "order_meta" => [
                "notify_url" => url('/api/user/cashfree/webhook'),
            ],
        ];

        $ch = curl_init("$baseUrl/pg/orders");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($orderPayload),
        ]);
        $orderResponse = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($orderResponse['payment_session_id'])) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to create payment order',
                'error'   => $orderResponse,
            ], 500);
        }

        $payment                 = new Payment();
        $payment->user_id        = $user->id;
        $payment->order_id       = $orderId;
        $payment->amount         = $amount;
        $payment->status         = 'PENDING';
        $payment->type           = 'wallet';   // mark as wallet top-up
        $payment->save();

        return response()->json([
            'status'             => 200,
            'message'            => 'Order created successfully',
            'order_id'           => $orderResponse['order_id'],
            'payment_session_id' => $orderResponse['payment_session_id'],
        ]);
    }

    public function saveWalletOrder(Request $request, WalletService $walletService)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                "orderId"      => "required",
                "type" => "required|in:wallet",
                "coupon_id" => "nullable|exists:coupons,id",
                "total_amount" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $userId = auth()->id();
        
            /* -------------------- CART -------------------- */
            $cart = Cache::get("cart_" . $userId);
            if (empty($cart)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Cart is empty'
                ], 400);
            }

            /* -------------------- ADDRESS -------------------- */
            $address = Address::where('created_by', $userId)
                ->where('is_default', 1)
                ->first();
            /* -------------------- COUPON -------------------- */
            $coupon_amount = 0;
            $coupon = null;
            if (!empty($request->coupon_id) && !empty($request->total_amount)) {
                $coupon = Coupon::find($request->coupon_id);
                if ($coupon) {
                    $coupon_amount = self::getCouponDetails($coupon, $request->total_amount);
                }
            }
    
            $shipping = 0;
            $netTotal = 0;
            $gstTotal = 0;
            $shipping = 0;
            $orderDetails = [];

            /* -------------------- CART LOOP -------------------- */
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found");
                    }
                    if ($product->product_type == 'variant') {
                        $variant = ProductVariant::find($item['variant_id']);
                        if (!$variant || $variant->product_id != $product->id) {
                            throw new \Exception("Invalid variant");
                            }
                         
                    if ($variant->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
            
                    $variant->decrement('stock', $item['quantity']);
                    $price = $variant->sale_price ?: $variant->regular_price;
                
                } elseif ($product->product_type == 'single') {

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }

                    $product->decrement('stock', $item['quantity']);

                    $price = $product->sale_price ?: $product->regular_price;
                } else {
                    throw new \Exception("Invalid product type");
                }
    
                $netAmount = $price * $item['quantity'];
                $netTotal += $netAmount;

                $orderDetails[] = [
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'net_amount' => $netAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            /* -------------------- TOTAL -------------------- */
            $grossAmount = ($netTotal + $shipping) - $coupon_amount;

            /* -------------------- CREATE ORDER -------------------- */
            $order = new Order();
            $order->order_id =  $request->orderId;
            $order->user_id  =  $userId;
            $order->address_id =  $address->id ?? '';
            $order->phone =  auth()->user()->mobile_number;
            $order->status = 1;
            $order->net_amount =  $netTotal;
            $order->gst_amount =   $gstTotal;
            $order->gross_amount = $grossAmount;
            $order->shipping_amount =  $shipping;
            $order->coupon_id = $coupon?->id;
            $order->coupon_amount =  $coupon_amount;
            $order->created_by =  $userId;
            $order->notes = 'Order Created';
            $order->save();

            foreach ($orderDetails as $detail) {
                $order->orderDetails()->create($detail);
            }

            /* -------------------- WALLET PAYMENT -------------------- */
            $walletUsed = 0;
            $bonusUsed  = 0;

            $walletResult = $walletService->deductWallet($userId, $grossAmount);
            $walletUsed = $walletResult['wallet_used'];
            $bonusUsed  = $walletResult['bonus_used'];

            /* -------------------- PAYMENT -------------------- */
            $payment = Payment::firstOrNew(['order_id' => $request->orderId]);

            $payment->user_id = $userId;
            $payment->amount  = $grossAmount;
            $payment->status  = 'PAID';
            $payment->type    = 'wallet';
            $payment->save();

            /* -------------------- UPDATE ORDER -------------------- */
            $order = Order::where('id',  $order->id)->update([
                'wallet_used' => $walletUsed,
                'bonus_used'  => $bonusUsed
            ]);
 
            DB::commit();
            Cache::forget("cart_" . $userId);
            return response()->json([
                'status' => 200,
                'message' => 'Order placed successfully',
                'wallet_used' => $walletUsed,
                'bonus_used'  => $bonusUsed
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public static function getCouponDetails($coupon, $total_amount)
    {
        if (!$coupon || $total_amount <= 0) {
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
}
