<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantAttributeValue extends Model
{
    public function get_attribute()
    {
        return $this->belongsTo(VariantAttribute::class, 'attribute_id');
    }
}
