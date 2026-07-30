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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status'); // pendind, partially_paid, paid, shipped, delivered, cancelled
            $table->text('notes')->nullable(); // admin notes about the specific transition
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who change it
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->timestamp('changed_at'); // when it happened
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
