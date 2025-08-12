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
            $table->string('so_number')->nullable()->unique()->after('id');
            $table->string('product');
            $table->integer('quantity');
            $table->bigInteger('price');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_address');
            $table->enum('status', ['Pending', 'Approved', 'Paid', 'Shipped', 'Received', 'Completed', 'Canceled', 'Refunded', 'Deleted']);
            $table->string('payment_proof_so')->nullable();
            $table->string('payment_proof_desc')->nullable();
            $table->string('shipping_proof_so')->nullable();
            $table->string('shipping_proof_desc')->nullable();
            $table->string('received_proof_so')->nullable();
            $table->string('received_proof_desc')->nullable();
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