<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParkingLog extends Model
{
    use HasFactory;


    protected $fillable=[

'user_id',

'guest_id',

'vehicle_id',

'vehicle_category',

'parking_slot_id',

'parking_token_id',

'check_in',

'check_out',

'status'

];


    protected $casts = [

        'check_in'=>'datetime',

        'check_out'=>'datetime'

    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }



    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }



    public function parkingSlot()
    {
        return $this->belongsTo(ParkingSlot::class);
    }



    public function parkingToken()
    {
        return $this->belongsTo(
            ParkingToken::class,
            'parking_token_id'
        );
    }

}