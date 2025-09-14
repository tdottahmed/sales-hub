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
        Schema::table('similar_products', function (Blueprint $table) {
            $table->boolean('is_created_offer')->default(false)->after('score');
            $table->string('source')->default('driffle')->after('is_created_offer'); // TODO: change default value to conditionally. eg: driffle or kinguin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('similar_products', function (Blueprint $table) {
            $table->dropColumn('is_created_offer');
            $table->dropColumn('source');
        });
    }
};
