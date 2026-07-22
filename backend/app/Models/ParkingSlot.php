<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParkingSlot extends Model
{
    use HasFactory;

    protected $fillable = [

    'slot_code',

    'status',

    'area_code',

    'area_name',

    'allowed_vehicle',

    'allowed_role'

];

    public function parkingLogs()
    {
        return $this->hasMany(ParkingLog::class);
    }
}