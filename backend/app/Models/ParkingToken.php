<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingToken extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'token',
        'type',
        'expired_at',
        'is_used',
        'used_at'
    ];


    protected $casts = [
        'expired_at' => 'datetime',
        'is_used' => 'boolean'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function parking()
{
    return $this->hasOne(Parking::class);
}
}