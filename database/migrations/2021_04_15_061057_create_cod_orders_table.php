<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cod_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->string('store_id');
            $table->string('order_code');
            $table->string('partner');
            $table->integer('quantity');
            $table->bigInteger('cod_amount');
            $table->bigInteger('fee_amount');
            $table->bigInteger('package_price');
            $table->integer('so_id')->nullable(); 
            $table->integer('compare_status')->default(0);
            $table->integer('charge_method')->default(1);
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
        Schema::table('cod_orders', function (Blueprint $table) {
            //
        });
    }
}
