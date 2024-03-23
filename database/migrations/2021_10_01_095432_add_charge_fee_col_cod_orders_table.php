<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChargeFeeColCodOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cod_orders', function (Blueprint $table) {
            $table->boolean('charge_fee')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cod_orders', function (Blueprint $table) {
            $table->dropColumn('charge_fee');
        });
    }
}
