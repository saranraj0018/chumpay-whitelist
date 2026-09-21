<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkProduct extends Model
{
    protected $fillable = [
        'product_id',
        'minimum',
        'maximum',
        'regular_price',
        'sale_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function bulk_product_variants()
    {
        return $this->hasMany(BulkProductVariant::class, 'bulk_product_id');
    }
}
