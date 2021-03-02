<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->bigIncrements('id');
            $table->string('invoice_id');
            $table->uuid('buyer_uid');
            $table->string('company_name');
            $table->uuid('supplier_uid');
            $table->timestamp('invoice_date')->nullable(true);
            $table->boolean('credit_memo');
            $table->timestamp('memo_date')->nullable(true);
            $table->string('currency');
            $table->timestamp('maturity_date')->nullable(true);
            $table->decimal('amount', 36, 18);
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
        Schema::dropIfExists('invoices');
    }
}
