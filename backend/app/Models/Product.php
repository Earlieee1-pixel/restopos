<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model sa mga pagkaon/inumon
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'is_available',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    // Asa kategorya kini nga produkto
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Labot sa order items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
