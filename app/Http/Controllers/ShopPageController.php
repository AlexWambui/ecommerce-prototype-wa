<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Http\Resources\Products\ProductIndexPageResource;

class ShopPageController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->search($request->search)
            ->where('is_active', true)
            ->with('images')
            ->orderBy('name')
            ->paginate(30);
        
        $product_categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->select('id', 'name', 'slug')
            ->get();

        return Inertia::render('guest/shoppage/Index', [
            'products' => ProductIndexPageResource::collection($products),
            'product_categories' => $product_categories,
            'filters' => [
                'search' => $request->search,
            ]
        ]);
    }
}