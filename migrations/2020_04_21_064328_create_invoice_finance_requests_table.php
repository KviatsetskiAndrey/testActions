<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceFinanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_finance_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->nullable(false);
            $table->string('supplier_uid');
            $table->string('funder_uid');
            $table->decimal('invoice_amount', 36, 18);
            $table->decimal('invoice_discount', 36, 18);
            $table->decimal('transaction_amount', 36, 18);
            $table->timestamp('expire_at')->nullable(true);
            $table->timestamps();
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
        Schema::dropIfExists('invoice_finance_requests');
    }
}
