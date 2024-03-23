<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUseAtFeColProductcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productcategories', function (Blueprint $table) {
            $table->tinyInteger('use_at_fe')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productcategories', function (Blueprint $table) {
            $table->dropColumn('use_at_fe');
        });
    }
}
