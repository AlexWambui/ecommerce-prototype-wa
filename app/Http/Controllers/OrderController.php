<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Products\ProductPOSResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('order_channel', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(50);

        return inertia('app/orders/orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => [
                'search' => $request->search
            ],
        ]);
    }

    public function create()
    {
        $products = Product::query()
            ->orderBy('name')
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->get();

        return inertia('app/orders/orders/Create', [
            'products' => ProductPOSResource::collection($products)
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $validated = $request->validate([
            'order_channel' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_method' => 'required|in:shop,delivery',
            'location' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'delivery_cost' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.id' => 'required|exists:products,id',
            'cart_items.*.price' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($validated) {
        
            // --- CALCULATE TOTALS ---
            $subtotal = collect($validated['cart_items'])->sum(fn($item) => $item['price']);
            $totalAmount = $subtotal + $validated['delivery_cost'];

            $orderStatus = $validated['amount_paid'] >= $totalAmount ? 'paid' : 'partially_paid';

            $deliveryLocation = 'shop';
            $deliveryArea = 'shop';
            $deliveryAddress = 'shop';

            if ($validated['delivery_method'] === 'delivery') {
                $deliveryLocation = $validated['location'];
                $deliveryArea = $validated['area'];
                $deliveryAddress = $validated['address'];
            }

            // --- CREATE THE ORDER ---
            $order = Order::create([
                'order_number' => 'Ord_' . strtoupper(Str::random(6)) . '_' . now()->format('ymd'),
                'order_channel' => $validated['order_channel'],
                'order_status' => $orderStatus,
                
                'subtotal' => $subtotal,
                'delivery_cost' => $validated['delivery_cost'],
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'amount_paid' => $validated['amount_paid'],

                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],

                'delivery_location' => $deliveryLocation,
                'delivery_area' => $deliveryArea,
                'delivery_address' => $deliveryAddress,
                
                // Snapshots
                'customer_details_snapshot' => json_encode([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                ]),
                'delivery_details_snapshot' => json_encode([
                    'location' => $validated['location'] ?? null,
                    'area' => $validated['area'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]),
                'pricing_snapshot' => json_encode([
                    'subtotal' => $subtotal,
                    'delivery' => $validated['delivery_cost'],
                    'total' => $totalAmount,
                ]),
                'payment_snapshot' => json_encode([
                    'methods' => collect($validated['payments'])
                        ->pluck('method')
                        ->unique()
                        ->implode(', '), // e.g., "mpesa, cash"
                    'total_paid' => collect($validated['payments'])->sum('amount'),
                ]),
                
                'sold_at' => now(),
            ]);

            OrderStatus::create([
                'order_id' => $order->id,
                'status' => $orderStatus,
                'notes' => 'Order created via ' . $validated['order_channel'],
                'user_id' => Auth::id(), // If admin is logged in
                'changed_at' => now(),
            ]);

            // --- CREATE ORDER ITEMS (Loop through cart) ---
            foreach ($validated['cart_items'] as $item) {
                $product = Product::find($item['id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    
                    'name' => $product->name,
                    'quantity' => 1,
                    'cost_price' => $product->cost_price ?? 0,
                    'selling_price' => $item['price'],
                    'discount' => 0,
                    
                    'product_name_snapshot' => $product->name,
                    'product_sku_snapshot' => $product->sku,
                ]);

                // Decrease stock
                $product->decrement('current_stock', 1);
                // $product->update(['is_active' => false]);
            }

            // --- CREATE PAYMENT RECORD ---
            foreach ($validated['payments'] as $paymentData) {
                if ($paymentData['amount'] > 0) {
                    Payment::create([
                        'order_id' => $order->id,
                        'payment_method' => $paymentData['method'],
                        'transaction_reference' => null, // Handled manually for walk-in
                        'amount' => $paymentData['amount'],
                        'payment_status' => 'paid',
                    ]);
                }
            }

            // --- OPTIONAL: ASSIGN LOYALTY POINTS ---
            // If you have a user/loyalty system, you can add points here
            // $user = User::where('phone', $validated['customer_phone'])->first();
            // if($user) $user->increment('points', floor($totalAmount / 100));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "Order added successfully",
            ]);

            return redirect()->back();
        });
    }

    public function show(Order $order)
    {
        //
    }

    public function edit(Order $order)
    {
        $order->load('orderItems', 'payments');

        return inertia('app/orders/orders/Edit', [
            'order' => new OrderResource($order)
        ]);
    }

    public function update(Request $request, Order $order)
    {
        //
    }

    public function destroy(Order $order)
    {
        //
    }
}
