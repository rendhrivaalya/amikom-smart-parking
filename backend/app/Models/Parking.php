<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'parking_token_id',
        'check_in',
        'check_out',
        'status'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function token()
    {
        return $this->belongsTo(ParkingToken::class,'parking_token_id');
    }
}