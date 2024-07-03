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
        // Drop the rating column from the restaurants table
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add the rating column to the restaurants table
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->nullable()->after('price_range');
        });
    }
};
