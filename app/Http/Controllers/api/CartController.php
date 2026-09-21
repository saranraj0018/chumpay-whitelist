<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Tax;
use App\Models\Wallet;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'required|integer|not_in:0',
            'color_variant_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $changeQty = $request->quantity;

        $product = Product::findOrFail($productId);

        $userId  = auth()->id() ?? session()->getId();
        $cartKey = "cart_{$userId}";
        $cart    = Cache::get($cartKey, []);

        // BUY NOW FLAG
        $isBuyNow = $request->boolean('is_buy_now');

        $sizeId  = null;
        $colorId = null;

        /* ================= PRODUCT TYPE ================= */
        if ($product->product_type === 'variant') {

            if (!$variantId) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Variant Id is required',
                ], 422);
            }

            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $productId)
                ->with('variantValues.attribute_value.get_attribute')
                ->firstOrFail();

            $stock = $variant->stock;
            $sale_price = $variant->sale_price;
            $regular_price = $variant->regular_price;

            $attributes = $variant->variantValues->keyBy(function ($val) {
                return strtolower($val->attribute_value->get_attribute->name);
            });

            $sizeId  = $attributes['size']->attribute_value_id ?? null;
            $autoColorId = $attributes['color']->attribute_value_id ?? null;

            if ($request->filled('color_variant_id')) {
                $requestedColorId = $request->color_variant_id;

                $isValidColor = $variant->variantValues->contains(function ($val) use ($requestedColorId) {
                    return $val->attribute_value_id == $requestedColorId &&
                        strtolower($val->attribute_value->get_attribute->name) === 'color';
                });

                if (!$isValidColor) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Invalid color for this variant'
                    ], 422);
                }

                $colorId = $requestedColorId;
            } else {
                $colorId = $autoColorId;
            }
        } else {
            $stock = $product->stock;
            $sale_price = $product->sale_price;
            $regular_price = $product->regular_price;
        }

        /* =========================================================
         * BUY NOW: DO NOT MERGE → SINGLE UNIQUE ENTRY ONLY
         * ========================================================= */
        if ($isBuyNow) {
            if ($changeQty > $stock) {
                return response()->json([
                    'status' => 422,
                    'message' => "Only {$stock} item(s) available",
                ], 422);
            }

            $existingBuyNowIndex = collect($cart)->search(function ($item) {
                return !empty($item['is_buy_now']);
            });

            // Check if same buy_now item already exists
            $existingIndex = collect($cart)->search(function ($item) use ($productId, $variantId, $colorId, $sizeId) {
                return $item['product_id'] == $productId &&
                    ($item['variant_id'] ?? null) == $variantId &&
                    ($item['color_variant_id'] ?? null) == $colorId &&
                    ($item['size_variant_id'] ?? null) == $sizeId &&
                    !empty($item['is_buy_now']);
            });
            // If already exists → DO NOTHING (no duplicate, no qty increase)
            if ($existingIndex !== false) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Buy Now item already exists',
                    'checkout_id' => $cart[$existingIndex]['checkout_id'],
                ]);
            }

            if ($existingBuyNowIndex !== false) {
                $cart[$existingBuyNowIndex]['is_buy_now'] = false;
                $cart[$existingBuyNowIndex]['selected_for_checkout'] = false;
            }

            // Otherwise create new
            $checkoutId = uniqid('buy_', true);
            $cart[] = [
                'product_id'       => $productId,
                'variant_id'       => $variantId,
                'color_variant_id' => $colorId,
                'size_variant_id'  => $sizeId,
                'sale_price'       => $sale_price,
                'regular_price'    => $regular_price,
                'quantity'         => $changeQty, // keep original qty
                'selected_for_checkout' => true,
                'is_buy_now'       => true,
                'checkout_id'      => $checkoutId,
            ];
            Cache::put($cartKey, array_values($cart), now()->addDays(7));
            return response()->json([
                'status' => 200,
                'message' => 'Buy Now item ready',
                'checkout_id' => $checkoutId,
            ]);
        }

        /* =========================================================
         * NORMAL CART (MERGE LOGIC)
         * ========================================================= */
        $index = collect($cart)->search(function ($item) use ($productId, $variantId, $colorId, $sizeId) {
            return $item['product_id'] == $productId &&
                ($item['variant_id'] ?? null) == $variantId &&
                ($item['color_variant_id'] ?? null) == $colorId &&
                ($item['size_variant_id'] ?? null) == $sizeId &&
                empty($item['is_buy_now']); // ❗ ignore buy_now items
        });

        if ($index !== false) {

            $newQty = $cart[$index]['quantity'] + $changeQty;

            if ($newQty <= 0) {
                unset($cart[$index]);
            } else {

                if ($newQty > $stock) {
                    return response()->json([
                        'status' => 422,
                        'message' => "Only {$stock} item(s) available",
                    ], 422);
                }

                $cart[$index]['quantity'] = $newQty;
            }
        } else {
            if ($changeQty <= 0) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Item not found to decrease',
                ], 404);
            }

            if ($changeQty > $stock) {
                return response()->json([
                    'status' => 422,
                    'message' => "Only {$stock} item(s) available",
                ], 422);
            }

            $cart[] = [
                'product_id'       => $productId,
                'variant_id'       => $variantId,
                'color_variant_id' => $colorId,
                'size_variant_id'  => $sizeId,
                'sale_price'       => $sale_price,
                'regular_price'    => $regular_price,
                'quantity'         => $changeQty,
                'selected_for_checkout' => false,
                'is_buy_now'       => false,
            ];
        }

        Cache::put($cartKey, array_values($cart), now()->addDays(7));

        return response()->json([
            'status' => 200,
            'message' => 'Cart updated successfully',
            'cart' => array_values($cart),
        ]);
    }

    public function getCart(Request $request)
    {
        $userId = auth()->id();

        if (empty($userId)) {
            return response()->json([
                "status" => 401,
                "message" => "Unauthorized"
            ]);
        }

        $cartKey = "cart_{$userId}";
        $cart_list = Cache::get($cartKey, []);
        $data = [];

        foreach ($cart_list as $item) {

            $product = Product::with([
                'product_variant.variantValues.attribute_value.get_attribute'
            ])->find($item['product_id']);

            if (!$product) continue;

            $quantity = (int) ($item['quantity'] ?? 1);

            /* ================= SINGLE PRODUCT ================= */
            if ($product->product_type == 'single') {
                if ($product->stock <= 0) continue;

                $price = $product->sale_price > 0 ? $product->sale_price * $quantity: $product->regular_price * $quantity;

                $newItem = [
                    "variant_id" => null,
                    "stock" => $product->stock ?? 0,
                    "product_id" => $product->id,
                    "product_name" => $product->name,
                    "product_image" => $product->main_image
                        ? url('storage/' . $product->main_image)
                        : null,
                    "product_size" => null,
                    "product_variant_size_id" => null,
                    "product_variant_color_id" => null,
                    "quantity" => $quantity,
                    "product_price" => (float) $price // single item price only
                ];

                $existingIndex = collect($data)->search(function ($d) use ($newItem) {
                    return $d['product_id'] == $newItem['product_id']
                        && $d['variant_id'] == $newItem['variant_id'];
                });

                if ($existingIndex !== false) {
                    $data[$existingIndex]['quantity'] += $quantity;
                    $data[$existingIndex]['product_price'] =
                        $data[$existingIndex]['product_price'] * $data[$existingIndex]['quantity'];
                } else {
                    $data[] = $newItem;
                }
            }

            /* ================= VARIANT PRODUCT ================= */
            if ($product->product_type == 'variant' && !empty($item['variant_id'])) {
                $variant = $product->product_variant
                    ->where('id', $item['variant_id'])
                    ->first();

                if (!$variant || $variant->stock <= 0) continue;

                $price = $variant->sale_price > 0 ? $variant->sale_price * $quantity: $variant->regular_price * $quantity;

                $size = null;
                $sizeId = null;
                $colorId = null;

                foreach ($variant->variantValues as $val) {
                    if (
                        $val->attribute_value->get_attribute &&
                        strtolower($val->attribute_value->get_attribute->name) == 'size'
                    ) {
                        $size = $val->attribute_value->value ?? null;
                        $sizeId = $val->attribute_value_id;
                    }

                    if (
                        $val->attribute_value->get_attribute &&
                        strtolower($val->attribute_value->get_attribute->name) == 'color'
                    ) {
                        $colorId = $val->attribute_value_id;
                    }
                }

                $newItem = [
                    "variant_id" => $variant->id,
                    "stock" => $variant->stock ?? 0,
                    "product_id" => $product->id,
                    "product_name" => $product->name,
                    "product_image" => $variant->cover_image
                        ? url('storage/' . $variant->cover_image)
                        : ($product->main_image
                            ? url('storage/' . $product->main_image)
                            : null),
                    "product_size" => $size,
                    "product_variant_size_id" => $sizeId,
                    "product_variant_color_id" => $colorId,
                    "quantity" => $quantity,
                    "product_price" => (float) $price // single item price only
                ];

                $existingIndex = collect($data)->search(function ($d) use ($newItem) {
                    return $d['product_id'] == $newItem['product_id']
                        && $d['variant_id'] == $newItem['variant_id'];
                });

                if ($existingIndex !== false) {
                    $data[$existingIndex]['quantity'] += $quantity;

                    $data[$existingIndex]['product_price'] =
                        $data[$existingIndex]['product_price'] * $data[$existingIndex]['quantity'];
                } else {
                    $data[] = $newItem;
                }
            }
        }

        // Correct total amount calculation
        $totalItems = count($data);

        $totalAmount = array_sum(array_map(function ($item) {
            return $item['product_price'];
        }, $data));

        return response()->json([
            "status" => 200,
            "message" => "Cart details fetched Successfully!",
            "total_item" => $totalItems,
            "total_amount" => (float) $totalAmount,
            "data" => $data,
        ]);
    }

    public function removeCartItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'        => 'required|integer',
            'variant_id'        => 'nullable|integer',
            'color_variant_id'  => 'nullable|integer',
            'size_variant_id'   => 'nullable|integer',
            'quantity'          => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $colorId   = $request->color_variant_id;
        $sizeId    = $request->size_variant_id;

        $userId  = auth()->id() ?? session()->getId();
        $cartKey = "cart_{$userId}";
        $cart    = Cache::get($cartKey, []);

        // Find matching cart items (buy_now + normal cart both)
        $matchedItems = collect($cart)->filter(function ($item) use (
            $productId,
            $variantId,
            $colorId,
            $sizeId
        ) {
            return $item['product_id'] == $productId &&
                ($item['variant_id'] ?? null) == $variantId &&
                ($item['color_variant_id'] ?? null) == $colorId &&
                ($item['size_variant_id'] ?? null) == $sizeId;
        });

        if ($matchedItems->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'Cart item not found'
            ], 404);
        }

        /**
         * If request quantity = 0
         * → remove all matching items completely
         */
        if ($request->has('quantity') && (int) $request->quantity === 0) {

            $cart = collect($cart)->reject(function ($item) use (
                $productId,
                $variantId,
                $colorId,
                $sizeId
            ) {
                return $item['product_id'] == $productId &&
                    ($item['variant_id'] ?? null) == $variantId &&
                    ($item['color_variant_id'] ?? null) == $colorId &&
                    ($item['size_variant_id'] ?? null) == $sizeId;
            })->values()->toArray();

            Cache::put($cartKey, $cart, now()->addDays(7));

            return response()->json([
                'status' => 200,
                'message' => 'All matching cart items removed completely',
                'cart' => $cart
            ]);
        }

        /**
         * If quantity > 1
         * → reduce quantity by 1 from first matched normal cart item
         * Else remove item
         */
        foreach ($cart as $index => $item) {
            if (
                $item['product_id'] == $productId &&
                ($item['variant_id'] ?? null) == $variantId &&
                ($item['color_variant_id'] ?? null) == $colorId &&
                ($item['size_variant_id'] ?? null) == $sizeId
            ) {
                if (($item['quantity'] ?? 1) > 1) {
                    $cart[$index]['quantity'] -= 1;
                    $message = 'Item quantity reduced successfully';
                } else {
                    unset($cart[$index]);
                    $message = 'Item removed from cart successfully';
                }

                break;
            }
        }

        $cart = array_values($cart);

        Cache::put($cartKey, $cart, now()->addDays(7));

        return response()->json([
            'status' => 200,
            'message' => $message,
            'cart' => $cart
        ]);
    }

    public function cartDetail(Request $request)
    {
        $userId = auth()->id();
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!$userId) {
            return response()->json([
                "status" => 401,
                "message" => "Unauthorized"
            ]);
        }

        $cartKey = "cart_{$userId}";
        $cart_list = Cache::get($cartKey, []);

        $isBuyNow   = $request->boolean('is_buy_now');
        $checkoutId = $request->checkout_id;

        /* ================= FILTER ================= */
        if ($isBuyNow) {

            $filteredCart = array_filter($cart_list, function ($item) use ($checkoutId) {

                if (empty($item['is_buy_now'])) return false;

                if (!empty($checkoutId)) {
                    return $item['checkout_id'] === $checkoutId;
                }

                return true;
            });

            // fallback if empty
            $cart_list = !empty($filteredCart) ? $filteredCart : $cart_list;

            $groupedCart = $cart_list;
        } else {
            $groupedCart = [];
            foreach ($cart_list as $item) {
                $key = $item['product_id'] . '_' .
                    ($item['variant_id'] ?? 0) . '_' .
                    ($item['color_variant_id'] ?? 0) . '_' .
                    ($item['size_variant_id'] ?? 0);
                if (!isset($groupedCart[$key])) {
                    $groupedCart[$key] = $item;
                } else {
                    $groupedCart[$key]['quantity'] += $item['quantity'];
                }
            }
        }
        $productData = [];
        $totalItems  = 0;
        $subtotal    = 0;
        $coupon_discount = 0;
        foreach ($groupedCart as $item) {
            $product = Product::with([
                'product_variant.variantValues.attribute_value.get_attribute',
            ])->find($item['product_id']);
            if (!$product) continue;
            $quantity = (int) $item['quantity'];
            /* ================= SINGLE ================= */
            if ($product->product_type == 'single') {
                if ($product->stock <= 0) continue;
                $originalPrice   = (float) ($product->regular_price ?? 0);
                $discountedPrice = ($product->sale_price > 0)
                    ? (float) $product->sale_price
                    : $originalPrice;
                $subtotal   += $discountedPrice * $quantity;
                $totalItems += $quantity;
                $productData[] = [
                    "id" => $product->id,
                    "name" => $product->name,
                    "image" => $product->main_image ? url('storage/' . $product->main_image) : '',
                    "originalPrice" => $originalPrice * $quantity,
                    "discountedPrice" => $discountedPrice * $quantity,
                    "variation" => null,
                    "product_type" => $product->product_type,
                    "variant_id" => null,
                    "stock" => $product->stock ?? 0,
                    "quantity" => $quantity,
                ];
            }

            /* ================= VARIANT ================= */
            if ($product->product_type == 'variant' && !empty($item['variant_id'])) {
                $variant = $product->product_variant
                    ->where('id', $item['variant_id'])
                    ->first();
                if (!$variant || $variant->stock <= 0) continue;
                $originalPrice   = (float) ($variant->regular_price ?? 0);
                $discountedPrice = ($variant->sale_price > 0)
                    ? (float) $variant->sale_price
                    : $originalPrice;
                $subtotal   += $discountedPrice * $quantity;
                $totalItems += $quantity;

                $variantParts = [];
                $size = null;
                $sizeId = null;
                $colorId = null;

                foreach ($variant->variantValues as $val) {
                    $attr = $val->attribute_value->get_attribute ?? null;
                    if (!$attr) continue;
                    $attrName = strtolower($attr->name);
                    $attrVal  = $val->attribute_value->value ?? null;
                    $variantParts[] = $attrVal;
                    if ($attrName == 'size') {
                        $size = $attrVal;
                        $sizeId = $val->attribute_value_id;
                    }
                    if ($attrName == 'color') {
                        $colorId = $val->attribute_value_id;
                    }
                }

                $productData[] = [
                    "id" => $product->id,
                    "name" => $product->name,
                    "image" => $variant->cover_image
                        ? url('storage/' . $variant->cover_image)
                        : ($product->main_image ? url('storage/' . $product->main_image) : ''),
                    "originalPrice" => $originalPrice * $quantity,
                    "discountedPrice" => $discountedPrice * $quantity,
                    "variation" => [
                        "id" => $variant->id,
                        "variant_name" => implode(' / ', array_filter($variantParts)),
                        "size" => $size,
                        "size_id" => $sizeId,
                        "color_id" => $colorId,
                        "price" => $discountedPrice * $quantity,
                    ],
                    "product_type" => $product->product_type,
                    "variant_id" => $variant->id,
                    "stock" => $variant->stock ?? 0,
                    "quantity" => $quantity,
                ];
            }
        }

        /* ================= COUPON ================= */
        $coupon = null;
        $coupon_status = true;

        $coupon_id = !empty($request['coupon_id'])
            ? $request['coupon_id']
            : Cache::get('coupon_id_' . Auth::id(), null);

        if (!empty($coupon_id)) {
            $key = 'coupon_id_' . Auth::id();
            $coupon = Coupon::where('id', $coupon_id)
                ->where('status', 1)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', now()->toDateString());
                })
                ->first();

            if (!empty($coupon)) {
                $coupon_discount = self::getCouponDetails($coupon, $subtotal);
                if ($coupon_discount > 0) {
                    Cache::put($key, $coupon_id, now()->addMinutes(5));
                } else {
                    $coupon_status = false;
                    Cache::forget($key);
                }
            } else {
                $coupon_status = false;
            }
        }

        /* ================= ADDRESS ================= */
        $address = Address::where('created_by', Auth::id())
            ->where('is_default', 1)
            ->first();

        $finalDeliveryCharge = 0;
        $tax = Tax::first();
        $taxPercentage = $tax ? $tax->percent : 0;
        $defaultShipping = $tax ? $tax->default_shipping : 0;
        $platformFee = $tax ? $tax->platform_fee : 0;
        $shipping_get = Shipping::where('minimum_delivery_amount', '<=', $subtotal)->where('maximum_delivery_amount', '>=', $subtotal)
       ->where('status', 1)->first();
       if(!empty($shipping_get)){
        $finalDeliveryCharge = $shipping_get->delivery_fee;
       }else{
        $finalDeliveryCharge = $defaultShipping;
       }
        /* ================= FINAL ================= */
        $totalAmount = ($subtotal + $finalDeliveryCharge + $platformFee + ($subtotal * $taxPercentage) / 100) - $coupon_discount;
        return response()->json([
            'status'         => 200,
            "message"        => "Cart details fetched successfully!",
            "productData"    => array_values($productData),
            "billSummary"    => [
                "totalItems"     => $totalItems,
                "itemsAmount"    => number_format($subtotal, 2),
                "discount"       => number_format($coupon_discount, 2),
                "deliveryCharge" => number_format($finalDeliveryCharge, 2),
                "totalAmount"    => number_format(max(0, $totalAmount), 2),
                "TAX" => number_format(($subtotal * $taxPercentage) / 100, 2),
                "platform_fee" => number_format(max(0, $platformFee), 2),
                "SGST" => '0',
                "IGST" => '0',
            ],
            "couponDetail"   => $coupon ?? null,
            "addressDetails" => $address,
            "coupon_status"  => $coupon_status,
            "wallet_amount" => optional($wallet)->balance + optional($wallet)->bonus_balance,
        ]);
    }

    // ================= COUPON DETAILS =================
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

        return $discountValue; // fixed
    }
}
