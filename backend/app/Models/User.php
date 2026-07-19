<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Vehicle;
use App\Models\ParkingToken;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function vehicles()
    {
    return $this->hasMany(Vehicle::class);
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

}