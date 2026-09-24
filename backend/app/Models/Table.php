<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model sa mga mesa sa restaurant
class Table extends Model
{
    protected $fillable = [
        'table_number',
        'capacity',
        'status',   // available, occupied, reserved
        'floor',
    ];

    // Mga order sa mesa
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Aktibo nga order karon
    public function activeOrder()
    {
        return $this->hasOne(Order::class)->whereIn('status', ['pending', 'preparing']);
    }
}
