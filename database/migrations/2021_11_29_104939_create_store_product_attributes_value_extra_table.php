<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreProductAttributesValueExtraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_product_attributes_value_extra', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('store_id');
            $table->string('attribute_value_ids');
            $table->string('attribute_value_texts');
            $table->string('n_quantity');
            $table->string('w_quantity');
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
        Schema::drop('store_product_attributes_value_extra');
    }
}
