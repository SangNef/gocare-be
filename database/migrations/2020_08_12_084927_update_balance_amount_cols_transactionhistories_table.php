<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBalanceAmountColsTransactionhistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactionhistories', function (Blueprint $table) {
            $table->bigInteger('balance')->default(0)->change();
            $table->bigInteger('amount')->default(0)->change();
        });

        Schema::table('orders', function(Blueprint $table) {
            $table->dropColumn('transantion_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactionhistories', function (Blueprint $table) {
            $table->unsignedBigInteger('balance')->default(0)->change();
            $table->unsignedBigInteger('amount')->default(0)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('transantion_id')->default(0);
        });
    }
}
