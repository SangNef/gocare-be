<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCustomerBacklogCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_backlogs', function (Blueprint $table) {
            $table->dropColumn('total');
            $table->bigInteger('money_in')->default(0)->change();
            $table->bigInteger('money_out')->default(0)->change();
            $table->bigInteger('debt')->default(0)->change();
            $table->bigInteger('has')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_backlogs', function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('money_in')->default(0)->change();
            $table->unsignedBigInteger('money_out')->default(0)->change();
            $table->unsignedBigInteger('debt')->default(0)->change();
            $table->dropColumn('has');
        });
    }
}
