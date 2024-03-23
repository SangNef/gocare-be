<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAddressColsCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('address');
            $table->dropColumn('province');
            $table->dropColumn('district');
            $table->dropColumn('ward');
        });
    }
}
