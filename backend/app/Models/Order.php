<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model sa mga order sa customers
class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'cashier_id',
        'table_id',
        'status',         // pending, preparing, served, cancelled
        'order_type',     // dine-in, takeout
        'total_amount',
        'discount',
        'amount_tendered',
        'change',
        'notes',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'discount'        => 'decimal:2',
        'amount_tendered' => 'decimal:2',
        'change'          => 'decimal:2',
    ];

    // Kinsa ang nag-asikaso
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // Unsang mesa
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    // Mga gi-order nga pagkaon
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
