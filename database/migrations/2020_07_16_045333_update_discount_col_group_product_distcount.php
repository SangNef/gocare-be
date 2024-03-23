<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDiscountColGroupProductDistcount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_product_discounts', function (Blueprint $table) {
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
        Schema::table('group_product_discounts', function (Blueprint $table) {
            $table->float('discount')->change();
        });
    }
}
