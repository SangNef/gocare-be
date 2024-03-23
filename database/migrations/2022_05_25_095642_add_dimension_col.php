<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDimensionCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_orderproducts', function (Blueprint $table) {
            $table->string('dimension')->nullable();
        });
        Schema::table('orderproducts', function (Blueprint $table) {
            $table->string('dimension')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('d_orderproducts', function (Blueprint $table) {
            $table->dropColumn('dimension');
        });
        Schema::table('orderproducts', function (Blueprint $table) {
            $table->dropColumn('dimension');
        });
    }
}
