<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Dwij\Laraadmin\Models\Module;

class CreateBacklogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backlogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->smallInteger('debt_type');
            $table->unsignedBigInteger('money_in')->default(0);
            $table->unsignedBigInteger('money_out')->default(0);
            $table->unsignedBigInteger('debt')->default(0);
            $table->unsignedBigInteger('total')->default(0);
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
        Schema::drop('backlogs');
    }
}
