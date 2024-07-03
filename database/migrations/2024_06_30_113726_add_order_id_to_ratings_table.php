<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable();

            // Foreign key constraint
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Drop foreign key first if exists
            $table->dropForeign(['order_id']);

            // Then drop the column
            $table->dropColumn('order_id');
        });
    }
};
