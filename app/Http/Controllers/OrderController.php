<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Products\ProductPOSResource;
use App\Http\Requests\Orders\OrderRequest;

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

    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            // --- CALCULATE TOTALS ---
            $subtotal = collect($validated['cart_items'])->sum(fn($item) => $item['price']);
            $total_amount = $subtotal + $validated['delivery_cost'];

            // Calculate total paid from payments
            $total_paid = collect($validated['payments'])->sum('amount');

            // Determine payment status
            $payment_status = $total_paid <= 0 ? 'pending' : ($total_paid >= $total_amount ? 'paid' : 'partially_paid');

            // Set the initial order status based on delivery method
            $order_status = $validated['delivery_method'] === 'delivery' ? Order::STATUS_PENDING : Order::STATUS_PROCESSING;

            // delivery details
            $delivery_location = $validated['delivery_method'] === 'shop' ? 'shop' : $validated['location'];
            $delivery_area = $validated['delivery_method'] === 'shop' ? 'shop' : $validated['area'];
            $delivery_address = $validated['delivery_method'] === 'shop' ? 'shop' : $validated['address'];

            // --- CREATE THE ORDER ---
            $order = Order::create([
                'order_number' => 'Ord_' . strtoupper(Str::random(6)) . '_' . now()->format('ymd'),
                'order_channel' => $validated['order_channel'],
                'order_status' => $order_status,
                
                'subtotal' => $subtotal,
                'delivery_cost' => $validated['delivery_cost'],
                'tax_amount' => 0,
                'total_amount' => $total_amount,
                'amount_paid' => $total_paid,

                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],

                'delivery_method' => $validated['delivery_method'],
                'delivery_location' => $delivery_location,
                'delivery_area' => $delivery_area,
                'delivery_address' => $delivery_address,
                
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
                    'total' => $total_amount,
                    'balance' => $total_amount - $total_paid,
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
                'status' => $order_status,
                'notes' => 'Order created via ' . $validated['order_channel'] . ' | Payment: ' . $payment_status,
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

            // If fully paid and delivery, update order status to processing
            if ($payment_status === 'paid' && $validated['delivery_method'] === 'delivery') {
                $order->update(['order_status' => Order::STATUS_PROCESSING]);

                OrderStatus::create([
                    'order_id' => $order->id,
                    'status' => Order::STATUS_PROCESSING,
                    'notes' => 'Order fully paid, ready for processing',
                    'user_id' => Auth::id(),
                    'changed_at' => now(),
                ]);
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
        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {

            $order->update([
                'order_status' => $validated['order_status'],
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Order updated successfully"
            ]);

            return to_route('orders.index');

        } catch (\Throwable $e) {
            DB::rollBack();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Order failed to update: {$e->getMessage()}"
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Order $order)
    {
        //
    }
}
