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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('payment_method'); // mpesa, paypal, paystack
            $table->string('transaction_reference');
            $table->string('checkout_request_id');
            $table->string('merchant_request_id');
            $table->string('response_code');
            $table->text('response_description');
            $table->text('customer_message');
            $table->decimal('amount', 10, 2);
            $table->string('payment_status');

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('transaction_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
