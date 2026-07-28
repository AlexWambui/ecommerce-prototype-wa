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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('product_name_snapshot');
            $table->string('product_sku_snapshot');
            $table->string('product_size_snapshot');
            $table->string('product_color_snapshot');

            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_method')->default('mpesa'); // mpesa, cash, bank_transfer
            $table->string('payment_status')->default('pending'); // pending, paid, cancelled

            $table->string('sale_channel')->default('shop'); // 'whatsapp', 'walk_in', 'instagram', 'tiktok'
            $table->timestamp('sold_at');

            $table->string('customer_loyalty_id')->nullable()->index();

            $table->foreignId('loyalty_member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
