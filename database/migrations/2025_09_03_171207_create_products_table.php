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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('internal_id')->unique();
            $table->string('name');
            $table->string('country_code', 5)->nullable();
            $table->string('currency_code', 5)->nullable();
            $table->text('description')->nullable();
            $table->text('disclaimer')->nullable();
            $table->text('redemption_instructions')->nullable();
            $table->longText('terms')->nullable();
            $table->string('logo_url')->nullable();
            $table->timestamp('modified_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
