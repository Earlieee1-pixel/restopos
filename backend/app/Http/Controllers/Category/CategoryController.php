<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Ari ang pag-manage sa mga kategorya sa menu
class CategoryController extends Controller
{
    // Tanan kategorya, sorted by sort_order
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return response()->json($categories);
    }

    // Bag-ong kategorya
    public function store(Request $request): JsonResponse
    {
        // Manager ug admin lang pwede mag-add
        if (!in_array(auth()->user()?->role, ['manager', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $category = Category::create($request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:categories,name'],
            'icon'       => ['nullable', 'string', 'max:10'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]));

        return response()->json($category, 201);
    }

    // I-update ang kategorya
    public function update(Request $request, int $id): JsonResponse
    {
        // Manager ug admin lang pwede mag-edit
        if (!in_array(auth()->user()?->role, ['manager', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $category = Category::findOrFail($id);
        $category->update($request->validate([
            'name'       => ['sometimes', 'string', 'max:255', 'unique:categories,name,' . $id],
            'icon'       => ['nullable', 'string', 'max:10'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]));

        return response()->json($category);
    }

    // Tangtangon ang kategorya (kung walay products)
    public function destroy(int $id): JsonResponse
    {
        // Admin lang pwede mag-delete
        if (auth()->user()?->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $category = Category::withCount('products')->findOrFail($id);

        // Dili pwede i-delete kung may products pa
        if ($category->products_count > 0) {
            return response()->json(['message' => 'Cannot delete a category that still has products.'], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
