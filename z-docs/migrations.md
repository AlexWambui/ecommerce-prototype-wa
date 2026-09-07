# Migrations

```php
orders {
    id();
    uuid()->unique();
    string('order_number');
    string('order_channel'); // walk_in, shop, tiktok, whatsapp, instagram
    string('order_status'); // current state: pending, processing, shipped, delivered, cancelled
    // Financials
    string('discount_type')->nullable();
    string('discount_code')->nullable();
    decimal('discount', 10, 2)->default(0);
    decimal('subtotal', 10, 2)->default(0);
    decimal('delivery_cost', 10, 2)->default(0);
    decimal('tax_amount', 10, 2)->default(0);
    decimal('total_cost_price', 10, 2)->default(0);
    decimal('total_selling_price', 10, 2)->default(0); // subtotal + delivery
    decimal('amount_paid', 10, 2)->default(0);
    text('notes')->nullable();
    // delivery details
    string('customer_name')->nullable();
    string('customer_phone')->nullable();
    string('customer_email')->nullable();
    string('delivery_method')->default('shop');
    string('delivery_location')->nullable();
    string('delivery_area')->nullable();
    string('delivery_address')->nullable();
    // Snapshots for when user gets anonymized incase of account deletion
    json('customer_details_snapshot')->nullable(); // name, email, phone
    json('delivery_details_snapshot')->nullable(); // location, area, address
    json('pricing_snapshot')->nullable(); // subtotal, shipping, discount, tax, total
    json('payment_snapshot')->nullable(); // method, phone, transaction_id
    foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    timestamp('sold_at');
    timestamps();
    index(['order_status']);
    index(['sold_at']);
    index(['customer_phone']);
}

order_items {
    id();
    string('name');
    unsignedSmallInteger('quantity')->default(1);
    unsignedSmallInteger('returned_quantity')->default(0); // for in case a shoe gets returned
    decimal('cost_price', 10, 2);
    decimal('selling_price', 10, 2);
    decimal('discount', 10, 2)->default(0); // discount applied to THIS specific item
    string('discount_type')->nullable(); // 'bulk', 'promo', 'clearance', 'manual'
    string('product_name_snapshot');
    string('product_sku_snapshot')->nullable();
    foreignId('product_id')->nullable()->constrained()->nullOnDelete();
    foreignId('order_id')->constrained()->cascadeOnDelete();
    timestamps();
    index(['order_id']);
}

order_statuses {
    id();
    string('status'); // pendind, partially_paid, paid, shipped, delivered, cancelled
    text('notes')->nullable(); // admin notes about the specific transition
    foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who change it
    foreignId('order_id')->constrained()->cascadeOnDelete();
    timestamp('changed_at'); // when it happened
    timestamps();
}

payments {
    id();
    uuid()->unique();
    string('payment_method'); // mpesa, paypal, paystack
    string('transaction_reference')->nullable();
    string('checkout_request_id')->nullable();
    string('merchant_request_id')->nullable();
    string('response_code')->nullable();
    text('response_description')->nullable();
    text('customer_message')->nullable();
    decimal('amount', 10, 2)->nullable();
    string('payment_status')->nullable();
    foreignId('order_id')->constrained()->cascadeOnDelete();
    timestamps();
    index('transaction_reference');
}

expenses {
    id();
    uuid()->unique();
    string('title');
    text('description')->nullable();
    decimal('amount', 10, 2);
    date('expense_date');

    string('category'); // Marketing, Packaging, Stock Purchase, Lights, Rent

    string('payment_method')->nullable(); // cash, mpesa, bank_transfer, cheque
    string('reference_number')->nullable(); // transaction id, cheque number
    string('bank_account')->nullable(); // account used
    string('paid_to')->nullable(); // vendor, employee, service provider

    boolean('is_recurring')->default(false);
    string('recurrence_frequency')->nullable(); // montly, quarterly, yearly
    date('recurrence_end_date')->nullable();

    foreignId('approved_by')->nullable()->constrained()->nullOnDelete();
    timestamp('approved_at')->nullable();
    text('approval_notes')->nullable();

    string('status')->default('pending'); // approved, paid, rejected

    foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who created it

    timestamps();

    index(['expense_date', 'category']);
    index(['category', 'status']);
    index(['expense_date', 'status']);
    index(['department']);
    index(['paid_to']);
}
```

## Guidelines

1. Frictionless way to onboard customers (username maybe).
1. How to handle walk in customers.
1. How to handle split payments (e.g., cash + mpesa).
1. How to label shoes after adding them to the system (for easier client and admin identification). Maybe use the id or at least generate the SKU automatically.
1. Add chat on whatsapp button to each product or add to cart, then checkout and redirecto to chart on whatsapp to make saving orders easier.
