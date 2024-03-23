<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusUserIdGroupIdColsRequestWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_warranties', function (Blueprint $table) {
            $table->integer('status')->default(0);
            $table->integer('user_id')->nullable();
            $table->integer('group_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_warranties', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('user_id');
            $table->dropColumn('group_id');
        });
    }
}
