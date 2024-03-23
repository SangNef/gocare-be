<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_order_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transport_order_id');
            $table->integer('product_id');
            $table->integer('quantity')->default(0);
            $table->integer('packages')->default(0);
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('length', 5, 2)->default(0);
            $table->decimal('height', 5, 2)->default(0);
            $table->decimal('width', 5, 2)->default(0);
            $table->decimal('price', 19, 2)->default(0);
            $table->decimal('total', 19, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transport_order_products');
    }
}
