<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCurrentDebtColumnInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table) {
            $table->dropColumn(['current_debt', 'paid', 'unpaid']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('current_debt', 19, 2)->default(0);
            $table->decimal('paid', 19, 2)->default(0);
            $table->decimal('unpaid', 19, 2)->default(0);
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
            $table->dropColumn(['current_debt', 'paid', 'unpaid']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->float('current_debt')->default(0)->change();
            $table->integer('paid')->default(0)->change();
            $table->integer('unpaid')->default(0)->change();
        });
    }
}
