<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentsColRequestWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_warranties', function (Blueprint $table) {
            $table->string('attachments')->default(json_encode([]));
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
            $table->dropColumn('attactments');
        });
    }
}
