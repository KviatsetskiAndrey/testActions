<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToInvoiceAmountLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_amount_log', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
        });

        Schema::table('invoice_amount_log', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable(false);
            $table->foreign('invoice_id')->references('id')->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_amount_log', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
        });

        Schema::table('invoice_amount_log', function (Blueprint $table) {
            $table->string('invoice_id');
        });
    }
}
