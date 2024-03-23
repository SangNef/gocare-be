<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProductSeriIdColWarrantyOrderProductSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warranty_order_product_series', function (Blueprint $table) {
            $table->integer('product_seri_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warranty_order_product_series', function (Blueprint $table) {
            $table->integer('product_seri_id')->nullable(false)->change();
        });
    }
}
