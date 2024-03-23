<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStockHistoryToProductproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produceproducts', function (Blueprint $table) {
            $table->text('stock_history')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produceproducts', function (Blueprint $table) {
            $table->dropColumn('stock_history');
        });
    }
}
