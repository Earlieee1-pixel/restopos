<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Usa ka linya sa order (produkto + qty)
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    // Unsa nga order kini
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Unsang produkto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
