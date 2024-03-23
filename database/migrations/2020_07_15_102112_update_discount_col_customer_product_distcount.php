<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDiscountColCustomerProductDistcount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_product_discount', function (Blueprint $table) {
            $table->integer('discount')->change();
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
            $table->float('discount')->change();
        });
    }
}
