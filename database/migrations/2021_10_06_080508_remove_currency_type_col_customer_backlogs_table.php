<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCurrencyTypeColCustomerBacklogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_backlogs', function (Blueprint $table) {
            $table->dropColumn('currency_type');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('debt_in_advance_ndt');
            $table->dropColumn('debt_total_ndt');
        });
        Schema::table('customer_product_discount', function (Blueprint $table) {
            $table->dropColumn('discount_ndt');
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
            $table->decimal('currency_type');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('debt_in_advance_ndt');
            $table->decimal('debt_total_ndt');
        });
        Schema::table('customer_product_discount', function (Blueprint $table) {
            $table->decimal('discount_ndt');
        });
    }
}
