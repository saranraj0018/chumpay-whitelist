<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\FormatsProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use FormatsProducts;

    public function index(Request $request)
    {
        $categories = Category::where('status', 1)->get();
        $banners    = Banner::latest()->take(10)->get()->values();
        $wishlistIds = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->all()
            : [];
        // Fetch models, then format each (scans all variant/bulk rows)
        $products = Product::with([
            'get_category',
            'bulk_product',
            'product_variant.variantValues.attribute_value.get_attribute',
            'ratings',
        ])
            ->where('status', 'active')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn(Product $product) => $this->formatProduct($product, $wishlistIds))
            ->values();

        // Price range from the resolved spans (covers all 3 types)
        $priceMin = (int) floor($products->min('priceMin') ?? 100);
        $priceMax = (int) ceil($products->max('priceMax') ?? 5000);
        if ($priceMax <= $priceMin) {
            $priceMax = $priceMin + 100;
        }

        $this->data['products']             = $products;
        $this->data['categories']           = $categories;
        $this->data['priceMin']             = $priceMin;
        $this->data['priceMax']             = $priceMax;
        $this->data['heroBannerDesktop']    = $banners->get(0)?->image;
        $this->data['heroBannerMobile']     = $banners->get(1)?->image ?? $this->data['heroBannerDesktop'];
        $this->data['categoryPromoBanners'] = $banners->slice(2, 2)->pluck('image')->values();
        $this->data['bottomBannerDesktop']  = $banners->get(4)?->image;
        $this->data['bottomBannerMobile']   = $banners->get(5)?->image ?? $this->data['bottomBannerDesktop'];
        $this->data['denimBanners']         = $banners->slice(6, 4)->pluck('image')->values();

        return view('frontend.home')->with($this->data);
    }

}
