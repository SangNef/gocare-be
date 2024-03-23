<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePriceInNdtColProductsTable extends Migration
{
    private $ndtCols = [
        'customers' => [
            'debt_in_advance', 'debt_total'
        ],
        'products' => [
            'price_in_ndt'
        ],
        'orderproducts' => [
            'price', 'total'
        ],
        'customer_backlogs' => [
            'money_in', 'money_out', 'has', 'debt'
        ],
        'transactions' => [
            'received_amount', 'transfered_amount', 'fee', 'bank_history'
        ],
        'transport_orders' => [
            'price_per_package', 'transport_price', 'total'
        ],
        'banks' => [
            'first_balance', 'last_balance'
        ],
        'bank_backlogs' => [
            'money_in', 'money_out', 'fee'
        ],
        'orders' => [
            'total', 'subtotal', 'fee', 'discount'
        ],
        'customer_product_discount' => [
            'discount'
        ]
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->ndtCols as $table => $cols) {
            foreach ($cols as $col) {
                Schema::table($table, function (Blueprint $table) use ($col) {
                    $table->decimal($col, 19, 2)->default(0)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->ndtCols as $table => $cols) {
            foreach ($cols as $col) {
                Schema::table($table, function (Blueprint $table) use ($col) {
                    $table->bigInteger($col)->default(0)->change();
                });
            }
        }
    }
}
