<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSelfCodServiceColumnForOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_orders', function (Blueprint $table) {
            $table->boolean('self_cod_service')->default(0);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('self_cod_service')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('d_orders', function (Blueprint $table) {
            $table->dropColumn('self_cod_service');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('self_cod_service');
        });
    }
}
