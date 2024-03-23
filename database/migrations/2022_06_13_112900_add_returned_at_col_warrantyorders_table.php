<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnedAtColWarrantyordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warrantyorders', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warrantyorders', function (Blueprint $table) {
            $table->dropColumn('returned_at');
        });
    }
}
