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
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('SET NULL'); // For registered users, nullable for guest checkout later
            $table->string('customer_name');
            $table->string('customer_email');
            // Shipping Address
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_postal_code');
            $table->string('shipping_country');
            // Billing Address (can be added similarly if needed, for now shipping is primary)
            // $table->string('billing_address_line1')->nullable();
            // ...
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // e.g., pending, processing, paid, shipped, delivered, cancelled
            $table->timestamps();
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
