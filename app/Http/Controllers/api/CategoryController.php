<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        $cacheKey = "inside_grocery_zone:user:{$user->id}";
        $isInside = Cache::get($cacheKey);
        $categories = Category::select('id', 'name', 'image')
            ->where('status', 1)
            ->get()
            ->map(function ($category) {
                return [
                    'category_id'    => $category->id,
                    'category_name'  => $category->name,
                    'category_image' => $category->image
                        ? url('/storage/' . $category->image)
                        : null,
                ];
            });

        return response()->json([
            'status' => 200,
            'msg'    => 'Categories fetched successfully',
            'data'   => $categories
        ]);
    }

    public function categoryProducts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id'     => 'required|integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $userId = auth()->id();
            $wishlistProductIds = \App\Models\Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
            $products = Product::whereNot('product_type', 'bulk')
                  ->where('category_id',$request->category_id)
                  ->with([
                     'product_variant.variantValues.attribute_value.get_attribute',
                     'ratings'
                   ])
                ->get()
                ->map(function ($product) use($wishlistProductIds) {
                    $image = $product->main_image
                        ? asset('storage/' . $product->main_image)
                        : null;
                    /* ---------------- PRICE ---------------- */
                    if ($product->product_type == 'single') {
                        $salePrice    = $product->sale_price;
                        $regularPrice = $product->regular_price;
                    } else {
                        $firstVariant = $product->product_variant->first();
                        $salePrice    = optional($firstVariant)->sale_price ?? 0;
                        $regularPrice = optional($firstVariant)->regular_price ?? 0;
                    }

                    /* ---------------- VARIANT DATA ---------------- */
                    $colors = [];
                    $sizes  = [];
                    $variantIds = [];
                    $selectedColor = null;
                    $selectedColorId = null;
                    $selectedSize = null;
                    $selectedSizeId = null;

                    if ($product->product_type == 'variant') {
                        foreach ($product->product_variant as $variant) {
                            foreach ($variant->variantValues as $val) {
                                $attrName = strtolower($val->attribute_value->get_attribute->name ?? '');
                                if ($attrName == 'color') {
                                    $colors[] = $val->attribute_value->value;
                                    if (!$selectedColor) {
                                        $selectedColor = $val->attribute_value->value;
                                        $selectedColorId = $val->attribute_value_id;
                                    }
                                }
                                if ($attrName == 'size') {
                                    $sizes[] = $val->attribute_value->value;
                                    if (!$selectedSize) {
                                        $selectedSize = $val->attribute_value->value;
                                        $selectedSizeId = $val->attribute_value_id;
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
                   $averageRating = round($product->ratings->avg('rating') ?? 0, 1);
                   $reviewCount = $product->ratings->count();
                    return [
                        "id" => $product->id,
                        "product_image" => $image,
                        "product_name" => $product->name,
                        "product_type" => $product->product_type,
                        "sale_price" => (float) $salePrice,
                        "regular_price" => (float) $regularPrice,
                        "rating" => $averageRating,
                        "product_variant_id" => $variantIds,
                        "product_variant_color" => array_values(array_unique($colors)),
                        "product_color" => $selectedColor,
                        "product_variant_color_id" => $selectedColorId,
                        "product_variant_size" => $selectedSize,
                        "product_variant_size_id" => $selectedSizeId,
                        "liked" => in_array($product->id, $wishlistProductIds)
                    ];
                });

            return response()->json([
                "status" => 200,
                "msg" => "Category Product fetched Successfully",
                "data" => $products
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
}
