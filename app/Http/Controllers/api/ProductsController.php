<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductGalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    public function getProductDetails(Request $request)
    {
        try {
            $userId = auth()->id() ?? session()->getId();

            $wishlistProductIds = \App\Models\Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|exists:products,id',
                'product_variant_color_id' => 'nullable|integer|exists:variant_attribute_values,id',
                'product_variant_size_id' => 'nullable|integer|exists:variant_attribute_values,id',
                'product_variant' => 'required|array',
                'product_variant.*' => 'integer|exists:product_variants,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $product = Product::with([
                'product_variant.variantValues.attribute_value.get_attribute',
                'product_gallery_image',
                'ratings.user'
            ])
                ->whereNot('product_type', 'bulk')
                ->findOrFail($request->product_id);

            $requestVariantIds = $request->product_variant ?? [];
            $colorId = $request->product_variant_color_id;
            $sizeId = $request->product_variant_size_id;

            /* ================= CART ================= */
            $cartKey = "cart_{$userId}";
            $cart = collect(Cache::get($cartKey, []));

            /* ================= PRICE / STOCK ================= */
            if ($product->product_type == 'single') {
                $salePrice = $product->sale_price;
                $regularPrice = $product->regular_price;
                $quantity = $product->stock;
            } else {
                $matchedVariant = $product->product_variant->filter(function ($variant) use ($colorId, $sizeId) {
                    $variantValueIds = $variant->variantValues->pluck('attribute_value_id')->toArray();

                    return (!$colorId || in_array($colorId, $variantValueIds)) &&
                        (!$sizeId || in_array($sizeId, $variantValueIds));
                })->first();

                if (!$matchedVariant) {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Selected variant not found.',
                    ], 404);
                }

                $salePrice = $matchedVariant->sale_price ?? 0;
                $regularPrice = $matchedVariant->regular_price ?? 0;
                $quantity = $matchedVariant->stock ?? 0;
            }

            /* ================= IMAGES ================= */
            $images = [];
            $productGalleryImages = ProductGalleryImage::whereIn('variant_id',$request->product_variant)->get();
            // if ($product->main_image) {
            //     $images[] = asset('storage/' . $product->main_image);
            // }

            foreach ($productGalleryImages  as $img) {
                if ($img->image_path) {
                    $images[] = asset('storage/' . $img->image_path);
                }
            }

            $images = array_values(array_unique($images));

            /* ================= COLORS & SIZES ================= */
            $colors = [];
            $sizes = [];

            foreach ($product->product_variant as $variant) {

                foreach ($variant->variantValues as $val) {

                    if (!$val->attribute_value || !$val->attribute_value->get_attribute) continue;

                    $attributeName = strtolower($val->attribute_value->get_attribute->name);
                    $attrId = $val->attribute_value_id;

                    /* ---------- COLOR ---------- */
                    if ($attributeName == 'color') {

                        if (!isset($colors[$attrId])) {
                            $colors[$attrId] = [
                                'id' => $attrId,
                                'color_name' => $val->attribute_value->value,
                                'quantity' => 0,
                                'color_image' => $variant->cover_image
                                    ? asset('storage/' . $variant->cover_image)
                                    : null,
                                'variant_id' => [],
                            ];
                        }

                        if (!in_array($variant->id, $colors[$attrId]['variant_id'])) {
                            $colors[$attrId]['variant_id'][] = $variant->id;
                        }

                        /* SAFE CART FILTER (NO BUG) */
                        $cartQty = $cart->where('product_id', $product->id)
                            ->where('variant_id', $variant->id)
                            ->where('color_variant_id', $attrId)
                            ->sum('quantity');

                        $colors[$attrId]['quantity'] += $cartQty;
                    }

                    /* ---------- SIZE ---------- */
                    if (
                        $attributeName == 'size' &&
                        in_array($variant->id, $requestVariantIds)
                    ) {

                        /* SAFE CART FILTER */
                        $cartQty = $cart->where('product_id', $product->id)
                            ->where('variant_id', $variant->id)
                            ->where('size_variant_id', $attrId)
                            ->sum('quantity');

                        $sizes[] = [
                            'id' => $attrId,
                            'size' => $val->attribute_value->value,
                            'stock' => $variant->stock,
                            'quantity' => $cartQty,
                            'regular_price' => $variant->regular_price,
                            'sale_price' => $variant->sale_price,
                            'variant_id' => $variant->id,
                        ];
                    }
                }
            }

            $colors = array_values($colors);
            $sizes = array_values(array_unique($sizes, SORT_REGULAR));

            /* ================= RATINGS ================= */
            $ratingsData = [];
            $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($product->ratings as $rating) {
                $stars = (int) $rating->rating;

                if (isset($ratingCounts[$stars])) {
                    $ratingCounts[$stars]++;
                }

                $ratingsData[] = [
                    'user_name' => $rating->user->name ?? '',
                    'user_image' => $rating->user && $rating->user->image_path
                        ? asset('storage/' . $rating->user->image_path)
                        : null,
                    'stars' => $rating->rating,
                    'description' => $rating->review,
                    'image' => !empty($rating->image)
                        ? collect(json_decode($rating->image, true))
                            ->map(fn($img) => url('storage/'.$img))
                            ->toArray()
                        : [],
                    'date' => date('d-m-Y', strtotime($rating->created_at))
                ];
            }
            $total_rating = $product->ratings->count();
            $fiveStarCount  = $product->ratings->where('rating', 5)->count();
            $fourStarCount  = $product->ratings->where('rating', 4)->count();
            $threeStarCount = $product->ratings->where('rating', 3)->count();
            $twoStarCount   = $product->ratings->where('rating', 2)->count();
            $oneStarCount   = $product->ratings->where('rating', 1)->count();

            /* ================= RESPONSE ================= */
            return response()->json([
                "status" => 200,
                "msg" => "Product Details Successfully",
                "data" => [
                    "product_id" => $product->id,
                    "product_name" => $product->name,
                    "product_type" => $product->product_type,
                    "sale_price" => $salePrice,
                    "regular_price" => $regularPrice,
                    "product_description" => $product->description,
                    "product_rating" => (string) round($product->ratings->avg('rating') ?? 0, 1),
                    "quantity" => $quantity,
                    "liked" => in_array($product->id, $wishlistProductIds),
                    "product_image" => $images,
                    "product_color" => $colors,
                    "product_size" => $sizes,
                    "selected_color_id" => $colorId,
                    "selected_size_id" => $sizeId,
                    "rating_count" => $product->ratings->count(),
                    '5-stars' => $total_rating > 0 ? round(($fiveStarCount / $total_rating) * 100) : 0,
                    '4-stars' => $total_rating > 0 ? round(($fourStarCount / $total_rating) * 100) : 0,
                    '3-stars' => $total_rating > 0 ? round(($threeStarCount / $total_rating) * 100) : 0,
                    '2-stars' => $total_rating > 0 ? round(($twoStarCount / $total_rating) * 100) : 0,
                    '1-stars' => $total_rating > 0 ? round(($oneStarCount / $total_rating) * 100) : 0,
                    "ratings" => $ratingsData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "msg" => "Something went wrong",
                "error" => $e->getMessage()
            ]);
        }
    }
}
