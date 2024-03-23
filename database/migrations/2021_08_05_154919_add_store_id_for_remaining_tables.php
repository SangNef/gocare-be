<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreIdForRemainingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('store_id')->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
    }
}
