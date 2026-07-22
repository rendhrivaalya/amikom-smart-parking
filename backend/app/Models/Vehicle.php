<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ParkingToken;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'owner_name',
    'stnk_number',
    'plate_number',
    'vehicle_type',
    'brand',
    'vehicle_model',
    'vehicle_year',
    'color',
    'qr_code',
    'status',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parkingLogs()
    {
        return $this->hasMany(ParkingLog::class);
    }

    public function parkingTokens()
{
    return $this->hasMany(ParkingToken::class);
}

public function parkings()
{
    return $this->hasMany(Parking::class);
}

public function getVehicleCategoryAttribute()
{
    return $this->vehicle_type == 'Motor'
        ? 'Roda 2'
        : 'Roda 4';
}

}