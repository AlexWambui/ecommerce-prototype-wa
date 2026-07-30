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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('returned_quantity')->default(0); // for in case a shoe gets returned

            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);

            $table->decimal('discount', 10, 2)->default(0); // discount applied to THIS specific item
            $table->string('discount_type')->nullable(); // 'bulk', 'promo', 'clearance', 'manual'

            $table->string('product_name_snapshot');
            $table->string('product_sku_snapshot');

            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
