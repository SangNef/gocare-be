<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProductComboGroupsTableToAddDiscountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_combo_groups', function (Blueprint $table) {
            $table->integer('discount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_combo_groups', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
}
