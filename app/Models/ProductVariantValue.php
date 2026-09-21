<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantValue extends Model
{
    protected $fillable = [
        'product_variant_id',
        'attribute_id',
        'attribute_value_id',
    ];
    
    public function attribute_value()
    {
        return $this->belongsTo(VariantAttributeValue::class, 'attribute_value_id');
    }
}
