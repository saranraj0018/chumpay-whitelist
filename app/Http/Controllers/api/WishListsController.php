<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishListsController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $wishlistProductIds = \App\Models\Wishlist::where('user_id', $userId)
            ->pluck('product_id');

        $products = Product::whereIn('id', $wishlistProductIds)
            ->where('product_type', '!=', 'bulk')
            ->with([
                'product_variant.variantValues.attribute_value.get_attribute',
                'ratings'
            ])
            ->get();

        $response = [];

        foreach ($products as $product) {
            if ($product->product_type == 'single') {
                $salePrice = $product->sale_price;
                $regularPrice = $product->regular_price;
                $variant = null;
            } else {
                $variant = $product->product_variant->first();
                $salePrice = optional($variant)->sale_price ?? 0;
                $regularPrice = optional($variant)->regular_price ?? 0;
            }

            $colors = [];
            $selectedColor = null;
            $selectedColorId = null;

            $selectedSize = null;
            $selectedSizeId = null;
            $variantIds = [];

            if ($product->product_type == 'variant' && $product->product_variant) {
                foreach ($product->product_variant as $v) {
                    foreach ($v->variantValues as $value) {
                        if (
                            $value->attribute_value &&
                            $value->attribute_value->get_attribute &&
                            strtolower($value->attribute_value->get_attribute->name) == 'color'
                        ) {
                            $colors[] = $value->attribute_value->value;
                        }

                        if ($variant && $v->id == $variant->id) {
                            if (
                                $value->attribute_value &&
                                $value->attribute_value->get_attribute &&
                                strtolower($value->attribute_value->get_attribute->name) == 'color'
                            ) {
                                $selectedColor = $value->attribute_value->value;
                                $selectedColorId = $value->attribute_value_id;
                            }

                            if (
                                $value->attribute_value &&
                                $value->attribute_value->get_attribute &&
                                strtolower($value->attribute_value->get_attribute->name) == 'size'
                            ) {
                                $selectedSize = $value->attribute_value->value;
                                $selectedSizeId = $value->attribute_value_id;
                            }
                        }
                    }
                }

                if ($selectedColorId) {
                    foreach ($product->product_variant as $variantItem) {
                        foreach ($variantItem->variantValues as $val) {
                            $attrValue = $val->attribute_value;

                            if (!$attrValue || !$attrValue->get_attribute) {
                                continue;
                            }

                            $attrName = strtolower($attrValue->get_attribute->name ?? '');

                            if (
                                $attrName == 'color' &&
                                $val->attribute_value_id == $selectedColorId
                            ) {
                                $variantIds[] = $variantItem->id;
                            }
                        }
                    }
                }

                $variantIds = array_values(array_unique($variantIds));
            }

            /* ---------------- RATING LOGIC ---------------- */
            $averageRating = round($product->ratings->avg('rating') ?? 0, 1);
            $reviewCount = $product->ratings->count();

            /* ---------------- FINAL ARRAY ---------------- */
            $response[] = [
                "id" => $product->id,
                "product_image" => $product->main_image ? asset('storage/' . $product->main_image) : null,
                "product_name" => $product->name,
                "product_type" => $product->product_type,
                "sale_price" => (float) $salePrice,
                "regular_price" => (float) $regularPrice,
                "rating" => $averageRating,
                "review_count" => $reviewCount,
                "product_variant_id" => $variantIds,
                "product_variant_color" => array_values(array_unique($colors)),
                "product_color" => $selectedColor,
                "product_variant_color_id" => $selectedColorId,
                "product_variant_size" => $selectedSize,
                "product_variant_size_id" => $selectedSizeId,
                "liked" => true
            ];
        }

        return response()->json([
            "status" => 200,
            "msg" => "Wishlist fetched Successfully",
            "data" => $response
        ]);
    }

    public function likeAndUnlike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userId = auth()->id();
        $productId = $request->product_id;
        $status = $request->status;

        if ($status) {
            $save_wishlist = new Wishlist();
            $save_wishlist->user_id = $userId;
            $save_wishlist->product_id = $productId;
            $save_wishlist->save();

            return response()->json([
                "product_id" => $productId,
                "status" => 200,
                "message" => "Product added to wishlist"
            ]);
        } else {
            $delete = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();

            return response()->json([
                "product_id" => $productId,
                "status" => 200,
                "message" => "Product removed from wishlist"
            ]);
        }
    }
}
