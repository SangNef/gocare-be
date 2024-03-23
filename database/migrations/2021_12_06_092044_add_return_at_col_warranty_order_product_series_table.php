<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnAtColWarrantyOrderProductSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warranty_order_product_series', function (Blueprint $table) {
            $table->timestamp('return_at')->nullable();
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
            $table->dropColumn('return_at');
        });
    }
}
