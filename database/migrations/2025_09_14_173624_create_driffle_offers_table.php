<?php

use App\Models\ProductVariation;
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
        Schema::create('driffle_offers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('offer_id')->unique();
            $table->bigInteger('driffle_product_id')->unique();
            $table->double('your_price')->nullable();
            $table->double('retail_price')->nullable();
            $table->double('final_selling_price')->nullable();
            $table->integer('available_stock')->nullable();
            $table->foreignIdFor(ProductVariation::class)->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driffle_offers');
    }
};
