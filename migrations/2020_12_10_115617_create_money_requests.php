<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoneyRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('target_user_id')->index();
            $table->string('initiator_user_id')->index();
            $table->string('status');
            $table->boolean('is_new')->default(false);
            $table->unsignedBigInteger('recipient_account_id');
            $table->unsignedBigInteger('request_id')->nullable(true);
            $table->decimal('amount', 36, 18);
            $table->string('currency_code');
            $table->string('description');
            $table->timestamps();

            $table->foreign('recipient_account_id')->references('id')->on('accounts')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('request_id')->references('id')->on('requests')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('money_requests');
    }
}
