<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRoles;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role === UserRoles::SUPER_ADMIN) {
            return inertia('app/dashboards/SuperAdmin', [
                'user' => $user,
                'stats' => ''
            ]);
        }

        if ($user->role === UserRoles::ADMIN) {
            return inertia('app/dashboards/Admin', [
                'user' => $user,
                'stats' => [
                    'total_customers' => User::where('role', UserRoles::CUSTOMER)->count(),
                    'total_users' => User::count(),
                    'total_products' => Product::count(),
                    'total_product_categories' => ProductCategory::count(),
                    'total_delivery_locations' => 00,
                    'total_delivery_areas' => 00,
                    'total_callbacks' => 00,
                    'total_unread_callbacks' => 00
                ]
            ]);
        }

        if ($user->role === UserRoles::SELLER) {
            return inertia('app/dashboards/Seller', [
                'user' => $user,
                'stats' => ''
            ]);
        }

        if ($user->role === UserRoles::CUSTOMER) {
            $ordersQuery = $user->orders();

            $stats = [
                'total_orders' => $ordersQuery->count(),
                'pending_orders' => (clone $ordersQuery)->pending()->count(),
                'processing_orders' => (clone $ordersQuery)->processing()->count(),
                'shipped_orders' => (clone $ordersQuery)->shipped()->count(),
                'delivered_orders' => (clone $ordersQuery)->delivered()->count(),
                'cancelled_orders' => (clone $ordersQuery)->cancelled()->count(),
                'active_orders' => (clone $ordersQuery)->active()->count(), // Using the new scope
                'total_spent' => (clone $ordersQuery)->paid()->sum('total_amount'),
                // 'recent_orders' => OrderResource::collection($ordersQuery->latest()->paginate(20)),
            ];

            return inertia('app/dashboards/Customer', [
                'user' => $user,
                'stats' => $stats
            ]);
        }
        return inertia('app/dashboards/Dashboard');
    }
}
