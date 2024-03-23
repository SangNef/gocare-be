<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsOrderedAtToProductSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->datetime('ordered_at')->nullable();
            $table->datetime('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->dropColumn('ordered_at');
            $table->dropColumn('completed_at');
        });
    }
}
