<?php

namespace App\Http\Controllers;

use App\Models\BulkProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilterController extends Controller
{
    public function searchProducts(Request $request)
    {
        $cleaned = array_map(function ($value) {
            if (is_string($value) && trim($value) === '') {
                return null;
            }
            return $value;
        }, $request->all());
        foreach ($cleaned as $key => $value) {
            if (is_array($value) && empty($value)) {
                $cleaned[$key] = null;
            }
        }
        $request->replace($cleaned);
        $validator = Validator::make($request->all(), [
            'search'       => 'nullable|string',
            'categories'   => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'sort'         => 'nullable|in:low,high',
            'min_price'    => 'nullable|numeric|min:0',
            'max_price'    => 'nullable|numeric|min:0',
            'rating'       => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = auth()->user();
        $wishlistProductIds = Wishlist::where('user_id', $user->id)
            ->pluck('product_id')
            ->toArray();
        $query = Product::whereNot('product_type', 'bulk')
            ->with([
                'product_variant.variantValues.attribute_value.get_attribute',
                'ratings'
            ])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings');
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('product_code', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->categories);
        }
        // if ($request->filled('min_price') || $request->filled('max_price')) {
        //     $min = $request->min_price ?? 0;
        //     $max = $request->max_price ?? PHP_INT_MAX;
        //     $query->where(function ($q) use ($min, $max) {
        //         $q->whereBetween('sale_price', [$min, $max])
        //             ->orWhereHas('product_variant', function ($q2) use ($min, $max) {
        //                 $q2->whereBetween('sale_price', [$min, $max]);
        //             });
        //     });
        // }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = $request->min_price;
            $max = $request->max_price;
            $query->where(function ($q) use ($min, $max) {
                $q->where(function ($p) use ($min, $max) {
                    if ($min !== null) {
                        $p->where('sale_price', '>=', $min);
                    }
                    if ($max !== null) {
                        $p->where('sale_price', '<=', $max);
                    }
                });

                // VARIANT PRODUCTS (MIN(product_details.sale_price))
                $q->orWhereHas('product_variant', function ($v) use ($min, $max) {
                    $v->whereRaw('sale_price = (
                SELECT MIN(pd2.sale_price)
                FROM product_variants pd2
                WHERE pd2.product_id = product_variants.product_id
            )');

                    if ($min !== null) {
                        $v->where('sale_price', '>=', $min);
                    }

                    if ($max !== null) {
                        $v->where('sale_price', '<=', $max);
                    }
                });
            });
        }


        /* RATING */
        if ($request->filled('rating') && $request->rating !== 'all') {
            $query->having('ratings_avg_rating', '>=', (float)$request->rating);
        }

        $products = $query->latest()->paginate(10);

        /* SAME LOGIC AS INDEX */
        $prepareProducts = function ($products) use ($wishlistProductIds) {
            $result = [];
            foreach ($products as $product) {
                $productImage = $product->main_image
                    ? asset('storage/' . $product->main_image)
                    : null;
                $salePrice = $product->sale_price ?? "0.00";
                $regularPrice = $product->regular_price ?? "0.00";
                $colors = [];
                $selectedColor = null;
                $selectedSize = null;
                $colorId = null;
                $variantIds = [];
                if ($product->product_variant->count()) {
                    $firstVariant = $product->product_variant->first();
                    $salePrice = $firstVariant->sale_price ?? $salePrice;
                    $regularPrice = $firstVariant->regular_price ?? $regularPrice;
                    if ($firstVariant->cover_image) {
                        $productImage = asset('storage/' . $firstVariant->cover_image);
                    }
                    foreach ($product->product_variant as $variant) {
                        foreach ($variant->variantValues as $value) {
                            $attrValue = $value->attribute_value;
                            if (!$attrValue) continue;
                            $attribute = $attrValue->get_attribute;
                            if (!$attribute) continue;
                            if (strtolower($attribute->name) == "color") {
                                $colors[] = $attrValue->value;
                                if (!$selectedColor) {
                                    $selectedColor = $attrValue->value;
                                    $colorId = $attrValue->id;
                                }
                            }
                            if (strtolower($attribute->name) == "size" && !$selectedSize) {
                                $selectedSize = $attrValue->id;
                            }
                        }
                    }

                    $colors = array_values(array_unique($colors));

                    if ($colorId) {
                        foreach ($product->product_variant as $variant) {
                            foreach ($variant->variantValues as $value) {
                                $attrValue = $value->attribute_value;
                                if (!$attrValue) continue;
                                $attribute = $attrValue->get_attribute;
                                if (!$attribute) continue;
                                if (
                                    strtolower($attribute->name) == "color" &&
                                    $attrValue->id == $colorId
                                ) {
                                    $variantIds[] = $variant->id;
                                }
                            }
                        }
                    }

                    $variantIds = array_values(array_unique($variantIds));
                }

                $result[] = [
                    "id" => $product->id,
                    "product_image" => $productImage,
                    "product_name" => $product->name,
                    "sale_price" => $salePrice,
                    "regular_price" => $regularPrice,
                    "rating" => round($product->ratings_avg_rating ?? 0, 1),
                    "review_count" => $product->ratings_count ?? 0,
                    "product_variant_id" => $variantIds,
                    "product_variant_color" => $colors,
                    "product_color" => $selectedColor,
                    "product_variant_color_id" => $colorId,
                    "product_variant_size" => $selectedSize,
                    "liked" => in_array($product->id, $wishlistProductIds)
                ];
            }

            return $result;
        };

        /* SORT */
        $collection = collect($prepareProducts($products));
  
        // if ($request->sort === 'low') {
        //     $collection = $collection->sortBy('sale_price')->values();
        // } elseif ($request->sort === 'high') {
        //     $collection = $collection->sortByDesc('sale_price')->values();
        // }
         

        return response()->json([
            'status' => 200,
            'message' => 'Products fetched successfully',
            'data' => $collection,
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }
}
