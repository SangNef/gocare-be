<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoucherIdToDOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_orders', function (Blueprint $table) {
            $table->integer('voucher_id')->nullable()->default(0);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('voucher_id')->nullable()->default(0);
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
            $table->dropColumn('voucher_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('voucher_id');
        });
    }
}
