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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');

            $table->string('category'); // Marketing, Packaging, Stock Purchase, Lights, Rent

            $table->string('payment_method')->nullable(); // cash, mpesa, bank_transfer, cheque
            $table->string('reference_number')->nullable(); // transaction id, cheque number
            $table->string('bank_account')->nullable(); // account used
            $table->string('paid_to')->nullable(); // vendor, employee, service provider

            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_frequency')->nullable(); // montly, quarterly, yearly
            $table->date('recurrence_end_date')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->string('status')->default('pending'); // approved, paid, rejected

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who created it

            $table->timestamps();

            $table->index(['expense_date', 'category']);
            $table->index(['category', 'status']);
            $table->index(['expense_date', 'status']);
            $table->index(['department']);
            $table->index(['paid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
