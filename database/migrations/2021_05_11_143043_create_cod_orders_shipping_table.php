<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodOrdersShippingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cod_orders_shipping', function (Blueprint $table) {
            $table->increments('id');
            $table->string('partner');
            $table->integer('type');
            $table->integer('status');
            $table->integer('handle_type');
            $table->text('bill_data');
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
        Schema::drop('cod_orders_shipping');
    }
}
