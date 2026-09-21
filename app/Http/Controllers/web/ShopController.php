<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\VariantAttributeValue;
use App\Models\Wishlist;
use App\Traits\FormatsProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    use FormatsProducts;

    public function index(Request $request)
    {
        $wishlistIds = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->all()
            : [];

        $products = Product::with([
            'get_category',
            'bulk_product',
            'product_variant.variantValues.attribute_value.get_attribute',
            'ratings',
        ])
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(fn(Product $product) => $this->formatProduct($product, $wishlistIds))
            ->filter(fn($p) => $p['priceMax'] > 0)
            ->values();

        $categories = Product::with('get_category')
            ->whereHas('get_category', function ($q) {
                $q->where('status', 1);
            })
            ->select('category_id')
            ->distinct()
            ->get();

        $fabrics = VariantAttributeValue::whereHas('get_attribute', function ($q) {
            $q->whereRaw('LOWER(name) = ?', ['fabric']);
        })
            ->pluck('value')->unique()->values();

        $priceMin = (int) floor($products->min('priceMin') ?? 100);
        $priceMax = (int) ceil($products->max('priceMax') ?? 5000);

        if ($priceMax <= $priceMin) {
            $priceMax = $priceMin + 100;
        }

        $allDiscounts = $products->pluck('discount');

        $discountTiers = collect([
            ['label' => '10% - 20%',  'min' => 10, 'max' => 20],
            ['label' => '20% - 40%',  'min' => 20, 'max' => 40],
            ['label' => '40% - 60%',  'min' => 40, 'max' => 60],
            ['label' => '60% - 80%',  'min' => 60, 'max' => 80],
            ['label' => '80% - 100%', 'min' => 80, 'max' => 100],
        ])
            ->filter(fn($tier) => $allDiscounts->contains(fn($d) => $d >= $tier['min'] && $d <= $tier['max']))
            ->values();

        return view('frontend.shop', [
            'products'   => $products,
            'categories' => $categories,
            'fabrics'    => $fabrics,
            'priceMin'   => $priceMin,
            'priceMax'   => $priceMax,
            'discountTiers' => $discountTiers,
        ]);
    }

    public function singleProduct($id)
    {
        $product = Product::with([
            'get_category',
            'bulk_product.bulk_product_variants.attribute_value.get_attribute',
            'product_variant.variantValues.attribute_value.get_attribute',
            'product_variant.gallery_images',
            'product_gallery_image',
            'ratings',
        ])
            ->where('status', 'active')
            ->findOrFail($id);
        // ---- Reviews ----
        $reviewsQuery = Review::with('user')
            ->where('product_id', $product->id)
            ->latest();
        $reviews = $reviewsQuery->paginate(5);
        $allRatings = Review::where('product_id', $product->id)
            ->pluck('rating');
        $reviewCount = $allRatings->count();
        $avgRating   = $reviewCount ? round($allRatings->avg(), 1) : 0;
        $distribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $allRatings->where(fn($r) => $r == $star)->count();
            $count = $allRatings->filter(fn($r) => (int) $r === $star)->count();
            $distribution[$star] = [
                'count'   => $count,
                'percent' => $reviewCount ? round(($count / $reviewCount) * 100) : 0,
            ];
        }

        $reviewImages = Review::where('product_id', $product->id)
            ->whereNotNull('image')
            ->latest()
            ->limit(3)
            ->pluck('image');

        // Related products...
        $products = Product::with([
            'get_category',
            'bulk_product',
            'product_variant.variantValues.attribute_value.get_attribute',
            'ratings',
        ])
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($p) => $this->formatProduct($p));

        // Each row in product_variants is one fixed SKU (its own size + color
        // combo via product_variant_values), so size and color can't be picked
        // independently on the front end — expose the full combo matrix and let
        // the UI resolve the exact variant_id for whatever pair is selected.
        $variantMatrix = $product->product_variant->map(function ($variant) {
            $attrs = [];
            foreach ($variant->variantValues as $val) {
                $name = strtolower(optional($val->attribute_value?->get_attribute)->name ?? '');
                if (!$name || !$val->attribute_value) continue;
                $attrs[$name] = $val->attribute_value_id;
            }

            $regular = (float) ($variant->regular_price ?? 0);
            $sale    = (float) ($variant->sale_price ?? $regular);
            $price   = $sale > 0 ? $sale : $regular;

            return [
                'variant_id' => $variant->id,
                'size_id'    => $attrs['size'] ?? null,
                'color_id'   => $attrs['color'] ?? ($attrs['colour'] ?? null),
                'stock'      => (int) ($variant->stock ?? 0),
                'price'      => $price,
                'old_price'  => $regular,
            ];
        })->values();

        // Gallery images are stored against a variant_id, but only the
        // "canonical" variant of a color group actually has rows attached
        // (see admin ProductsController::store), while every variant in the
        // group shares the same cover_image. Group by color attribute_value_id
        // here so the front end can show only the images for the picked color.
        // cover_image is kept separate (colorCoverMap) instead of being mixed
        // into the gallery list, so the color's main/hero photo isn't also
        // duplicated as a thumbnail in the gallery strip.
        $colorGalleryMap = [];
        $colorCoverMap = [];
        foreach ($product->product_variant as $variant) {
            $colorId = null;
            foreach ($variant->variantValues as $val) {
                $attrName = strtolower(optional($val->attribute_value?->get_attribute)->name ?? '');
                if (in_array($attrName, ['color', 'colour'])) {
                    $colorId = $val->attribute_value_id;
                    break;
                }
            }
            if (!$colorId) {
                continue;
            }

            $colorGalleryMap[$colorId] ??= [];

            if ($variant->cover_image && !isset($colorCoverMap[$colorId])) {
                $colorCoverMap[$colorId] = asset('storage/' . $variant->cover_image);
            }
            foreach ($variant->gallery_images as $img) {
                $colorGalleryMap[$colorId][] = asset('storage/' . $img->image_path);
            }
        }
        foreach ($colorGalleryMap as $colorId => $images) {
            $colorGalleryMap[$colorId] = array_values(array_unique($images));
        }

        return view('frontend.productdetails.index', [
            'product'         => $product,
            'formatted'       => $this->formatProduct($product),
            'products'        => $products,
            'reviews'         => $reviews,
            'reviewCount'     => $reviewCount,
            'avgRating'       => $avgRating,
            'distribution'    => $distribution,
            'reviewImages'    => $reviewImages,
            'variantMatrix'   => $variantMatrix,
            'colorGalleryMap' => $colorGalleryMap,
            'colorCoverMap'   => $colorCoverMap,
        ]);
    }

    public function cart()
    {
        return view('frontend.cart.index', ['step' => 1]);
    }

    public function delivery(Request $request)
    {
        $isBuyNow   = $request->boolean('is_buy_now');
        $checkoutId = $request->query('checkout_id');
        $addressId  = $request->query('address_id');
        $step       = (int) $request->query('step', 2);

        return view('frontend.cart.index', compact(
            'step',
            'isBuyNow',
            'checkoutId',
            'addressId'
        ));
    }

    public function success()
    {
        return view('frontend.cart.index', ['step' => 4]);
    }

    public function buyNow($id, Request $request)
    {
        $product = Product::findOrFail($id);
        $buyNowItem = [
            'product_id' => $product->id,
            'name'       => $product->name,
            'qty'        => (int) $request->query('qty', 1),
            'size'       => $request->query('size'),
            'color'      => $request->query('color'),
            'price'      => $request->query('price'),
            'matrix'     => $request->query('matrix'),
        ];
        session(['buy_now' => $buyNowItem]);
        return redirect()->route('shop.delivery');
    }
}
