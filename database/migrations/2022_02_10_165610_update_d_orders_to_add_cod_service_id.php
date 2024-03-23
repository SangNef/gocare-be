<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDOrdersToAddCodServiceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_orders', function (Blueprint $table) {
            $table->string('cod_service_id')->nullable();
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
            $table->dropColumn('cod_service_id');
        });
    }
}
