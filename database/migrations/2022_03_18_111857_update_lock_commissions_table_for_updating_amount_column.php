<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLockCommissionsTableForUpdatingAmountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lockcommissions', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
        Schema::table('lockcommissions', function (Blueprint $table) {
            $table->integer('amount')->unsigned(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
