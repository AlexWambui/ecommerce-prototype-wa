<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('order_number');
            $table->string('order_channel'); // walk_in, shop, tiktok, whatsapp, instagram, website
            $table->string('order_status'); // pending, partially_paid, paid, cancelled
            $table->string('discount_type')->nullable();
            $table->string('discount_code')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0); // subtotal + delivery
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('delivery_location')->nullable();
            $table->string('delivery_area')->nullable();
            $table->string('delivery_address')->nullable();
            // Snapshots for when user gets anonymized incase of account deletion
            $table->json('customer_details_snapshot')->nullable(); // name, email, phone
            $table->json('delivery_details_snapshot')->nullable(); // location, area, address
            $table->json('pricing_snapshot')->nullable(); // subtotal, shipping, discount, tax, total
            $table->json('payment_snapshot')->nullable(); // method, phone, transaction_id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('sold_at');
            $table->timestamps();
            $table->index(['order_status']);
            $table->index(['sold_at']);
            $table->index(['customer_phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
