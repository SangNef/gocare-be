<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_warranties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('seri_number');
            $table->string('name');
            $table->string('phone');
            $table->string('address');
            $table->string('province');
            $table->string('district');
            $table->string('ward');
            $table->string('product_name');
            $table->text('content');
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
        Schema::drop('request_warranties');
    }
}
