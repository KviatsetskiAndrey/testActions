<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndicesRelatedToLimits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()
            ->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');


        $indexTransactions =<<<SQL
    ALTER TABLE `transactions`
    ADD INDEX `account_id_status` (`account_id` ASC, `status` ASC);
SQL;

        $indexAccounts =<<<SQL
    ALTER TABLE `accounts`
    ADD INDEX `user_id` (`user_id` ASC);
SQL;
        foreach ([$indexTransactions, $indexAccounts] as $sql) {
            app("db")->getPdo()->exec($sql);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function ($table) {
            $table->dropIndex('account_id_status');
        });

        Schema::table('accounts', function ($table) {
            $table->dropIndex('user_id');
        });
    }
}
