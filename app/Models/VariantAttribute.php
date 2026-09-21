<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantAttribute extends Model
{
    public function get_variant_value()
    {
        return $this->hasMany(VariantAttributeValue::class, 'attribute_id', 'id');
    }
}
