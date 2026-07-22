<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Http\Resources\Products\ProductCategoryResource;
use App\Http\Resources\Products\ProductHomePageResource;

class HomePageController extends Controller
{
    public function index()
    {
        $product_categories = ProductCategory::orderBy('name')->get();

        $new_arrivals = Product::where('is_new', true)->where('is_active', true)->with('images')->limit(4)->get();
        
        $most_popular = $this->getMostPopularProducts();

        return inertia('guest/homepage/Index', [
            'product_categories' => ProductCategoryResource::collection($product_categories),
            'new_arrivals' => ProductHomePageResource::collection($new_arrivals)->resolve(),
            'most_popular' => ProductHomePageResource::collection($most_popular)->resolve()
        ]);
    }

    private function getMostPopularProducts()
    {
        // Get up to 4 featured products
        $featured = Product::where('is_featured', true)
            ->where('is_active', true)
            ->with('images')
            ->limit(4)
            ->get();

        // If we have 4 featured products, return them
        if ($featured->count() >= 4) {
            return $featured;
        }

        // If we have less than 4 featured, get the remaining from random products
        $needed = 4 - $featured->count();
        
        $random = Product::where('is_active', true)
            ->where('is_featured', false) // Exclude featured products
            ->whereNotIn('id', $featured->pluck('id')) // Exclude already selected
            ->with('images')
            ->inRandomOrder()
            ->limit($needed)
            ->get();

        // Merge featured and random products
        return $featured->merge($random);
    }
}
