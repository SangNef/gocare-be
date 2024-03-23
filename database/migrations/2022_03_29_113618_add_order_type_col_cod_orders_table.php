<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderTypeColCodOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cod_orders', function (Blueprint $table) {
            $table->string('order_type')->default('Order');
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
            $table->dropColumn('order_type');
        });
    }
}
