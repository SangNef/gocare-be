<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAttributesValueMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attributes_value_media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->string('attribute_value_ids');
            $table->string('attribute_value_texts');
            $table->string('media_ids');
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
        Schema::drop('product_attributes_value_media');
    }
}
