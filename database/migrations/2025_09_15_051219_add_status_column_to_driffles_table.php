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
        Schema::table('driffle_offers', function (Blueprint $table) {
            $table->string('status')->default('disable')->after('product_variation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driffle_offers', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
