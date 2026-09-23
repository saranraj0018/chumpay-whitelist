<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'variant_size_id',
        'bulk_product_id',
        'variant_color_id',
        'product_name',
        'quantity',
        'unit_price',
        'net_amount',
        'gst_type',
        'gst_percentage',
        'gst_amount',
        'matrix',
    ];

    protected function casts(): array
    {
        return [
            'matrix' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'order_detail_id', 'id');
    }

    public function product_variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function variantSizeValue()
    {
        return $this->belongsTo(VariantAttributeValue::class, 'variant_size_id');
    }

    public function variantColorValue()
    {
        return $this->belongsTo(VariantAttributeValue::class, 'variant_color_id');
    }
}
