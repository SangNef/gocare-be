<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarrantyOrderProductSeries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warranty_order_product_series', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warranty_order_product_id');
            $table->integer('product_seri_id');
            $table->integer('status')->default(1);
            $table->integer('error_type');
            $table->text('note')->default('');
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
        Schema::drop('warranty_order_product_series');
    }
}
