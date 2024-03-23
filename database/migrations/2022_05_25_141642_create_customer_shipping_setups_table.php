<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerShippingSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_shipping_setups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('partner');
            $table->string('connection');
            $table->string('inventory')->nullable();
            $table->tinyInteger('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('customer_shipping_setups');
    }
}
