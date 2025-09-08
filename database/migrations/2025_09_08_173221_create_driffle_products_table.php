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
        Schema::create('driffle_products', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('product_id')->unique();
            $table->string('title');
            $table->string('platform');
            $table->string('regions');
            $table->longText('product_data');
            $table->boolean('is_mapped')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driffle_products');
    }
};
