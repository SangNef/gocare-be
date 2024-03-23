<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentMethodBillLadingTypeOrderSeriesTypeColsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('payment_method')->default(1);
            $table->string('cod_partner')->nullable();
            $table->integer('order_series_type')->nullable();
            $table->integer('paid')->default(0);
            $table->integer('unpaid')->default(0);
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
            $table->dropColumn(['payment_method', 'cod_partner', 'order_series_type', 'paid', 'unpaid']);
        });
    }
}
