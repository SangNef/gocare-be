<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLockCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Module::generate("Lockcommissions", 'commissions', 'note', 'fa-cube', [
            ["customer_id", "Khách hàng", "Dropdown", false, "", 0, 0, true, "@customers"],
            ["amount", "Số tiền", "Integer", false, "0", 0, 11, false],
            ["order_id", "Đơn hàng", "Integer", false, "0", 0, 0, false],
            ["order_code", "Mã Đơn hàng", "String", false, "0", 0, 0, false],
            ["note", "Ghi chú", "Textarea", false, "", 0, 0, false],
            ["balance", "Số dư", "Integer", false, "0", 0, 11, false],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('lockcommissions')) {
            Schema::drop('lockcommissions');
        }
    }
}
