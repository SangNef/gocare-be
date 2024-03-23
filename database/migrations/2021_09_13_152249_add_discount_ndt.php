<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountNdt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_product_discounts', function (Blueprint $table) {
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
        Schema::table('group_product_discounts', function (Blueprint $table) {
            $table->dropColumn('discount_ndt');
        });
    }
}
