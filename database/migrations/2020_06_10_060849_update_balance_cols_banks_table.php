<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBalanceColsBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->bigInteger('first_balance')->default(0)->change();
            $table->bigInteger('last_balance')->default(0)->change();
        });
        
        Schema::table('bank_backlogs', function (Blueprint $table) {
            $table->bigInteger('money_in')->default(0)->change();
            $table->bigInteger('money_out')->default(0)->change();
            $table->bigInteger('fee')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->unsignedBigInteger('first_balance')->default(0)->change();
            $table->unsignedBigInteger('last_balance')->default(0)->change();
        });
        
        Schema::table('bank_backlogs', function (Blueprint $table) {
            $table->unsignedBigInteger('money_in')->default(0)->change();
            $table->unsignedBigInteger('money_out')->default(0)->change();
            $table->unsignedBigInteger('fee')->default(0)->change();
        });
    }
}
