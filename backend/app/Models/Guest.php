<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'identity_number',

        'plate_number',
        'vehicle_type',
        'brand',
        'vehicle_model',
        'vehicle_year',
        'color',

        'purpose',
        'destination',

        'stnk_image',

        'qr_token',
        'expired_at',
        'is_used',
        'used_at',

        'status'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function getVehicleCategoryAttribute()
{
    return $this->vehicle_type == 'Motor'
        ? 'Roda 2'
        : 'Roda 4';
}
}