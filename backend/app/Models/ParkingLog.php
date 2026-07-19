<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParkingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'parking_slot_id',
        'check_in',
        'check_out',
        'status'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function parkingSlot()
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}