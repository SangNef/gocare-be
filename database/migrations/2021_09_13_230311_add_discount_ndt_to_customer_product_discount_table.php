<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountNdtToCustomerProductDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_product_discount', function (Blueprint $table) {
            $table->decimal('discount_ndt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_product_discount', function (Blueprint $table) {
            $table->dropColumn('discount_ndt');
        });
    }
}
