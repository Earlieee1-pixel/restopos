<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Model sa mga empleyado/cashier
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // cashier, manager, admin
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // Labot sa iyang mga order
    public function orders()
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }
}
