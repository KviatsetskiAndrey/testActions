<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChangeStuctInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('supplier_uid');
            $table->dropColumn('buyer_uid');
            $table->string('supplier_id');
            $table->string('buyer_id');
            $table->uuid('user_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->uuid('supplier_uid');
        $table->uuid('buyer_uid');
        $table->dropColumn('supplier_id');
        $table->dropColumn('buyer_id');
        $table->dropColumn('user_uid');
    }
}
