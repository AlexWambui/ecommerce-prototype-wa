<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\Products\ProductDetailsResource;
use App\Http\Resources\Products\ProductIndexPageResource;

class GuestProductController extends Controller
{
    public function index(Product $product)
    {
        $product->load('images');

        $related_products = collect();

        // Same category
        if ($product->category_id) {
            $categoryProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(8)
                ->get();
            
            $related_products = $related_products->concat($categoryProducts);
        }

        // Same brand
        if ($related_products->count() < 8 && $product->brand_id) {
            $brandProducts = Product::where('brand_id', $product->brand_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                // Exclude products we already grabbed from Step 1
                ->whereNotIn('id', $related_products->pluck('id'))
                ->inRandomOrder()
                ->limit(8 - $related_products->count()) 
                ->get();
            
            $related_products = $related_products->concat($brandProducts);
        }

        // Random products
        if ($related_products->count() < 8) {
            $randomProducts = Product::where('id', '!=', $product->id)
                ->where('is_active', true)
                // Exclude products we already grabbed from Steps 1 & 2
                ->whereNotIn('id', $related_products->pluck('id'))
                ->inRandomOrder()
                ->limit(8 - $related_products->count())
                ->get();
            
            $related_products = $related_products->concat($randomProducts);
        }

        return inertia('guest/products/details/Index', [
            'product' => new ProductDetailsREsource($product),
            'related_products' => ProductIndexPageResource::collection($related_products)
        ]);
    }
}
