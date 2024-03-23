<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDebtColsCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->bigInteger('debt_in_advance')->default(0)->change();
            $table->bigInteger('debt_total')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('debt_in_advance')->default(0)->change();
            $table->unsignedBigInteger('debt_total')->default(0)->change();
        });
    }
}
