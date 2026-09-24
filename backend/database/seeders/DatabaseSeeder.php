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

        // Sample products per category — kauban ang Imgur image URLs
        $sampleProducts = [
            'Chicken' => [
                ['Chickenjoy 1pc',    89,  'https://i.imgur.com/D10whPz.jpg'],
                ['Chickenjoy 2pc',    159, 'https://i.imgur.com/6dDWDxg.jpg'],
                ['Chicken Sandwich',  99,  'https://i.imgur.com/JIxpUWt.jpg'],
            ],
            'Burger' => [
                ['Yumburger',         49,  'https://i.imgur.com/pA1I9NI.jpg'],
                ['Cheeseburger',      59,  'https://i.imgur.com/qtvjtX4.jpg'],
                ['Double Yumburger',  79,  'https://i.imgur.com/KBpavFS.jpg'],
            ],
            'Pasta' => [
                ['Jolly Spaghetti',   79,  'https://i.imgur.com/1zvEXjc.jpg'],
                ['Baked Mac',         89,  'https://i.imgur.com/rOurbXI.jpg'],
            ],
            'Drinks' => [
                ['Coke Float',        55,  'https://i.imgur.com/KfOMmfR.jpg'],
                ['Pineapple Juice',   45,  'https://i.imgur.com/1cGkLs5.jpg'],
                ['Bottled Water',     30,  'https://i.imgur.com/G0F7Tx8.jpg'],
            ],
            'Dessert' => [
                ['Peach Mango Pie',   39,  'https://i.imgur.com/fJ1QcSO.jpg'],
                ['Hot Fudge Sundae',  49,  'https://i.imgur.com/oC1xEvl.jpg'],
            ],
        ];

        foreach ($categories as $catData) {
            // I-skip kung naa na ang kategorya
            $category = Category::firstOrCreate(
                ['name' => $catData['name']],
                ['icon' => $catData['icon'], 'sort_order' => $catData['sort_order']]
            );

            // I-seed ang products para sa kategorya
            foreach ($sampleProducts[$catData['name']] as [$productName, $price, $image]) {
                $product = Product::firstOrCreate(
                    ['name' => $productName, 'category_id' => $category->id],
                    [
                        'description'  => $productName . ' - ' . $catData['name'],
                        'price'        => $price,
                        'image'        => $image,
                        'is_available' => true,
                    ]
                );

                // I-update ang image para ma-sync ang latest URL
                if ($product->image !== $image) {
                    $product->update(['image' => $image]);
                }
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
