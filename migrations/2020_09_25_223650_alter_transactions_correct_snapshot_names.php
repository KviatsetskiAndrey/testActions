<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionsCorrectSnapshotNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `transactions` 
CHANGE COLUMN `show_amount` `show_amount` DECIMAL(36,18) NULL DEFAULT NULL AFTER `amount`,
CHANGE COLUMN `show_balance_snapshot` `show_available_balance_snapshot` DECIMAL(36,18) NULL DEFAULT NULL AFTER `available_balance_snapshot`,
CHANGE COLUMN `balance_snapshot` `available_balance_snapshot` DECIMAL(36,18) NULL DEFAULT NULL ,
ADD COLUMN `current_balance_snapshot` DECIMAL(36,18) NULL AFTER `show_available_balance_snapshot`,
ADD COLUMN `show_current_balance_snapshot` DECIMAL(36,18) NULL AFTER `current_balance_snapshot`;
SQL;

        try {
            app("db")->getPdo()->exec($sql);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
