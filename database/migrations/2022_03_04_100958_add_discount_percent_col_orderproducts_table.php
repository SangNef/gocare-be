<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountPercentColOrderproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderproducts', function (Blueprint $table) {
            $table->integer('discount_percent')->default(0);
        });
        Schema::table('d_orderproducts', function (Blueprint $table) {
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
        Schema::table('orderproducts', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
        Schema::table('d_orderproducts', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
}
