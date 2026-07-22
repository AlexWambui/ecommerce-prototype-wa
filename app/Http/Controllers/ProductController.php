<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Exception;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Http\Resources\Products\ProductIndexPageResource;
use App\Http\Requests\Products\ProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('name')->paginate(20);

        return inertia('app/products/products/Index', [
            'products' => ProductIndexPageResource::collection($products),
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function create()
    {
        $product_categories = ProductCategory::select('id', 'name')->orderBy('name')->get();

        return inertia('app/products/products/Create', [
            'product_categories' => $product_categories
        ]);
    }

    public function store(ProductRequest $request)
    {
        $validated_data = $request->validated();

        $images = $validated_data['images'] ?? [];
        unset($validated_data['images']);

        try {
            DB::beginTransaction();

            $product = Product::create($validated_data);

            if (!empty($images)) {
                $this->uploadImages($images, $product);
            }

            DB::commit();
        
            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Product created successfully"
            ]);

            return to_route('products.index');
        } catch (Exception $e) {
            DB::rollback();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to save product: {$e->getMessage()}"
            ]);
        }
    }

    public function edit(Product $product)
    {
        $product_categories = ProductCategory::select('id', 'name')->orderBy('name')->get();

        $product->load('category', 'images');

        return inertia('app/products/products/Edit', [
            'product' => $product,
            'product_categories' => $product_categories
        ]);
    }
    
    public function update(ProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $images = $validated['images'] ?? [];
        unset($validated['images']);

        $imagesToDelete = $request->input('images_to_delete', []);
        if (is_string($imagesToDelete)) {
            $imagesToDelete = json_decode($imagesToDelete, true) ?? [];
        }

        DB::beginTransaction();

        try {

            $product->update($validated);

            // DELETE ONLY SELECTED IMAGES
            if (!empty($imagesToDelete)) {
                $oldImages = $product->images()->whereIn('id', $imagesToDelete)->get();

                foreach ($oldImages as $image) {
                    if (Storage::disk('public')->exists("products/{$image->name}")) {
                        Storage::disk('public')->delete("products/{$image->name}");
                    }
                    $image->delete();
                }
            }

            // NEXT SORT ORDER (IMPORTANT FIX)
            $nextSortOrder = $product->images()->max('sort_order') ?? 0;

            if (!empty($images)) {
                $this->uploadImages($images, $product, $nextSortOrder);
            }

            DB::commit();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Product updated successfully"
            ]);

            return to_route('products.index');

        } catch (\Throwable $e) {
            DB::rollBack();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Product failed to update: {$e->getMessage()}"
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toggleAttribute(Request $request, Product $product)
    {
        $request->validate([
            'attribute' => 'required|in:is_featured,is_new,is_active',
            'value' => 'required|boolean'
        ]);

        try {
            $attribute = $request->attribute;
            $value = $request->value;
            
            $product->update([$attribute => $value]);

            return response()->json([
                'success' => true,
                'message' => "Product {$attribute} updated successfully",
                'product' => [
                    'id' => $product->id,
                    $attribute => $product->$attribute,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy(Product $product)
    {
        $product->delete();

        Inertia::flash('toast', [
            'type' => "success",
            'message' => "Product deleted successfully"
        ]);
        
        return to_route('products.index');
    }

    protected function uploadImages(array $images, Product $product, int $startSortOrder = 0): void
    {
        $slug = Str::slug($product->name);
        $timestamp = now()->format('Ymd_His');
        $productId = $product->id;

        foreach ($images as $index => $image) {
            if (!$image instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }

            $extension = $image->getClientOriginalExtension();
            $random = Str::random(6);
            $sortOrder = $startSortOrder + $index + 1;

            $filename = "{$slug}_{$productId}_{$index}_{$timestamp}_{$random}.{$extension}";
            
            $image->storeAs('products', $filename, 'public');
            
            $product->images()->create([
                'name' => $filename,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}