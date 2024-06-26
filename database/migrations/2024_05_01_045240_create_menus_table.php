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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->enum('category', [
                'main_course',
                'side_dish',
                'appetizer',
                'salad',
                'dessert',
                'drink',
                'beverage',
                'snack',
                'breakfast',
            ]);
            $table->text('description');
            $table->string('image');
            $table->decimal('price', 8, 2);
            // $table->bigInteger('stock_quantity')->default(0);
            $table->enum('display', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
