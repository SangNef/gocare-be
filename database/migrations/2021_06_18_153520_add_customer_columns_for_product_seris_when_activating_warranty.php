<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerColumnsForProductSerisWhenActivatingWarranty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->dropColumn(['activated_at', 'expired_at', 'name', 'phone', 'email', 'province', 'district', 'ward', 'address']);
        });
    }
}
