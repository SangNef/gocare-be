<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDraftOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE d_orders LIKE orders;');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE d_orderproducts LIKE orderproducts;');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE d_transactions LIKE transactions;');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE d_transport_orders LIKE transport_orders;');
        \Illuminate\Support\Facades\DB::statement('CREATE TABLE d_transport_order_products LIKE transport_order_products;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('d_orders');
        Schema::drop('d_orderproducts');
        Schema::drop('d_transactions');
        Schema::drop('d_transport_orders');
        Schema::drop('d_transport_order_products');
    }
}
