<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRoles;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Brand;
use App\Models\DeliveryLocation;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;

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
            $monthlySales = [];
            for ($i = 1; $i <= 12; $i++) {
                // Get total sales for each month of the current year
                $monthlySales[] = Order::whereYear('sold_at', now()->year)
                    ->whereMonth('sold_at', $i)
                    ->where('order_status', 'paid') // Only count fully paid orders
                    ->sum('total_amount');
            }

            $mpesaTotal = Payment::where('payment_method', 'mpesa')->sum('amount');
            $cashTotal = Payment::where('payment_method', 'cash')->sum('amount');

            return inertia('app/dashboards/Admin', [
                'user' => $user,
                'stats' => [
                    // --- MONTHLY SALES BAR CHART DATA ---
                    'total_customers' => User::where('role', UserRoles::CUSTOMER)->count(),
                    'total_users' => User::count(),
                    'total_products' => Product::count(),
                    'total_product_categories' => ProductCategory::count(),
                    'total_brands' => Brand::count(),
                    'total_delivery_locations' => DeliveryLocation::count(),
                    'total_delivery_areas' => DeliveryArea::count(),
                    'total_orders' => Order::count(),
                    'total_pending_orders' => Order::where('order_status', 'partially_paid')->count(),
                    'total_callbacks' => 1001,
                    'total_unread_callbacks' => 1001,

                    'monthly_sales' => $monthlySales,
                    'payment_breakdown' => [
                        'mpesa' => $mpesaTotal,
                        'cash' => $cashTotal,
                    ]
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
