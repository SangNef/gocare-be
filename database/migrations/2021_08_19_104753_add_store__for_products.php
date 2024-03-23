<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreForProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });

        Schema::create('store_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('store_id');
            $table->integer('n_quantity');
            $table->integer('w_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });

        Schema::drop('store_products');
    }
}
