<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('customer_id');
            $table->string('unit');
            $table->bigInteger('price_per_package');
            $table->integer('total_package');
            $table->bigInteger('total');
            $table->bigInteger('transport_price');
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
        Schema::drop('transport_orders');
    }
}
