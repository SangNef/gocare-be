<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreIdForTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
    }
}
