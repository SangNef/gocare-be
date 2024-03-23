<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index([
                'deleted_at',
                'email',
                'debt_total',
            ], 'customers_email_deleted_at_debt_total_indexes');
            $table->index([
                'address',
                'province',
                'district',
                'ward',
            ], 'customers_address_province_district_ward_indexes');
            $table->index([
                'store_id',
                'group_id'
            ], 'customers_store_id_group_id_indexes');
        });

        Schema::table('customer_backlogs', function (Blueprint $table) {
            $table->index([
                'customer_id',
                'debt_type'
            ], 'customer_backlogs_indexes');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index([
                'deleted_at',
                'created_at',
            ], 'orders_deleted_at_created_at_indexes');
            $table->index([
                'customer_id',
                'store_id',
            ], 'orders_customer_id_store_id_indexes');
            $table->index([
                'type',
                'total',
                'status',
                'sub_type'
            ], 'orders_type_total_status_sub_type_indexes');
            $table->index([
                'code',
                'payment_method',
                'cod_partner',
                'total'
            ], 'orders_code_payment_method_cod_partner_total_indexes');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index([
                'deleted_at',
                'type',
                'status'
            ], 'transactions_deleted_at_type_indexes');
            $table->index([
                'order_id',
                'bank_id',
                'customer_id',
                'store_id'
            ], 'transactions_order_id_bank_id_customer_id_store_id_indexes');
            $table->index([
                'received_amount',
                'transfered_amount',
            ], 'transactions_received_amount_transfered_amount_indexes');
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
            $table->dropIndex('customers_email_deleted_at_debt_total_indexes');
            $table->dropIndex('customers_address_province_district_ward_indexes');
            $table->dropIndex('customers_store_id_group_id_indexes');
        });
        Schema::table('customer_backlogs', function (Blueprint $table) {
            $table->dropIndex('customer_backlogs_indexes');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_deleted_at_created_at_indexes');
            $table->dropIndex('orders_customer_id_store_id_indexes');
            $table->dropIndex('orders_type_total_status_sub_type_indexes');
            $table->dropIndex('orders_code_payment_method_cod_partner_total_indexes');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_deleted_at_type_indexes');
            $table->dropIndex('transactions_order_id_bank_id_customer_id_store_id_indexes');
            $table->dropIndex('transactions_received_amount_transfered_amount_indexes');
        });
    }
}
