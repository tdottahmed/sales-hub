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
        Schema::create('map_driffle_products', function (Blueprint $table) {
            $table->id();
            $table->string('driffle_product_id')->unique();
            $table->string('product_id')->unique();
            $table->string('name')->nullable();
            $table->string('platform')->nullable();
            $table->string('regions')->nullable();
            $table->boolean('is_offer_created')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_driffle_products');
    }
};
