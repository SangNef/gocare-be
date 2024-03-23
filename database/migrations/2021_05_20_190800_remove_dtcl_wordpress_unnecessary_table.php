<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDtclWordpressUnnecessaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('partner_product_attributes');
        Schema::dropIfExists('partner_product_categories');
        Schema::dropIfExists('partner_customer_attributes');
        Schema::dropIfExists('partner_order_attributes');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('partner_order_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('partner_order_id');
            $table->string('meta_data')->nullable();
            $table->timestamps();
        });
        Schema::create('partner_customer_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->integer('partner_customer_id');
            $table->string('meta_data')->nullable();
            $table->timestamps();
        });
        Schema::create('partner_product_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('partner_product_id');
            $table->string('meta_data')->nullable();
            $table->timestamps();
        });
        Schema::create('partner_product_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->integer('partner_category_id');
            $table->string('meta_data')->nullable();
            $table->timestamps();
        });
    }
}
