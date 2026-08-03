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
            $current_year = now()->year;

            $completed_orders = Order::completed();

            // Revenue & COGS (Cost of Goods Sold)
            $total_revenue = $completed_orders->sum('total_selling_price');
            $total_cogs = $completed_orders->sum('total_cost_price');

            // Gross Profit
            $total_gross_profit = $total_revenue - $total_cogs;
            $gross_margin = $total_revenue > 0 ? ($total_gross_profit / $total_revenue) * 100 : 0;

            // Operating Expenses
            // $total_operating_expenses = Expense::whereYear('expense_date', $current_year)->sum('amount'); // TODO: uncomment after adding expenses model

            // Net Profit
            // $total_net_profit = $total_gross_profit - $total_operating_expenses; // TODO: uncomment after uncommenting total_operating_expenses
            //$net_margin = $total_revenue > 0 ? ($total_net_profit / $total_revenue) * 100 : 0; // TODO: uncomment after uncommenting total_net_profit

            // Expense by category
            // $expenses_by_category = Expense::whereYear('expense_date', $currentYear)->select('category', DB::raw('SUM(amount) as total'))->groupBy('category')->pluck('total', 'category')->toArray(); // TODO: uncomment after adding the expense model

            $monthly_sales = [];
            for ($i = 1; $i <= 12; $i++) {
                // Get total sales for each month of the current year
                $monthly_sales[] = Order::delivered()
                    ->whereYear('sold_at', now()->year)
                    ->whereMonth('sold_at', $i)
                    ->sum('total_selling_price');
            }

            $completed_order_ids = Order::completed()->pluck('id');

            $mpesa_total = Payment::whereIn('order_id', $completed_order_ids)->where('payment_method', 'mpesa')->sum('amount');
            $cash_total = Payment::whereIn('order_id', $completed_order_ids)->where('payment_method', 'cash')->sum('amount');

            $total_revenue = Order::completed()->sum('total_selling_price');
            $total_cost = Order::completed()->sum('total_cost_price');
            $total_gross_profit = $total_revenue - $total_cost;
            $total_delivered_orders = Order::delivered()->count();
            $average_order_value = $total_delivered_orders > 0 ? $total_revenue / $total_delivered_orders : 0;

            // Year to date revenue
            $current_ytd_revenue = Order::whereYear('sold_at', now()->year)->completed()->sum('total_selling_price');

            $previous_ytd_revenue = Order::whereYear('sold_at', now()->year - 1)->completed()->sum('total_selling_price');

            $growth_percentage = $previous_ytd_revenue > 0 ? (($current_ytd_revenue - $previous_ytd_revenue) /$previous_ytd_revenue) * 100 : 0;
            
            $unique_customers = Order::where('order_status', 'delivered')->distinct('customer_email')->count('customer_email');

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

                    'monthly_sales' => $monthly_sales,
                    'payment_breakdown' => [
                        'mpesa' => (float) $mpesa_total,
                        'cash' => (float) $cash_total,
                    ],

                    'total_revenue' => (float) $total_revenue,
                    'total_cost' => (float) $total_cost,
                    'total_gross_profit' => (float) $total_gross_profit,
                    'average_order_value' => (float) $average_order_value,
                    'current_ytd_revenue' => (float) $current_ytd_revenue,
                    'growth_percentage' => (float) $growth_percentage,
                    'unique_customers' => $unique_customers,
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
                'total_spent' => (clone $ordersQuery)->paid()->sum('total_selling_price'),
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
