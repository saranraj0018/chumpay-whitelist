<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\VariantAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    // public function couponList(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'total_amount' => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => $validator->errors()->first(),
    //             ], 422);
    //         }
    //         $totalAmount = (float) $request->total_amount;
    //         $coupons = Coupon::select(
    //             'id',
    //             'coupon_code',
    //             'discount_type',
    //             'discount_value',
    //             'description',
    //             'apply_for',
    //             'max_price',
    //             'min_price',
    //             'order_count',
    //             'status',
    //             'expires_at',
    //             'created_at'
    //         )
    //             ->where('status', 1)
    //             ->where(function ($query) {
    //                 $query->whereNull('expires_at')
    //                     ->orWhereDate('expires_at', '>=', now()->toDateString());
    //             })
    //             ->orderBy('id', 'desc')
    //             ->get()
    //             ->map(function ($coupon) use ($totalAmount) {
    //                 $isApplicable = false;

    //                 if ($coupon->apply_for == '1') {
    //                     $isApplicable = true;
    //                 } else {
    //                     $minPrice = (float) ($coupon->min_price ?? 0);
    //                     $maxPrice = (float) ($coupon->max_price ?? 0);

    //                     if ($totalAmount >= $minPrice) {
    //                         if ($maxPrice > 0) {
    //                             $isApplicable = $totalAmount <= $maxPrice;
    //                         } else {
    //                             $isApplicable = true;
    //                         }
    //                     }
    //                 }

    //                 return [
    //                     'coupon_id'          => $coupon->id,
    //                     'coupon_code'        => $coupon->coupon_code,
    //                     'coupon_discount'    => $coupon->discount_type == '1'
    //                         ? $coupon->discount_value . '%'
    //                         : number_format($coupon->discount_value, 2),
    //                     'coupon_type'        => $coupon->discount_type == '1' ? 'percentage' : 'amount',
    //                     'coupon_value'       => $coupon->discount_value,
    //                     'apply_for'          => $coupon->apply_for == '1' ? 'all' : 'minimum_order',
    //                     'minimum_amount'     => $coupon->min_price,
    //                     'maximum_amount'     => $coupon->max_price,
    //                     'order_count'        => $coupon->order_count,
    //                     'description'        => $coupon->description,
    //                     'coupon_expiry_date' => $coupon->expires_at
    //                         ? \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d')
    //                         : '',
    //                     'coupon_status'      => $isApplicable,
    //                     'created_at'         => $coupon->created_at
    //                         ? \Carbon\Carbon::parse($coupon->created_at)->format('Y-m-d H:i:s')
    //                         : '',
    //                 ];
    //             });

    //         return response()->json([
    //             'status' => 200,
    //             'mgs'    => 'Coupon list screen',
    //             'data'   => $coupons
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'mgs'    => 'Something went wrong',
    //             'error'  => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function couponList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $totalAmount = (float) str_replace(',', '', $request->total_amount);
            $coupons = Coupon::where('status', 1)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', now()->toDateString());
                })
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($coupon) use ($totalAmount) {
                    $discountAmount = self::getCouponDetails($coupon, $totalAmount);
                    return [
                        "coupon_id"        => $coupon->id,
                        "coupon_code"      => $coupon->coupon_code,
                        "coupon_discount"  => $coupon->discount_type == 1
                            ? $coupon->discount_value . '%'
                            : number_format($coupon->discount_value, 2),
                        "coupon_type"      => $coupon->discount_type == 1 ? "percentage" : "amount",
                        "coupon_value"     => $coupon->discount_value,
                        "minimum_amount"   => $coupon->min_price,
                        "maximum_amount"   => $coupon->max_price,
                        "description"      => $coupon->description ?? "",
                        "coupon_expiry_date" => $coupon->expires_at
                            ? \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d')
                            : '',
                        "discount_amount"  => round($discountAmount, 2),
                        "coupon_status"    => $discountAmount > 0,
                    ];
                });

            return response()->json([
                'status'  => 200,
                'message' => 'Coupons fetched successfully',
                'data'    => $coupons,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 500,
                'message' => $th->getMessage(),
            ]);
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

        // Check min / max conditions
        if ($total_amount < $minPrice) {
            return 0;
        }

        if ($maxPrice > 0 && $total_amount > $maxPrice) {
            return 0;
        }

        // ORDER COUNT LOGIC (optional)
        if ($coupon->apply_for == 2) {
            $orderCount = \App\Models\Order::where('user_id', auth()->id())
                ->where('status', 4)
                ->count();

            if (!empty($coupon->order_count) && ($orderCount + 1) != $coupon->order_count) {
                return 0;
            }
        }

        // Calculate discount
        if ($coupon->discount_type == 1) {
            return ($total_amount * $discountValue) / 100; // percentage
        }

        return $discountValue;
    }

    public function applyCouponToCart(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user->id;
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'required|integer|exists:coupons,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'mgs'    => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $cartKey   = "cart_{$userId}";
            $cart_list = Cache::get($cartKey, []);

            if (empty($cart_list)) {
                return response()->json([
                    'status' => 404,
                    'mgs'    => 'Cart is empty',
                ], 404);
            }

            $productData = [];
            $totalItems = 0;
            $itemsAmount = 0;
            foreach ($cart_list as $index => $item) {
                $productId      = $item['product_id'] ?? null;
                $quantity       = (int) ($item['quantity'] ?? 1);
                $price          = (float) ($item['sale_price'] ?? 0);
                $sizeVariantId  = $item['size_variant_id'] ?? null;
                $product = Product::select('id', 'name', 'main_image')
                    ->find($productId);
                $sizeName = '';
                if (!empty($sizeVariantId)) {
                    $size = VariantAttributeValue::select('id', 'value')->find($sizeVariantId);
                    $sizeName = $size->value ?? '';
                }
                $totalPrice = $quantity * $price;
                $totalItems += $quantity;
                $itemsAmount += $totalPrice;
                $productData[] = [
                    'id'               => $index + 1,
                    'product_id'       => $productId,
                    'product_image'    => $product->main_image ? url('/storage/' . $product->main_image) : null,
                    'product_name'     => $product->name ?? '',
                    'product_quantity' => $quantity,
                    'product_price'    => $price,
                    'product_size'     => $sizeName,
                    'total_price'      => $totalPrice,
                ];
            }
            $coupon = Coupon::find($request->coupon_id);
            if (!$coupon) {
                return response()->json([
                    'status' => 404,
                    'mgs'    => 'Coupon not found',
                ], 404);
            }
            if ((int) $coupon->status !== 1) {
                return response()->json([
                    'status' => 400,
                    'mgs'    => 'Coupon is inactive',
                ], 400);
            }
            if (!empty($coupon->expires_at) && now()->gt(\Carbon\Carbon::parse($coupon->expires_at))) {
                return response()->json([
                    'status' => 400,
                    'mgs'    => 'Coupon has expired',
                ], 400);
            }

            if (!is_null($coupon->min_price) && $itemsAmount < $coupon->min_price) {
                return response()->json([
                    'status' => 400,
                    'mgs'    => 'Minimum cart amount not reached for this coupon',
                ], 400);
            }

            if (!is_null($coupon->max_price) && $coupon->max_price > 0 && $itemsAmount > $coupon->max_price) {
                return response()->json([
                    'status' => 400,
                    'mgs'    => 'Cart amount exceeded for this coupon',
                ], 400);
            }

            $discount = 0;

            if ((int) $coupon->discount_type === 1) {
                $discount = ($itemsAmount * (float) $coupon->discount_value) / 100;
            } elseif ((int) $coupon->discount_type === 2) {
                $discount = (float) $coupon->discount_value;
            }

            if ($discount > $itemsAmount) {
                $discount = $itemsAmount;
            }

            $deliveryCharge = 100;
            $platformFee    = 12;

            $totalAmount = $itemsAmount - $discount + $deliveryCharge + $platformFee;

            $address = Address::where('user_id', $userId)
                ->where('address_type', 'yes')
                ->first();

            $addressDetails = null;

            if ($address) {
                $addressDetails = [
                    'id'           => $address->id,
                    'name'         => $address->name ?? '',
                    'phone_number' => $address->phone_number ?? '',
                    'address'      => $address->address ?? '',
                    'pincode'      => $address->pincode ?? '',
                    'state'        => $address->state ?? '',
                    'city'         => $address->city ?? '',
                    'address_type' => $address->address_type ?? '',
                    'latitude'     => $address->latitude ?? '',
                    'longitude'    => $address->longitude ?? '',
                    'created_by'   => $address->created_by ?? '',
                    'updated_by'   => $address->updated_by ?? '',
                    'created_at'   => $address->created_at ? $address->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at'   => $address->updated_at ? $address->updated_at->format('Y-m-d H:i:s') : '',
                ];
            }

            return response()->json([
                'status' => 200,
                'mgs'    => 'Cart Screen',
                'Product_data' => $productData,
                'coupon_details' => [
                    'id'             => $coupon->id,
                    'coupon_code'    => $coupon->coupon_code ?? '',
                    'discount_type'  => (int) $coupon->discount_type,
                    'discount_value' => $coupon->discount_value ?? '',
                    'description'    => $coupon->description ?? '',
                    'apply_for'      => $coupon->apply_for ?? '',
                    'max_price'      => $coupon->max_price ?? '',
                    'min_price'      => $coupon->min_price ?? '',
                    'order_count'    => $coupon->order_count ?? 0,
                    'coupon_status'  => (bool) $coupon->status,
                    'expires_at'     => !empty($coupon->expires_at) ? \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d H:i:s') : '',
                    'created_at'     => $coupon->created_at ? $coupon->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at'     => $coupon->updated_at ? $coupon->updated_at->format('Y-m-d H:i:s') : '',
                ],
                'addressDetails' => $addressDetails,
                'billSummary' => [
                    'total_items'     => $totalItems,
                    'items_amount'    => round($itemsAmount, 2),
                    'discount'        => round($discount, 2),
                    'delivery_charge' => round($deliveryCharge, 2),
                    'platform_fee'    => round($platformFee, 2),
                    'total_amount'    => round($totalAmount, 2),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'mgs'    => 'Something went wrong',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCoupon(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'required|exists:coupons,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 419,
                    'message' => $validator->errors()->first(),
                ], 419);
            }

            Cache::forget('coupon_id_' . Auth::id());

            return response()->json([
                'status' => 200,
                'message' => 'Coupon Removed',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
