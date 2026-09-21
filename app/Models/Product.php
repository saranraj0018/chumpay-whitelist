<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'product_type',
        'product_code',
        'description',
        'regular_price',
        'sale_price',
        'stock',
        'main_image',
    ];

    public function product_variant()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function bulk_product()
    {
        return $this->hasMany(BulkProduct::class, 'product_id');
    }

    public function get_category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function product_gallery_image()
    {
        return $this->hasMany(ProductGalleryImage::class);
    }

    public function ratings()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }
}
