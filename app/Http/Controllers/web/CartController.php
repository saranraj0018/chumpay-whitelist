<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipping;
use App\Models\Tax;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Please login to continue',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'product_id'       => 'required|integer|exists:products,id',
            'variant_id'       => 'nullable|integer|exists:product_variants,id',
            'quantity'         => 'required|integer|not_in:0',
            'color_variant_id' => 'nullable|integer',
            'matrix'           => 'nullable|array',
            'matrix.*.size'    => 'nullable|string',
            'matrix.*.color'   => 'nullable|string',
            'matrix.*.qty'     => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $changeQty = (int) $request->quantity;
        $matrix    = $request->input('matrix', []); // [{size, color, qty}, ...] for bulk

        $product = Product::findOrFail($productId);
        // Calculate quantity for bulk products from matrix
        if ($product->product_type === 'bulk' && !empty($matrix)) {
            $changeQty = collect($matrix)->sum(function ($item) {
                return (int) ($item['qty'] ?? 0);
            });
        }
        $userId  = Auth::id();
        $cartKey = "cart_{$userId}";
        $cart    = Cache::get($cartKey, []);

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

            $sale_price    = $variant->sale_price;
            $regular_price = $variant->regular_price;

            $attributes = $variant->variantValues->keyBy(function ($val) {
                return strtolower($val->attribute_value->get_attribute->name);
            });

            $sizeId      = $attributes['size']->attribute_value_id ?? null;
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
        } elseif ($product->product_type === 'bulk') {
            $sale_price    = $product->per_piece_price ?? 0;
            $regular_price = $product->per_piece_price ?? 0;
        } else { // single
            $sale_price    = $product->sale_price;
            $regular_price = $product->regular_price;
        }

        /* ================= BUY NOW ================= */
        if ($isBuyNow) {

            $existingIndex = collect($cart)->search(function ($item) use ($productId, $variantId, $colorId, $sizeId) {
                return $item['product_id'] == $productId &&
                    ($item['variant_id'] ?? null) == $variantId &&
                    ($item['color_variant_id'] ?? null) == $colorId &&
                    ($item['size_variant_id'] ?? null) == $sizeId &&
                    !empty($item['is_buy_now']);
            });

            if ($existingIndex !== false) {
                // Keep quantity and matrix synchronized
                if ($product->product_type === 'bulk') {

                    $changeQty = collect($matrix)->sum(function ($item) {
                        return (int) ($item['qty'] ?? 0);
                    });
                }

                $cart[$existingIndex]['quantity'] = $changeQty;
                $cart[$existingIndex]['matrix']   = $matrix;
                Cache::put($cartKey, array_values($cart), now()->addDays(7));

                return response()->json([
                    'status' => 200,
                    'message' => 'Buy Now item ready',
                    'checkout_id' => $cart[$existingIndex]['checkout_id'],
                ]);
            }

            // A NEW / different Buy Now is starting. If a previous, unfinished
            // Buy Now item exists, absorb it into the normal cart (merged with
            // a matching line if one exists) instead of deleting it outright.
            // NOTE: isAddToCart defaults to false here -> absorbing a stale
            // buy-now must NOT bump the quantity of a matching cart line.
            $cart = self::absorbStaleBuyNow($cart);

            $checkoutId = uniqid('buy_', true);
            $cart[] = [
                'product_id'            => $productId,
                'variant_id'            => $variantId,
                'color_variant_id'      => $colorId,
                'size_variant_id'       => $sizeId,
                'sale_price'            => $sale_price,
                'regular_price'         => $regular_price,
                // 'quantity'              => $changeQty,
                'quantity' => $product->product_type === 'bulk'
                    ? collect($matrix)->sum(function ($item) {
                        return (int) ($item['qty'] ?? 0);
                    })
                    : $changeQty,
                'matrix'                => $matrix,
                'selected_for_checkout' => true,
                'is_buy_now'            => true,
                'checkout_id'           => $checkoutId,
            ];

            Cache::put($cartKey, array_values($cart), now()->addDays(7));

            return response()->json([
                'status' => 200,
                'message' => 'Buy Now item ready',
                'checkout_id' => $checkoutId,
            ]);
        }

        /* ================= NORMAL CART (MERGE) ================= */
        $index = collect($cart)->search(function ($item) use ($productId, $variantId, $colorId, $sizeId) {
            return $item['product_id'] == $productId &&
                ($item['variant_id'] ?? null) == $variantId &&
                ($item['color_variant_id'] ?? null) == $colorId &&
                ($item['size_variant_id'] ?? null) == $sizeId &&
                empty($item['is_buy_now']);
        });

        if ($index !== false) {
            $newQty = $cart[$index]['quantity'] + $changeQty;
            if ($newQty <= 0) {
                unset($cart[$index]);
            } else {
                $cart[$index]['quantity'] = $newQty;
                // accumulate matrix breakdown across repeated adds (merge by color+size)
                // isAddToCart = true -> this IS an explicit add-to-cart action,
                // so matching color/size cells should have their qty increased too.
                if (!empty($matrix)) {
                    $cart[$index]['matrix'] = self::mergeMatrix($cart[$index]['matrix'] ?? [], $matrix, true);
                }
            }
        } else {
            if ($changeQty <= 0) {
                return response()->json(['status' => 404, 'message' => 'Item not found to decrease'], 404);
            }
            $cart[] = [
                'product_id'            => $productId,
                'variant_id'            => $variantId,
                'color_variant_id'      => $colorId,
                'size_variant_id'       => $sizeId,
                'sale_price'            => $sale_price,
                'regular_price'         => $regular_price,
                'quantity'              => $changeQty,
                'matrix'                => $matrix,
                'selected_for_checkout' => false,
                'is_buy_now'            => false,
            ];
        }

        Cache::put($cartKey, array_values($cart), now()->addDays(7));

        return response()->json([
            'status' => 200,
            'message' => 'Cart updated successfully',
            'cart' => array_values($cart),
        ]);
    }

    public function cartDetail(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                "status" => 401,
                "message" => "Unauthorized"
            ]);
        }

        $wallet    = Wallet::where('user_id', $userId)->first();
        $cartKey   = "cart_{$userId}";
        $cart_list = Cache::get($cartKey, []);

        $isBuyNow   = $request->boolean('is_buy_now');
        $isCartView = $request->boolean('is_cart');
        $checkoutId = $request->checkout_id;

        $keepCheckoutId = $isBuyNow ? $checkoutId : null;
        $hasStaleBuyNow = collect($cart_list)->contains(function ($item) use ($keepCheckoutId) {
            if (empty($item['is_buy_now'])) return false;
            if ($keepCheckoutId !== null && ($item['checkout_id'] ?? null) === $keepCheckoutId) return false;
            return true;
        });

        if ($hasStaleBuyNow) {
            // Passive cleanup on page load, NOT a user "add to cart" action ->
            // isAddToCart defaults to false, so quantity is left untouched.
            $cart_list = self::absorbStaleBuyNow($cart_list, $keepCheckoutId);
            Cache::put($cartKey, $cart_list, now()->addDays(7));
        }

        /* ================= FILTER ================= */
        if ($isBuyNow) {
            $filteredCart = array_filter($cart_list, function ($item) use ($checkoutId) {
                if (empty($item['is_buy_now'])) return false;
                if (!empty($checkoutId)) {
                    return ($item['checkout_id'] ?? null) === $checkoutId;
                }
                return true;
            });
            $groupedCart = array_values(!empty($filteredCart) ? $filteredCart : []);
        } elseif ($isCartView) {
            $grouped = [];
            foreach ($cart_list as $item) {
                if (!empty($item['is_buy_now'])) {
                    $key = 'buynow_' . ($item['checkout_id'] ?? uniqid());
                    $grouped[$key] = $item;
                    continue;
                }
                $key = 'cart_' . $item['product_id'] . '_' .
                    ($item['variant_id'] ?? 0) . '_' .
                    ($item['color_variant_id'] ?? 0) . '_' .
                    ($item['size_variant_id'] ?? 0);
                if (!isset($grouped[$key])) {
                    $grouped[$key] = $item;
                } else {
                    // Combining genuinely separate existing cart lines for display
                    // -> always sum (not a buy-now absorption case).
                    $grouped[$key]['quantity'] += $item['quantity'];
                    if (!empty($item['matrix'])) {
                        $grouped[$key]['matrix'] = self::mergeMatrix(
                            $grouped[$key]['matrix'] ?? [],
                            $item['matrix'],
                            true
                        );
                    }
                }
            }
            $groupedCart = array_values($grouped);
        } else {
            $grouped = [];
            foreach ($cart_list as $item) {
                if (!empty($item['is_buy_now'])) continue;
                if (empty($item['selected_for_checkout'])) continue;
                $key = $item['product_id'] . '_' .
                    ($item['variant_id'] ?? 0) . '_' .
                    ($item['color_variant_id'] ?? 0) . '_' .
                    ($item['size_variant_id'] ?? 0);
                if (!isset($grouped[$key])) {
                    $grouped[$key] = $item;
                } else {
                    $grouped[$key]['quantity'] += $item['quantity'];
                    if (!empty($item['matrix'])) {
                        $grouped[$key]['matrix'] = self::mergeMatrix(
                            $grouped[$key]['matrix'] ?? [],
                            $item['matrix'],
                            true
                        );
                    }
                }
            }
            $groupedCart = array_values($grouped);
        }

        $productData     = [];
        $totalItems      = 0;
        $subtotal        = 0;
        $coupon_discount = 0;

        foreach ($groupedCart as $item) {
            $product = Product::with([
                'product_variant.variantValues.attribute_value.get_attribute',
                'bulk_product',
            ])->find($item['product_id']);
            if (!$product) continue;
            // $quantity = (int) $item['quantity'];
            if ($product->product_type === 'bulk') {
                $quantity = collect($item['matrix'] ?? [])->sum(function ($item) {
                    return (int) ($item['qty'] ?? 0);
                });
            } else {
                $quantity = (int) $item['quantity'];
            }
            if ($quantity <= 0) continue;

            if ($product->product_type == 'single') {

                $originalPrice   = (float) ($product->regular_price ?? 0);
                $discountedPrice = ($product->sale_price > 0)
                    ? (float) $product->sale_price
                    : $originalPrice;

                $subtotal   += $discountedPrice * $quantity;
                $totalItems += $quantity;

                $productData[] = [
                    "id"              => $product->id,
                    "name"            => $product->name,
                    "image"           => $product->main_image ? url('storage/' . $product->main_image) : '',
                    "originalPrice"   => $originalPrice * $quantity,
                    "discountedPrice" => $discountedPrice * $quantity,
                    "variation"       => null,
                    "matrix"          => [],
                    "product_type"    => $product->product_type,
                    "variant_id"      => null,
                    "stock"           => $product->stock ?? 0,
                    "quantity"        => $quantity,
                ];
            }

            if ($product->product_type == 'variant' && !empty($item['variant_id'])) {
                $variant = $product->product_variant
                    ->where('id', $item['variant_id'])
                    ->first();

                if (!$variant) continue;

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

                $hasVariantImage = (bool) $variant->cover_image;

                $productData[] = [
                    "id"              => $product->id,
                    "name"            => $product->name,
                    "image"           => $variant->cover_image
                        ? url('storage/' . $variant->cover_image)
                        : ($product->main_image ? url('storage/' . $product->main_image) : ''),
                    "originalPrice"   => $originalPrice * $quantity,
                    "discountedPrice" => $discountedPrice * $quantity,
                    "variation"       => [
                        "id"           => $variant->id,
                        "variant_name" => implode(' / ', array_filter($variantParts)),
                        "size"         => $size,
                        "size_id"      => $sizeId,
                        "color_id"     => $colorId,
                        "price"        => $discountedPrice * $quantity,
                    ],
                    "matrix"          => [],
                    "product_type"    => $product->product_type,
                    "variant_id"      => $variant->id,
                    "stock"           => $variant->stock ?? 0,
                    "quantity"        => $quantity,
                    "hasVariantImage" => $hasVariantImage,
                    // per-piece (unit) sale price, shown in the "Per Piece" column
                    // only when the variant has its own image
                    "perPiece"        => $hasVariantImage ? $discountedPrice : null,
                ];
            }

            if ($product->product_type == 'bulk') {
                $tiers    = $product->bulk_product->sortBy('minimum')->values();
                $perPiece = (float) ($product->per_piece_price ?? 0);

                $matchedTier = $tiers->first(function ($t) use ($quantity) {
                    $min = $t->minimum ?? 0;
                    $max = $t->maximum ?? null;
                    return $quantity >= $min && ($max === null || $quantity <= $max);
                });

                if ($matchedTier) {
                    $lineTotal = (float) ($matchedTier->sale_price ?? $matchedTier->regular_price ?? 0);
                    $tierMin   = $matchedTier->minimum ?? 0;
                    $tierMax   = $matchedTier->maximum ?? null;
                } else {
                    $rangedTiers = $tiers->filter(fn($t) => !is_null($t->maximum))->values();
                    $topTier     = $rangedTiers->last();
                    $tierMin     = $topTier->minimum ?? 0;
                    $tierMax     = $topTier->maximum ?? null;

                    if ($topTier && $quantity > $topTier->maximum) {
                        $topPrice   = (float) ($topTier->sale_price ?? $topTier->regular_price ?? 0);
                        $extraUnits = $quantity - $topTier->maximum;
                        $lineTotal  = $topPrice + ($extraUnits * $perPiece);
                    } else {
                        $lineTotal = $perPiece * $quantity;
                    }
                }

                $tierRange = $tierMax !== null
                    ? ($tierMin . '-' . $tierMax)
                    : ($tierMin . '+');

                $subtotal   += $lineTotal;
                $totalItems += $quantity;

                $productData[] = [
                    "id"              => $product->id,
                    "name"            => $product->name,
                    "image"           => $product->main_image ? url('storage/' . $product->main_image) : '',
                    "originalPrice"   => $lineTotal,
                    "discountedPrice" => $lineTotal,
                    "variation"       => null,
                    "matrix"          => $item['matrix'] ?? [],
                    "tierRange"       => $tierRange,
                    "tierPrice"       => $lineTotal,
                    "perPiece"        => $perPiece,
                    "product_type"    => $product->product_type,
                    "variant_id"      => null,
                    "stock"           => 0,
                    "quantity"        => $quantity,
                ];
            }
        }

        /* ================= COUPON (FIXED) ================= */
        $coupon         = null;
        $coupon_status  = true;
        $coupon_message = null;

        $requestedCouponId   = $request->filled('coupon_id') ? $request->coupon_id : null;
        $requestedCouponCode = $request->filled('coupon_code') ? trim($request->coupon_code) : null;
        $removeCouponFlag    = $request->boolean('remove_coupon');

        if ($removeCouponFlag) {
            Cache::forget('coupon_id_' . $userId);
        } elseif ($requestedCouponId || $requestedCouponCode) {
            $coupon_id = $requestedCouponId;
            if (empty($coupon_id) && $requestedCouponCode) {
                $byCode = Coupon::where('coupon_code', $requestedCouponCode)
                    ->where('status', 1)
                    ->first();

                if ($byCode) {
                    $coupon_id = $byCode->id;
                } else {
                    $coupon_status  = false;
                    $coupon_message = 'This coupon code does not exist.';
                }
            }

            if ($coupon_id) {
                $coupon = Coupon::where('id', $coupon_id)
                    ->where('status', 1)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhereDate('expires_at', '>=', now()->toDateString());
                    })
                    ->first();

                if (!$coupon) {
                    $coupon_status  = false;
                    $coupon_message = 'This coupon is not available or has expired.';
                } else {
                    $coupon_discount = self::getCouponDetails($coupon, $subtotal);
                    if ($coupon_discount > 0) {
                        Cache::put('coupon_id_' . $userId, $coupon_id, now()->addMinutes(30));
                    } else {
                        $coupon_status  = false;
                        $coupon_message = 'This coupon is not applicable to your current cart total.';
                        $coupon         = null;
                        Cache::forget('coupon_id_' . $userId);
                    }
                }
            }
        } else {
            $cachedCouponId = Cache::get('coupon_id_' . $userId, null);
            if (!empty($cachedCouponId)) {
                $coupon = Coupon::where('id', $cachedCouponId)
                    ->where('status', 1)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhereDate('expires_at', '>=', now()->toDateString());
                    })
                    ->first();

                if ($coupon) {
                    $coupon_discount = self::getCouponDetails($coupon, $subtotal);
                    if ($coupon_discount <= 0) {
                        $coupon_status = false;
                        $coupon        = null;
                        Cache::forget('coupon_id_' . $userId);
                    }
                } else {
                    $coupon_status = false;
                    Cache::forget('coupon_id_' . $userId);
                }
            }
        }

        /* ================= ADDRESS ================= */
        $address = Address::where('created_by', $userId)
            ->where('is_default', 1)
            ->first();

        /* ================= CHARGES ================= */
        $tax = Tax::first();
        $taxPercentage   = $tax ? $tax->percent : 0;
        $defaultShipping = $tax ? $tax->default_shipping : 0;
        $platformFee     = $tax ? $tax->platform_fee : 0;

        $shipping_get = Shipping::where('minimum_delivery_amount', '<=', $subtotal)
            ->where('maximum_delivery_amount', '>=', $subtotal)
            ->where('status', 1)
            ->first();

        $finalDeliveryCharge = !empty($shipping_get) ? $shipping_get->delivery_fee : $defaultShipping;

        $taxableBase = max(0, $subtotal - $coupon_discount);
        $taxAmount   = number_format(($subtotal * $taxPercentage) / 100, 2);

        $taxableBase         = (float) str_replace(',', '', $taxableBase);
        $finalDeliveryCharge = (float) str_replace(',', '', $finalDeliveryCharge);
        $platformFee         = (float) str_replace(',', '', $platformFee);
        $taxAmount           = (float) str_replace(',', '', $taxAmount);

        $totalAmount = $taxableBase + $finalDeliveryCharge + $platformFee + $taxAmount;

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
                "TAX"            => number_format($taxAmount, 2),
                "platform_fee"   => number_format(max(0, $platformFee), 2),
                "SGST"           => '0',
                "IGST"           => '0',
            ],
            "couponDetail"   => $coupon ?? null,
            "addressDetails" => $address,
            "coupon_status"  => $coupon_status,
            "coupon_message" => $coupon_message,   // NEW — dedicated field, only set on real coupon failure
            "wallet_amount"  => optional($wallet)->balance + optional($wallet)->bonus_balance,
        ]);
    }

    public function selectAllForCheckout(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $cartKey = "cart_{$userId}";
        $cart    = Cache::get($cartKey, []);

        $hasNormalItem = false;
        $hasBuyNowItem = false;

        foreach ($cart as $i => $item) {
            if (empty($item['is_buy_now'])) {
                $cart[$i]['selected_for_checkout'] = true;
                $hasNormalItem = true;
            } else {
                $hasBuyNowItem = true;
            }
        }

        // Nothing at all in the cart
        if (!$hasNormalItem && !$hasBuyNowItem) {
            return response()->json(['status' => 400, 'message' => 'Your cart is empty'], 400);
        }

        // Only buy-now item(s) present — that's a valid checkout, just nothing to "select"
        if (!$hasNormalItem && $hasBuyNowItem) {
            return response()->json(['status' => 200, 'message' => 'Buy now item ready for checkout']);
        }

        Cache::put($cartKey, array_values($cart), now()->addDays(7));

        return response()->json(['status' => 200, 'message' => 'Ready for checkout']);
    }

    public function clearCart(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        Cache::forget("cart_{$userId}");
        Cache::forget("coupon_id_{$userId}");

        return response()->json([
            'status'  => 200,
            'message' => 'Cart cleared successfully',
        ]);
    }

    public function saveDeliveryAddress(Request $request)
    {
        $validated = $request->validate([
            'address_id'   => 'nullable|integer|exists:addresses,id',
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_type' => 'required|in:home,work,other',
            'state'        => 'required|string|max:100',
            'address'      => 'required|string|max:500',
            'city'         => 'required|string|max:100',
            'pincode'      => 'required|string|max:10',
            'landmark'     => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();

        // Cast explicitly — JS may send "12" as string, "" as empty string, or omit it
        $addressId = isset($validated['address_id']) && $validated['address_id'] !== ''
            ? (int) $validated['address_id']
            : null;

        Log::info('Address save called', ['address_id' => $addressId, 'user_id' => $userId]);

        if ($addressId) {
            $address = Address::where('id', $addressId)
                ->where('created_by', $userId)
                ->first();

            if (!$address) {
                Log::warning('Edit attempted but address not found for this user', [
                    'address_id' => $addressId,
                    'user_id'    => $userId,
                ]);
                return response()->json(['status' => 404, 'message' => 'Address not found.'], 404);
            }

            $address->update([
                'name'         => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'address_type' => $validated['address_type'],
                'state'        => $validated['state'],
                'address'      => $validated['address'],
                'city'         => $validated['city'],
                'pincode'      => $validated['pincode'],
                'landmark'     => $validated['landmark'] ?? null,
                'created_by'   => $userId
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Address updated successfully.',
                'address' => $address->fresh(),
            ]);
        }

        // No address_id => genuinely a new address
        $address = Address::create([
            'created_by'      => $userId,
            'name'         => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'address_type' => $validated['address_type'],
            'state'        => $validated['state'],
            'address'      => $validated['address'],
            'city'         => $validated['city'],
            'pincode'      => $validated['pincode'],
            'landmark'     => $validated['landmark'] ?? null,
            'is_default'   => Address::where('created_by', $userId)->doesntExist() ? 1 : 0,
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Address added successfully.',
            'address' => $address,
        ]);
    }

    public static function mergeMatrix(array $existing, array $incoming, bool $isAddToCart = false): array
    {
        $byKey = [];
        foreach (array_merge($existing, $incoming) as $cell) {
            $color = $cell['color'] ?? '';
            $size  = $cell['size'] ?? '';
            $qty   = (int) ($cell['qty'] ?? 0);
            if ($qty <= 0) continue;

            $k = $color . '|' . $size;
            if (isset($byKey[$k])) {
                if ($isAddToCart) {
                    $byKey[$k]['qty'] += $qty;
                    Log::info('mergeMatrix: qty increased', ['key' => $k, 'qty' => $byKey[$k]['qty']]);
                } else {
                    // passive merge (e.g. absorbing stale buy-now) -> keep existing qty
                    Log::info('mergeMatrix: duplicate key found, qty left unchanged', ['key' => $k]);
                }
            } else {
                $byKey[$k] = ['color' => $color, 'size' => $size, 'qty' => $qty];
            }
        }
        return array_values($byKey);
    }

    private static function moveBuyNowToCart($cart, $checkoutId)
    {
        foreach ($cart as &$item) {

            if (
                !empty($item['is_buy_now']) &&
                ($item['checkout_id'] ?? null) == $checkoutId
            ) {

                $item['is_buy_now'] = false;
                $item['selected_for_checkout'] = false;

                unset($item['checkout_id']);

                $exists = collect($cart)->search(function ($c) use ($item) {

                    return empty($c['is_buy_now'])
                        && $c['product_id'] == $item['product_id']
                        && ($c['variant_id'] ?? 0) == ($item['variant_id'] ?? 0)
                        && ($c['color_variant_id'] ?? 0) == ($item['color_variant_id'] ?? 0)
                        && ($c['size_variant_id'] ?? 0) == ($item['size_variant_id'] ?? 0);
                });

                if ($exists !== false) {

                    $cart[$exists]['quantity'] += $item['quantity'];

                    unset($cart[array_search($item, $cart)]);
                }
            }
        }

        return array_values($cart);
    }

    private static function absorbStaleBuyNow(array $cart, ?string $keepCheckoutId = null, bool $isAddToCart = false): array
    {
        $buyNowItems = [];
        $rest = [];

        foreach ($cart as $item) {
            if (!empty($item['is_buy_now'])) {
                if ($keepCheckoutId !== null && ($item['checkout_id'] ?? null) === $keepCheckoutId) {
                    $rest[] = $item; // still in-flight, don't touch
                    continue;
                }
                $buyNowItems[] = $item; // abandoned buy-now -> absorb below
            } else {
                $rest[] = $item;
            }
        }

        if (empty($buyNowItems)) {
            return $cart; // nothing to absorb
        }

        foreach ($buyNowItems as $bn) {
            $index = collect($rest)->search(function ($item) use ($bn) {
                return $item['product_id'] == $bn['product_id'] &&
                    ($item['variant_id'] ?? null) == ($bn['variant_id'] ?? null) &&
                    ($item['color_variant_id'] ?? null) == ($bn['color_variant_id'] ?? null) &&
                    ($item['size_variant_id'] ?? null) == ($bn['size_variant_id'] ?? null) &&
                    empty($item['is_buy_now']);
            });

            if ($index !== false) {
                // same product/variant already in cart -> merge, don't duplicate
                if ($isAddToCart) {
                    $rest[$index]['quantity'] += $bn['quantity'];
                }
                // else: leave quantity as-is, only absorb matrix/variant data below

                if (!empty($bn['matrix'])) {
                    $rest[$index]['matrix'] = self::mergeMatrix(
                        $rest[$index]['matrix'] ?? [],
                        $bn['matrix'],
                        $isAddToCart
                    );
                }
            } else {
                // different variant / not in cart yet -> becomes a new cart line
                unset($bn['checkout_id']);
                $bn['is_buy_now'] = false;
                $bn['selected_for_checkout'] = false;
                $rest[] = $bn;
            }
        }

        return array_values($rest);
    }

    public static function getCouponDetails($coupon, $total_amount)
    {
        if (!$coupon || $total_amount <= 0) return 0;

        $discountValue = (float) $coupon->discount_value;
        $minPrice = (float) ($coupon->min_price ?? 0);
        $maxPrice = (float) ($coupon->max_price ?? 0);

        if ($total_amount < $minPrice) return 0;
        if ($maxPrice > 0 && $total_amount > $maxPrice) return 0;

        if ($coupon->apply_for == 2) {
            $orderCount = \App\Models\Order::where('user_id', auth()->id())
                ->where('status', 4)->count();
            if (!empty($coupon->order_count) && ($orderCount + 1) != $coupon->order_count) return 0;
        }

        if ($coupon->discount_type == 1) {
            return ($total_amount * $discountValue) / 100;
        }
        return $discountValue;
    }

    public function listCoupons(Request $request)
    {
        $userId = Auth::id();
        $subtotal = $request->filled('subtotal')
            ? (float) preg_replace('/[^\d.]/', '', (string) $request->subtotal)
            : $this->currentCartSubtotal($userId);
        $coupons = Coupon::where('status', 1)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) use ($subtotal) {
                if ((int) $c->discount_type === 1) {
                    $discountLabel = rtrim(rtrim((string) $c->discount_value, '0'), '.') . '% OFF';
                } else {
                    $discountLabel = '₹' . rtrim(rtrim((string) $c->discount_value, '0'), '.') . ' OFF';
                }

                $conditions = [];
                if (!empty($c->min_price)) {
                    $conditions[] = 'Min order ₹' . (int) $c->min_price;
                }
                if (!empty($c->max_price)) {
                    $conditions[] = 'Max order ₹' . (int) $c->max_price;
                }

                /* -------- eligibility against current cart subtotal -------- */
                $minPrice   = (float) ($c->min_price ?? 0);
                $maxPrice   = (float) ($c->max_price ?? 0);
                $eligible   = true;
                $reason     = '';

                if ($subtotal <= 0) {
                    $eligible = false;
                    $reason   = 'Add items to your cart to use this coupon';
                } elseif ($minPrice > 0 && $subtotal < $minPrice) {
                    $eligible = false;
                    $needed   = $minPrice - $subtotal;
                    $reason   = 'Add ₹' . number_format($needed, 0) . ' more to use this coupon';
                } elseif ($maxPrice > 0 && $subtotal > $maxPrice) {
                    $eligible = false;
                    $reason   = 'Order total exceeds the ₹' . number_format($maxPrice, 0) . ' limit for this coupon';
                }

                // discount preview amount for the current subtotal
                $discountAmount = 0;
                if ($eligible) {
                    if ((int) $c->discount_type === 1) {
                        $discountAmount = ($subtotal * (float) $c->discount_value) / 100;
                    } else {
                        $discountAmount = (float) $c->discount_value;
                    }
                }

                // display label: prefer code, else description, else "COUPON #id"
                $displayCode = !empty($c->code)
                    ? $c->code
                    : (!empty($c->description) ? $c->description : ('COUPON #' . $c->id));

                return [
                    'id'              => $c->id,
                    'code'            => $c->coupon_code,
                    'display_code'    => $displayCode,
                    'discount_label'  => $discountLabel,
                    'discount_amount' => round($discountAmount, 2),
                    'description'     => $c->description ?? '',
                    'conditions'      => implode(' • ', $conditions),
                    'expires_at'      => $c->expires_at ? \Carbon\Carbon::parse($c->expires_at)->format('d M Y') : null,
                    'eligible'        => $eligible,
                    'reason'          => $reason,
                ];
            });

        return response()->json([
            'status'   => 200,
            'subtotal' => $subtotal,
            'coupons'  => $coupons,
        ]);
    }

    private function currentCartSubtotal($userId): float
    {
        $cart = Cache::get("cart_{$userId}", []);
        $subtotal = 0.0;

        $grouped = [];
        foreach ($cart as $item) {
            if (!empty($item['is_buy_now'])) continue;
            $key = $item['product_id'] . '_' .
                ($item['variant_id'] ?? 0) . '_' .
                ($item['color_variant_id'] ?? 0) . '_' .
                ($item['size_variant_id'] ?? 0);
            if (!isset($grouped[$key])) {
                $grouped[$key] = $item;
            } else {
                $grouped[$key]['quantity'] += $item['quantity'];
            }
        }

        foreach ($grouped as $item) {
            $product = Product::with('bulk_product')->find($item['product_id']);
            if (!$product) continue;
            // $quantity = (int) $item['quantity'];
            if ($product->product_type === 'bulk') {
                $quantity = collect($item['matrix'] ?? [])->sum(function ($item) {
                    return (int) ($item['qty'] ?? 0);
                });
            } else {
                $quantity = (int) $item['quantity'];
            }
            if ($quantity <= 0) continue;

            if ($product->product_type === 'single') {
                if ($product->stock <= 0) continue;
                $price = ($product->sale_price > 0) ? (float) $product->sale_price : (float) $product->regular_price;
                $subtotal += $price * $quantity;
            } elseif ($product->product_type === 'variant' && !empty($item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);
                if (!$variant || $variant->stock <= 0) continue;
                $price = ($variant->sale_price > 0) ? (float) $variant->sale_price : (float) $variant->regular_price;
                $subtotal += $price * $quantity;
            } elseif ($product->product_type === 'bulk') {
                $subtotal += (float) ($item['sale_price'] ?? 0);
            }
        }

        return $subtotal;
    }

    public function removeCoupon(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        Cache::forget('coupon_id_' . $userId);
        return response()->json(['status' => 200, 'message' => 'Coupon removed']);
    }

    public static function getCartItemCount($userId): int
    {
        if (!$userId) return 0;
        $cartKey   = "cart_{$userId}";
        $cart_list = Cache::get($cartKey, []);
        $grouped = [];
        foreach ($cart_list as $item) {
            // if (!empty($item['is_buy_now'])) continue;
            $key = $item['product_id'] . '_' .
                ($item['variant_id'] ?? 0) . '_' .
                ($item['color_variant_id'] ?? 0) . '_' .
                ($item['size_variant_id'] ?? 0);
            $grouped[$key] = true;
        }
        return count($grouped);
    }
}
