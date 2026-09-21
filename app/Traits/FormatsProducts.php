<?php
// app/Traits/FormatsProducts.php

namespace App\Traits;

use App\Models\Product;

trait FormatsProducts
{
    /**
     * Format a product into a display-ready array.
     * Variant products use only their first variant's sale/regular price;
     * bulk products use only their first tier's sale/regular price
     * (ordered by minimum qty).
     */
    protected function formatProduct(Product $product, array $wishlistIds = []): array
    {
        $priceMin = null;
        $priceMax = null;
        $regularForDiscount = 0;

        if ($product->product_type === 'single') {
            $regular = (float) ($product->regular_price ?? 0);
            $sale    = (float) ($product->sale_price ?? $regular);
            $eff     = $sale > 0 ? $sale : $regular;

            $priceMin = $eff;
            $priceMax = $eff;
            $regularForDiscount = $regular;
        } elseif ($product->product_type === 'variant') {
            $first = $product->product_variant->sortBy('id')->first();

            if ($first) {
                $regular = (float) ($first->regular_price ?? 0);
                $sale    = (float) ($first->sale_price ?? $regular);
                $eff     = $sale > 0 ? $sale : $regular;

                $priceMin = $eff;
                $priceMax = $eff;
                $regularForDiscount = $regular;
            }
        } elseif ($product->product_type === 'bulk') {
            $first = $product->bulk_product->sortBy([['minimum', 'asc'], ['id', 'asc']])->first();

            if ($first) {
                $regular = (float) ($first->regular_price ?? 0);
                $sale    = (float) ($first->sale_price ?? $regular);
                $eff     = $sale > 0 ? $sale : $regular;

                $priceMin = $eff;
                $priceMax = $eff;
                $regularForDiscount = $regular;
            }
        }

        $priceMin = $priceMin ?? 0;
        $priceMax = $priceMax ?? 0;

        $rating = round((float) $product->ratings->avg('rating'), 1);
        $ratingCount = $product->ratings->count();


        $stock = $product->product_type === 'variant'
            ? (int) $product->product_variant->sum('stock')
            : (int) $product->stock;

        $colors = collect();
        $fabric = '';
        foreach ($product->product_variant as $variant) {
            foreach ($variant->variantValues as $val) {
                $attrName = strtolower(optional($val->attribute_value?->get_attribute)->name ?? '');
                if ($attrName === 'color' || $attrName === 'colour') {
                    $colors->push($val->attribute_value->value);
                }
                if ($attrName === 'fabric' && $fabric === '') {
                    $fabric = $val->attribute_value->value;
                }
            }
        }

        $discount = 0;
        if ($regularForDiscount > 0 && $priceMin > 0 && $priceMin < $regularForDiscount) {
            $discount = (int) round((($regularForDiscount - $priceMin) / $regularForDiscount) * 100);
        }

        return [
            'id'          => $product->id,
            'name'        => $product->name,
            'category'    => $product->get_category?->name ?? '',
            'category_id' => $product->category_id,
            'fabric'      => $fabric,
            'price'       => $priceMin,
            'priceMin'    => $priceMin,
            'priceMax'    => $priceMax,
            'oldPrice'    => $regularForDiscount,
            'productType' => $product->product_type,
            'perPiecePrice' => (float) ($product->per_piece_price ?? 0),
            'rating'      => $rating > 0 ? $rating : 0,
            'rating_count' => $ratingCount,
            'discount'    => $discount,
            'stock'       => $stock > 0 ? 'in' : 'out',
            'image'       => $product->main_image,
            'colors'      => $colors->unique()->values()->take(3),
            'wishlisted'  => in_array($product->id, $wishlistIds),
            'description' => $product->description ?? '',
        ];
    }
}
