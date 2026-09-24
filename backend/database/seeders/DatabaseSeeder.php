<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Sample data para masubok ang POS
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default users — updateOrCreate para dili mag-crash kung na-seed na
        User::updateOrCreate(['email' => 'admin@restopos.com'], [
            'name'      => 'Admin User',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'manager@restopos.com'], [
            'name'      => 'Manager User',
            'password'  => Hash::make('password'),
            'role'      => 'manager',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'cashier@restopos.com'], [
            'name'      => 'Juan Cashier',
            'password'  => Hash::make('password'),
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        // Mga kategorya sa menu
        $categories = [
            ['name' => 'Chicken',  'icon' => '🍗', 'sort_order' => 1],
            ['name' => 'Burger',   'icon' => '🍔', 'sort_order' => 2],
            ['name' => 'Pasta',    'icon' => '🍝', 'sort_order' => 3],
            ['name' => 'Drinks',   'icon' => '🥤', 'sort_order' => 4],
            ['name' => 'Dessert',  'icon' => '🍨', 'sort_order' => 5],
        ];

        // Sample products per category
        $sampleProducts = [
            'Chicken'  => [['Chickenjoy 1pc', 89], ['Chickenjoy 2pc', 159], ['Chicken Sandwich', 99]],
            'Burger'   => [['Yumburger', 49], ['Cheeseburger', 59], ['Double Yumburger', 79]],
            'Pasta'    => [['Jolly Spaghetti', 79], ['Baked Mac', 89]],
            'Drinks'   => [['Coke Float', 55], ['Pineapple Juice', 45], ['Bottled Water', 30]],
            'Dessert'  => [['Peach Mango Pie', 39], ['Hot Fudge Sundae', 49]],
        ];

        foreach ($categories as $catData) {
            // I-skip kung naa na ang kategorya
            $category = Category::firstOrCreate(
                ['name' => $catData['name']],
                ['icon' => $catData['icon'], 'sort_order' => $catData['sort_order']]
            );

            // I-seed ang products para sa kategorya
            foreach ($sampleProducts[$catData['name']] as [$productName, $price]) {
                Product::firstOrCreate(
                    ['name' => $productName, 'category_id' => $category->id],
                    [
                        'description'  => $productName . ' - ' . $catData['name'],
                        'price'        => $price,
                        'is_available' => true,
                    ]
                );
            }
        }

        // Sample tables — i-skip kung naa na
        for ($i = 1; $i <= 10; $i++) {
            Table::firstOrCreate(
                ['table_number' => $i],
                [
                    'capacity' => ($i <= 5) ? 4 : 6,
                    'floor'    => ($i <= 5) ? 'Ground' : 'Second',
                    'status'   => 'available',
                ]
            );
        }
    }
}
