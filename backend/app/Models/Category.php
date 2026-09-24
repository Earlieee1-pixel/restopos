<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model sa kategorya (e.g. Burger, Chicken, Drinks)
class Category extends Model
{
    protected $fillable = ['name', 'icon', 'sort_order'];

    // Mga produkto sa kategorya
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
