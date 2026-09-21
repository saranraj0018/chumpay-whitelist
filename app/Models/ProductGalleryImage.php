<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGalleryImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'variant_id'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
