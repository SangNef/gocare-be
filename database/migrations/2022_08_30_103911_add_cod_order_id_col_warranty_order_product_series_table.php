<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodOrderIdColWarrantyOrderProductSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warranty_order_product_series', function (Blueprint $table) {
            $table->integer('cod_order_id')->nullable();
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
            $table->dropColumn('cod_order_id');
        });
    }
}
