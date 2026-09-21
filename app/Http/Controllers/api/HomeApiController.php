<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $wishlistProductIds = \App\Models\Wishlist::where('user_id', $user->id)
                ->pluck('product_id')
                ->toArray();
            $banners = Banner::select('id', 'image')->get()->map(function ($banner) {
                return [
                    "banner_id" => $banner->id,
                    "banner_image" => $banner->image ? asset('storage/' . $banner->image) : null
                ];
            });

            $categories = Category::select('id', 'image', 'name')->latest()
                ->take(6)->get()->map(function ($category) {
                return [
                    "id" => $category->id,
                    "category_image" => $category->image ? asset('storage/' . $category->image) : null,
                    "category_name" => $category->name,
                ];
            });
            $products = Product::whereNot('product_type', 'bulk')->with([
                'product_variant.variantValues.attribute_value.get_attribute',
                'ratings'
            ])
                ->latest()
                ->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->take(4)
                ->get();
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
                    $variantIds = []; // selected color variant ids

                    if ($product->product_variant->count()) {
                        $firstVariant = $product->product_variant->first();
                        $salePrice = $firstVariant->sale_price ?? $salePrice;
                        $regularPrice = $firstVariant->regular_price ?? $regularPrice;

                        if ($firstVariant->cover_image) {
                            $productImage = asset('storage/' . $firstVariant->cover_image);
                        }

                        // STEP 1: get first selected color
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
                        "product_variant_id" => $variantIds, // array of selected color variant ids
                        "product_variant_color" => $colors,
                        "product_color" => $selectedColor,
                        "product_variant_color_id" => $colorId,
                        "product_variant_size" => $selectedSize,
                        "liked" => in_array($product->id, $wishlistProductIds)
                    ];
                }

                return $result;
            };
            $readStatus = Notification::where([
                'user_id' => $user->id,
                'role' => 2,
                'status' => 0
            ])->exists();
         
            return response()->json([
                "status" => 200,
                "msg" => "Home Successfully",
                "data" => [
                    "user_image" => $user && $user->image_path
                        ? asset('storage/' . $user->image_path)
                        : null,
                    "user_name" => $user->name ?? null,
                    "notification" => $readStatus,
                    "banner" => $banners,
                    "categories" => $categories,
                    "hot_deals" => $prepareProducts($products),
                    "today_sale" => $prepareProducts($products),
                    "deal_ofthe_day" => $prepareProducts($products),
                ]

            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "msg" => $e->getMessage()
            ]);
        }
    }

    public function hotDeals()
    {
        try {
            $userId = auth()->id();

            $wishlistProductIds = \App\Models\Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();

            $products = Product::whereNot('product_type', 'bulk')
                ->with([
                    'product_variant.variantValues.attribute_value.get_attribute',
                ])
                ->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->get()
                ->map(function ($product) use ($wishlistProductIds) {

                    $image = $product->main_image
                        ? asset('storage/' . $product->main_image)
                        : null;

                    /* ---------------- DEFAULT PRICE ---------------- */
                    if ($product->product_type == 'single') {
                        $salePrice    = $product->sale_price;
                        $regularPrice = $product->regular_price;
                    } else {
                        $firstVariant = $product->product_variant->first();
                        $salePrice    = optional($firstVariant)->sale_price ?? 0;
                        $regularPrice = optional($firstVariant)->regular_price ?? 0;

                        if ($firstVariant && $firstVariant->cover_image) {
                            $image = asset('storage/' . $firstVariant->cover_image);
                        }
                    }
                    $colors = [];
                    $sizes = [];

                    $selectedColor = null;
                    $selectedColorId = null;
                    $selectedSize = null;
                    $selectedSizeId = null;
                    $variantIds = [];

                    if ($product->product_type == 'variant') {
                        foreach ($product->product_variant as $variant) {
                            foreach ($variant->variantValues as $val) {
                                $attrValue = $val->attribute_value;
                                if (!$attrValue) continue;

                                $attrName = strtolower($attrValue->get_attribute->name ?? '');

                                if ($attrName == 'color') {
                                    $colors[] = $attrValue->value;

                                    if (!$selectedColor) {
                                        $selectedColor = $attrValue->value;
                                        $selectedColorId = $val->attribute_value_id;
                                    }
                                }

                                if ($attrName == 'size') {
                                    $sizes[] = $attrValue->value;

                                    if (!$selectedSize) {
                                        $selectedSize = $attrValue->value;
                                        $selectedSizeId = $val->attribute_value_id;
                                    }
                                }
                            }
                        }
                        $colors = array_values(array_unique($colors));
                        $sizes = array_values(array_unique($sizes));
                        if ($selectedColorId) {
                            foreach ($product->product_variant as $variant) {
                                foreach ($variant->variantValues as $val) {
                                    $attrValue = $val->attribute_value;
                                    if (!$attrValue) continue;

                                    $attrName = strtolower($attrValue->get_attribute->name ?? '');

                                    if (
                                        $attrName == 'color' &&
                                        $val->attribute_value_id == $selectedColorId
                                    ) {
                                        $variantIds[] = $variant->id;
                                    }
                                }
                            }
                        }

                        $variantIds = array_values(array_unique($variantIds));
                    }

                    return [
                        "id" => $product->id,
                        "product_image" => $image,
                        "product_name" => $product->name,
                        "product_type" => $product->product_type,
                        "sale_price" => (float) $salePrice,
                        "regular_price" => (float) $regularPrice,
                        "rating" => round($product->ratings_avg_rating ?? 0, 1),
                        "review_count" => $product->ratings_count ?? 0,
                        "product_variant_color" => $colors,
                        "product_color" => $selectedColor,
                        "product_variant_id" => $variantIds, // only selected color variant ids
                        "product_variant_color_id" => $selectedColorId,
                        "product_variant_size" => $selectedSize,
                        "product_variant_size_id" => $selectedSizeId,
                        "liked" => in_array($product->id, $wishlistProductIds)
                    ];
                });

            return response()->json([
                "status" => 200,
                "msg" => "Hot Deals Successfully",
                "data" => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "msg" => $e->getMessage()
            ]);
        }
    }

    public function todaySales()
    {
        $userId = auth()->id();
        $wishlistProductIds = \App\Models\Wishlist::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
        $products = Product::whereNot('product_type', 'bulk')
            ->with([
                'product_variant.variantValues.attribute_value.get_attribute',
                'ratings'
            ])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->get();
        $response = [];
        foreach ($products as $product) {
            $salePrice = $product->sale_price ?? 0;
            $regularPrice = $product->regular_price ?? 0;
            $variant = null;
            $colors = [];
            $selectedColor = null;
            $selectedColorId = null;
            $selectedSize = null;
            $selectedSizeId = null;
            $variantIds = []; // selected color variant ids
            if ($product->product_type == 'variant' && $product->product_variant->count()) {
                $variant = $product->product_variant->first();
                $salePrice = $variant->sale_price ?? 0;
                $regularPrice = $variant->regular_price ?? 0;
            }

            /* ---------------- VARIANT ATTRIBUTES ---------------- */
            if ($product->product_type == 'variant' && $product->product_variant->count()) {

                // STEP 1: get colors + selected color from first variant
                foreach ($product->product_variant as $v) {
                    foreach ($v->variantValues as $value) {
                        $attrValue = $value->attribute_value;
                        if (!$attrValue || !$attrValue->get_attribute) continue;

                        $attributeName = strtolower($attrValue->get_attribute->name);

                        // all colors
                        if ($attributeName == 'color') {
                            $colors[] = $attrValue->value;
                        }

                        // selected values from first variant
                        if ($variant && $v->id == $variant->id) {
                            if ($attributeName == 'color') {
                                $selectedColor = $attrValue->value;
                                $selectedColorId = $value->attribute_value_id;
                            }

                            if ($attributeName == 'size') {
                                $selectedSize = $attrValue->value;
                                $selectedSizeId = $value->attribute_value_id;
                            }
                        }
                    }
                }

                // unique colors
                $colors = array_values(array_unique($colors));

                // STEP 2: get all variant ids for selected color
                if ($selectedColorId) {
                    foreach ($product->product_variant as $v) {
                        foreach ($v->variantValues as $value) {
                            $attrValue = $value->attribute_value;
                            if (!$attrValue || !$attrValue->get_attribute) continue;

                            $attributeName = strtolower($attrValue->get_attribute->name);

                            if (
                                $attributeName == 'color' &&
                                $value->attribute_value_id == $selectedColorId
                            ) {
                                $variantIds[] = $v->id;
                            }
                        }
                    }
                }

                $variantIds = array_values(array_unique($variantIds));
            }

            /* ---------------- FINAL ARRAY ---------------- */
            $response[] = [
                "id" => $product->id,
                "product_image" => $product->main_image ? asset('storage/' . $product->main_image) : null,
                "product_name" => $product->name,
                "product_type" => $product->product_type,
                "sale_price" => (float) $salePrice,
                "regular_price" => (float) $regularPrice,
                "rating" => round($product->ratings_avg_rating ?? 0, 1),
                "review_count" => $product->ratings_count ?? 0,
                "product_variant_color" => $colors,
                "product_color" => $selectedColor,
                "product_variant_id" => $variantIds, // only selected color variant ids
                "product_variant_color_id" => $selectedColorId,
                "product_variant_size" => $selectedSize,
                "product_variant_size_id" => $selectedSizeId,
                "liked" => in_array($product->id, $wishlistProductIds)
            ];
        }

        return response()->json([
            "status" => 200,
            "msg" => "Today Sale Successfully",
            "data" => $response
        ]);
    }

    public function searchProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'nullable|integer|exists:categories,id',
                'search_name' => 'nullable|string|max:255',
                'min'         => 'nullable|numeric|min:0',
                'max'         => 'nullable|numeric|min:0|gte:min',
                'sort'        => 'nullable|in:low,high',
                'rating'      => 'nullable|numeric|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'mgs'    => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $userId =  $user = auth()->user()->id;

            $products = Product::query()
                ->with([
                    'variants.variantValues.attributeValue.attribute',
                    'galleryImages',
                ])
                ->leftJoin('reviews', 'products.id', '=', 'reviews.product_id')
                ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.product_type',
                    'products.main_image',
                    'products.regular_price',
                    'products.sale_price',
                    'products.category_id',
                    DB::raw('ROUND(COALESCE(AVG(reviews.rating), 0), 1) as avg_rating'),
                    DB::raw('MIN(
                        CASE
                            WHEN products.product_type = "single"
                                THEN CASE
                                    WHEN products.sale_price > 0 THEN products.sale_price
                                    ELSE products.regular_price
                                END
                            WHEN products.product_type = "variant"
                                THEN CASE
                                    WHEN product_variants.sale_price > 0 THEN product_variants.sale_price
                                    ELSE product_variants.regular_price
                                END
                            ELSE 0
                        END
                    ) as final_price'),
                    DB::raw('MIN(products.regular_price) as single_regular_price'),
                    DB::raw('MIN(products.sale_price) as single_sale_price'),
                    DB::raw('MIN(product_variants.regular_price) as min_variant_regular_price'),
                    DB::raw('MIN(product_variants.sale_price) as min_variant_sale_price')
                )
                ->groupBy(
                    'products.id',
                    'products.name',
                    'products.product_type',
                    'products.main_image',
                    'products.regular_price',
                    'products.sale_price',
                    'products.category_id'
                );

            if ($request->filled('category_id')) {
                $products->where('products.category_id', $request->category_id);
            }
            if ($request->filled('search_name')) {
                $products->where('products.name', 'like', '%' . $request->search_name . '%');
            }
            if ($request->filled('min')) {
                $products->having('final_price', '>=', $request->min);
            }
            if ($request->filled('max')) {
                $products->having('final_price', '<=', $request->max);
            }
            if ($request->filled('rating')) {
                $products->having('avg_rating', '>=', $request->rating);
            }
            if ($request->sort == 'low') {
                $products->orderBy('final_price', 'asc');
            } elseif ($request->sort == 'high') {
                $products->orderBy('final_price', 'desc');
            } else {
                $products->latest('products.id');
            }
            $productList = $products->get();
            $data = $productList->map(function ($product) use ($userId) {

                $productImage = $product->main_image
                    ? asset('storage/' . $product->main_image)
                    : null;

                $liked = false;
                if ($userId) {
                    $liked = Wishlist::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->exists();
                }

                $productVariantColors = [];
                $productColor = null;
                $productVariantColorId = null;
                $productVariantSize = null;
                $productVariantSizeId = null;
                $salePrice = 0;
                $regularPrice = 0;

                if ($product->product_type === 'single') {
                    $regularPrice = (float) $product->regular_price;
                    $salePrice = $product->sale_price > 0
                        ? (float) $product->sale_price
                        : (float) $product->regular_price;
                }

                if ($product->product_type === 'variant') {
                    $variants = ProductVariant::with(['variantValues.attributeValue.attribute'])
                        ->where('product_id', $product->id)
                        ->get();

                    $allColors = [];
                    $defaultVariant = $variants->sortBy(function ($variant) {
                        return $variant->sale_price > 0 ? $variant->sale_price : $variant->regular_price;
                    })->first();

                    foreach ($variants as $variant) {
                        foreach ($variant->variantValues as $value) {
                            if (
                                $value->attributeValue &&
                                $value->attributeValue->attribute &&
                                strtolower($value->attributeValue->attribute->name) === 'color'
                            ) {
                                $allColors[$value->attributeValue->id] = $value->attributeValue->value;
                            }
                        }
                    }

                    $productVariantColors = array_values($allColors);

                    if ($defaultVariant) {
                        $regularPrice = (float) $defaultVariant->regular_price;
                        $salePrice = $defaultVariant->sale_price > 0
                            ? (float) $defaultVariant->sale_price
                            : (float) $defaultVariant->regular_price;

                        foreach ($defaultVariant->variantValues as $value) {
                            if (
                                $value->attributeValue &&
                                $value->attributeValue->attribute
                            ) {
                                $attributeName = strtolower($value->attributeValue->attribute->name);

                                if ($attributeName === 'color') {
                                    $productColor = $value->attributeValue->value;
                                    $productVariantColorId = $value->attribute_value_id;
                                }

                                if ($attributeName === 'size') {
                                    $productVariantSize = $value->attributeValue->value;
                                    $productVariantSizeId = $value->attribute_value_id;
                                }
                            }
                        }
                    }
                }

                return [
                    'id'                       => $product->id,
                    'product_image'            => $productImage,
                    'product_name'             => $product->name,
                    'sale_price'               => $salePrice,
                    'regular_price'            => $regularPrice,
                    'rating'                   => (float) $product->avg_rating,
                    'product_variant_color'    => $productVariantColors,
                    'product_color'            => $productColor,
                    'product_variant_color_id' => $productVariantColorId,
                    'product_variant_size'     => $productVariantSize,
                    'product_variant_size_id'  => $productVariantSizeId,
                    'liked'                    => $liked,
                ];
            });

            return response()->json([
                'status' => 200,
                'mgs'    => 'Search Successfully',
                'data'   => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'mgs'    => 'Something went wrong',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }
}
