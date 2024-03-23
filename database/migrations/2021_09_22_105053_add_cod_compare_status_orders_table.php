<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodCompareStatusOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('vtp_fee_compare_status');
            $table->dropColumn('current_total_debt');
            $table->dropColumn('bill_lading_type');
            $table->string('cod_compare_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('vtp_fee_compare_status');
            $table->decimal('current_total_debt');
            $table->integer('bill_lading_type');
            $table->dropColumn('cod_compare_status');
        });
    }
}
