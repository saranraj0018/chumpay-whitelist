<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\FormatsProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use FormatsProducts;

    public function index()
    {
        $userId = Auth::id();
        $wishlistIds = Wishlist::where('user_id', $userId)
            ->pluck('product_id')
            ->all();
        $products = Product::with([
            'get_category',
            'bulk_product',
            'product_variant.variantValues.attribute_value.get_attribute',
            'ratings',
        ])
            ->where('status', 'active')
            ->whereIn('id', $wishlistIds)
            ->latest()
            ->get()
            ->map(fn(Product $product) => $this->formatProduct($product, $wishlistIds))
            ->values();

        return view('frontend.whistlist', [
            'products' => $products,
        ]);
    }

    /**
     * Toggle wishlist (add/remove) via AJAX
     */
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status'  => false,
                'auth'    => false,
                'message' => 'Please log in to save items to your wishlist',
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId    = Auth::id();
        $productId = $request->product_id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status'     => true,
                'auth'       => true,
                'wishlisted' => false,
                'message'    => 'Removed from wishlist',
            ]);
        }
        $wishlist = new Wishlist();
        $wishlist->user_id =  $userId;
        $wishlist->product_id =  $productId;
        $wishlist->save();

        return response()->json([
            'status'     => true,
            'auth'       => true,
            'wishlisted' => true,
            'message'    => 'Added to wishlist',
        ]);
    }
}
