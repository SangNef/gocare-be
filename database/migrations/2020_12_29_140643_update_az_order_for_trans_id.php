<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAzOrderForTransId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('az_orders', function (Blueprint $table) {
            $table->string('request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('az_orders', function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
    }
}
