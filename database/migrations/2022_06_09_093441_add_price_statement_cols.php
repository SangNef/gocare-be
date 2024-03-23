<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceStatementCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_orders', function (Blueprint $table) {
            $table->decimal('cod_price_statement', 19, 2)->default(0);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('cod_price_statement', 19, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('d_orders', function (Blueprint $table) {
            $table->dropColumn('cod_price_statement');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cod_price_statement');
        });
    }
}
