<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankBacklogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_backlogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id');
            $table->smallInteger('debt_type');
            $table->unsignedBigInteger('money_in')->default(0);
            $table->unsignedBigInteger('money_out')->default(0);
            $table->unsignedBigInteger('fee')->default(0);
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
        Schema::drop('bank_backlogs');
    }
}
