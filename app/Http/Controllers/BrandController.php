<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Exception;
use App\Models\Brand;
use App\Http\Requests\Products\BrandRequest;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        $brands = $query
            ->orderBy('name')
            ->withCount('products')
            ->get();

        return inertia('app/products/brands/Index', [
            'brands' => $brands,
            'filters' => [
                'search' => $request->search
            ]
        ]);
    }

    public function create()
    {
        return inertia('app/products/brands/Create');
    }

    public function store(BrandRequest $request)
    {
        $validated_data = $request->validated();

        $image = $validated_data['image'] ?? null;
        unset($validated_data['image']);

        try {
            DB::beginTransaction();

            $brand = Brand::create($validated_data);

            if ($image && $image instanceof \Illuminate\Http\UploadedFile) {
                $filename = $this->uploadImage($image, $brand);
                $brand->update(['image' => $filename]);
            }

            DB::commit();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Brand created successfully"
            ]);

            return to_route('brands.index');
        } catch (Exception $e) {
            DB::rollback();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to save brand: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }

    public function edit(Brand $brand)
    {
        return inertia('app/products/brands/Edit', [
            'brand' => $brand
        ]);
    }

    public function update(Brand $brand, BrandRequest $request)
    {
        $validated_data = $request->validated();

        $image = $validated_data['image'] ?? null;
        unset($validated_data['image']);

        try {
            DB::beginTransaction();

            $brand->update($validated_data);

            if ($request->hasFile('image')) {
                // Delete old logo if exists
                if ($brand->image) {
                    $oldPath = "brands/{$brand->image}";
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $image_path = $this->uploadImage($request->file('image'), $brand);
                $brand->update(['image' => $image_path]);
            }

            DB::commit();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Brand updated successfully"
            ]);

            return to_route('brands.index');
        } catch (Exception $e) {
            DB::rollBack();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to update brand: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }

    public function destroy(Brand $brand)
    {
        try {
            if ($brand->image) {
                Storage::disk('public')->delete('brands/' . $brand->image);
            }

            $brand->delete();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Brand deleted successfully"
            ]);

            return to_route('brands.index');
        } catch (Exception $e) {
            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to delete brand: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }

    private function uploadImage($file, Brand $brand): string
    {
        $slug = Str::slug($brand->name);
        $timestamp = now()->format('Ymd');
        $random = 'albachuza_'.Str::random(6);
        $extension = $file->getClientOriginalExtension();

        $filename = "{$slug}_{$timestamp}_{$random}.{$extension}";

        $file->storeAs('brands', $filename, 'public');

        return $filename;
    }
}