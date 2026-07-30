# Migrations

```php
orders {
    id();
    uuid()->unique();
    string('order_number');
    string('order_channel'); // walk_in, shop, tiktok, whatsapp, instagram
    string('order_status'); // pending, partially_paid, paid, cancelled

    string('discount_type')->nullable();
    string('discount_code')->nullable();
    decimal('discount', 10, 2)->default(0);

    decimal('subtotal', 10, 2)->default(0);
    decimal('delivery_cost', 10, 2)->default(0);
    decimal('tax_amount', 10, 2)->default(0);
    decimal('total_amount', 10, 2)->default(0); // subtotal + delivery
    decimal('amount_paid', 10, 2)->default(0);

    text('notes')->nullable();

    // Snapshots of customer info, delivery, pricing at order time (for when user gets anonymized incase of account deletion)
    // customer: name, email, phone
    json('customer_details_snapshot')->nullable();
    // delivery location, delivery area, delivery address, phone
    json('delivery_details_snapshot')->nullable();
    // subtotal, shipping, discount, tax, total
    json('pricing_snapshot')->nullable();
    // method, phone, transaction_id
    json('payment_snapshot')->nullable();

    string('customer_loyalty_id')->nullable();
    foreignId('loyalty_member_id')->nullable()->constrained()->nullOnDelete();
    foreignId('user_id')->nullable()->constrained()->nullOnDelete();

    timestamp('sold_at');
    timestamps();

    index(['customer_loyalty_id']);
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
    string('product_sku_snapshot');

    foreignId('product_id')->nullable()->constrained()->nullOnDelete();
    foreignId('order_id')->constrained()->cascadeOnDelete();
    timestamps();

    index(['order_id']);
}

order_deliveries {
    id();
    string('name');
    string('email');
    string('phone_number');
    string('address');
    string('location');
    string('area');
    string('additional_information')->nullable();
    decimal('shipping_cost', 10, 2)->default(0);
    string('delivery_status')->default('pending'); // pending, shipped, delivered

    foreignId('order_id')->constrained()->cascadeOnDelete();
    timestamps();
}

payments {
    id();
    string('payment_method'); // mpesa, paypal, paystack
    string('transaction_reference');
    string('checkout_request_id');
    string('merchant_request_id');
    string('response_code');
    text('response_description');
    text('customer_message');
    decimal('amount', 10, 2);
    string('payment_status');

    foreignId('order_id')->constrained()->cascadeOnDelete();
    timestamps();

    index('transaction_reference');
}
```
