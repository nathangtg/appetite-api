<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('address');
            $table->string('image_path')->nullable();
            $table->integer('preparation_time');
            $table->enum('cuisine', [
                'malay',
                'chinese',
                'indian',
                'thai',
                'western',
                'indonesian',
                'japanese',
                'korean',
                'middle-eastern',
                'fusion',
                'other'
            ]);
            $table->string('price_range');
            $table->timestamps();
            $table->primary(['id', 'admin_id']);
        });

        DB::statement("ALTER TABLE restaurants ADD CONSTRAINT cuisines_enum_constraint CHECK (JSON_VALUE(cuisines, '$[*]') IN ('malay', 'chinese', 'indian', 'thai', 'western', 'indonesian', 'japanese', 'korean', 'middle-eastern', 'fusion', 'other'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
