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
        Schema::create('purchasings', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->nullable()->unique();
            $table->string('product');
            $table->integer('quantity');
            $table->bigInteger('price');
            $table->enum('status', [
                'Pending',
                'Approved',
                'Paid',
                'Shipped',
                'Received',
                'Completed',
                'Canceled',
                'Refunded',
                'Deleted'
            ]);
            $table->string('payment_proof')->nullable();
            $table->string('payment_proof_desc')->nullable();
            $table->string('received_proof')->nullable();
            $table->string('received_proof_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasings');
    }
};