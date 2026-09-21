<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'pincode',
        'state',
        'city',
        'address_type',
        'is_default',
        'latitude',
        'longitude',
        'landmark',
        'created_by',
        'updated_by',
    ];
}
