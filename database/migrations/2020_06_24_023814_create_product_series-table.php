<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_series', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('order_id')->nullable();
            $table->string('seri_number')->unique();
            $table->integer('qr_code_status')->default(0);
            $table->integer('stock_status')->default(0);
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
        Schema::drop('product_series');
    }
}
