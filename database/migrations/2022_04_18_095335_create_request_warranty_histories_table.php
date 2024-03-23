<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestWarrantyHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_warranty_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_warranty_id');
            $table->text('detail');
            $table->integer('handler_id');
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
        Schema::drop('request_warranty_histories');
    }
}
