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
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_order_id')->unique(); // maps to _id from supplier
            $table->uuid('card_id')->nullable();
            $table->string('code')->nullable();
            $table->string('pin')->nullable();
            $table->date('date')->nullable();
            $table->string('status')->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('crypto_value', 16, 8)->nullable();
            $table->string('order_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_orders');
    }
};
