<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerRevenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_revenues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('month');
            $table->text('data');
            $table->string('status');
            $table->string('accepted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('customer_revenues')) {
            Schema::drop('customer_revenues');
        }
    }
}
