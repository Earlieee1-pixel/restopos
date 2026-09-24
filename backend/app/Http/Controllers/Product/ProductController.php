<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Ari ang pag-manage sa menu/products
class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // Tanan available nga produkto
    public function index(): JsonResponse
    {
        return response()->json(
            ProductResource::collection($this->productService->getAllProducts())
        );
    }

    // Tanan soft-deleted nga produkto (archive)
    public function trashed(): JsonResponse
    {
        // Manager ug admin lang pwede
        if (!in_array(auth()->user()?->role, ['manager', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(
            ProductResource::collection($this->productService->getTrashedProducts())
        );
    }

    // I-restore ang produkto gikan sa archive
    public function restore(int $id): JsonResponse
    {
        // Manager ug admin lang pwede
        if (!in_array(auth()->user()?->role, ['manager', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $product = $this->productService->restoreProduct($id);
        return response()->json(new ProductResource($product));
    }

    // Permanenteng tangtangon ang produkto
    public function forceDelete(int $id): JsonResponse
    {
        // Admin lang pwede mag-permanent delete
        if (auth()->user()?->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->productService->forceDeleteProduct($id);
        return response()->json(['message' => 'Product permanently deleted.']);
    }

    // Bag-ong produkto sa menu
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());
        return response()->json(new ProductResource($product->load('category')), 201);
    }

    // I-update ang produkto
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->updateProduct($id, $request->validated());
        return response()->json(new ProductResource($product->load('category')));
    }

    // I-archive/delete ang produkto (soft delete)
    public function destroy(int $id): JsonResponse
    {
        // Manager ug admin lang pwede mag-delete
        if (!in_array(auth()->user()?->role, ['manager', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->productService->deleteProduct($id);
        return response()->json(['message' => 'Product deleted successfully.']);
    }

    // I-toggle kung available ba ang produkto sa menu
    public function toggleAvailability(int $id): JsonResponse
    {
        $product = $this->productService->toggleAvailability($id);
        return response()->json(new ProductResource($product->load('category')));
    }

    // I-upload ang product image
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        // Manager ug admin lang pwede mag-upload
        if (!in_array(auth()->user()?->role, ['manager', 'admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'image' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $product = $this->productService->getProduct($id);

        // Tangtangon ang daan nga image kung naa
        if ($product->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
        }

        // I-store ang bag-ong image
        $path = $request->file('image')->store('products', 'public');
        $product->update(['image' => '/storage/' . $path]);

        return response()->json(new ProductResource($product->load('category')));
    }
}
