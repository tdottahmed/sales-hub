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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('external_id'); // supplier variation id
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->decimal('min_face_value', 10, 2)->nullable();
            $table->decimal('max_face_value', 10, 2)->nullable();
            $table->integer('count')->nullable();
            $table->decimal('min_price', 10, 2);
            $table->decimal('max_price', 10, 2);
            $table->string('currency_code', 5);
            $table->timestamp('modified_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
