<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountPercentColGroupProductDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_product_discounts', function (Blueprint $table) {
            $table->integer('discount_percent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_product_discounts', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
}
