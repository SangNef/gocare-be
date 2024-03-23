<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttrsIdsColumnsForOrderproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderproducts', function (Blueprint $table) {
            $table->string('attr_ids')->nullable();
            $table->string('attr_texts')->nullable();
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
            $table->dropColumn('attr_ids');
            $table->dropColumn('attr_texts');
        });
    }
}
