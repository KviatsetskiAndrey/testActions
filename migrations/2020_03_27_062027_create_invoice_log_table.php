<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('new');
            $table->integer('updated');
            $table->integer('error');
            $table->string('source');
            $table->uuid('user_uid');
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
        Schema::dropIfExists('invoice_log');
    }
}
