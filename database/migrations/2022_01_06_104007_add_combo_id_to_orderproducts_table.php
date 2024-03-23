<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComboIdToOrderproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderproducts', function (Blueprint $table) {
            $table->integer('combo_id')->nullable();
            $table->integer('parent_id')->default(0);
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
            $table->dropColumn('combo_id');
            $table->dropColumn('parent_id');
        });
    }
}
