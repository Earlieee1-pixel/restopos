<?php

namespace App\Services\Product;

use App\Models\Product;

// Lohika para sa mga produkto/menu items
class ProductService
{
    // Kuha sa tanan produkto, kauban ang kategorya
    public function getAllProducts()
    {
        return Product::with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
    }

    // Kuha sa tanan soft-deleted nga produkto
    public function getTrashedProducts()
    {
        return Product::onlyTrashed()
            ->with('category')
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    // I-restore ang soft-deleted nga produkto
    public function restoreProduct(int $id): Product
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return $product->load('category');
    }

    // Permanenteng tangtangon ang produkto
    public function forceDeleteProduct(int $id): void
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();
    }

    // Buhatan ug bag-ong produkto
    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    // I-edit ang produkto
    public function updateProduct(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    // Tangtangon (soft delete)
    public function deleteProduct(int $id): void
    {
        Product::findOrFail($id)->delete();
    }

    // Kuha sa usa ka produkto
    public function getProduct(int $id): Product
    {
        return Product::findOrFail($id);
    }

    // On/off ang availability sa menu
    public function toggleAvailability(int $id): Product
    {
        $product = Product::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);
        return $product;
    }
}
