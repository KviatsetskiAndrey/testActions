<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRequestsAddRateDesignation extends Migration
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

        Schema::table('requests', function (Blueprint $table) {
            $table
                ->enum('rate_designation', ['base/reference', 'reference/base'])
                ->after('rate');
            $table
                ->decimal('input_amount', 36, 18)
                ->nullable()
                ->after('amount');
        });

        $updateOWT = <<<SQL
     UPDATE `requests` `r`
         INNER JOIN `request_data_owt` `data` ON `data`.`request_id` = `r`.`id`
         INNER JOIN `accounts` `a` ON `a`.`id` = `data`.`source_account_id`
         INNER JOIN `beneficiary_customers` `bc` ON `bc`.`id` = `data`.`beneficiary_customer_id`
     	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
         LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1
     SET  `r`.`input` = JSON_OBJECT(
         "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
         "sourceAccountId", `data`.`source_account_id`,
         "sourceAccountNumber", `a`.`number`,
         "revenueAccountId", `ra`.`id`,
         "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100,
         "refMessage", `data`.`ref_message`,
         "beneficiaryCustomerAccountName", `bc`.`account_name`
     ),
         `r`.`input_amount` = IFNULL(CAST(JSON_EXTRACT(metadata, '$."ConvertOutgoingAmount.OriginRequestedAmount"') as decimal(36,18)), `r`.`amount`),
         `r`.`rate_designation` = 'reference/base'
         WHERE `subject` = 'OWT'
SQL;

        $updateCA = <<<SQL
     UPDATE `requests` r
     INNER JOIN `request_data_ca` `data` ON `data`.`request_id` = `r`.`id`

     SET `input` = JSON_OBJECT(
         "destinationAccountId", `data`.`destination_account_id`,
         "debitFromRevenueAccount", `data`.`debit_from_revenue_account` IS TRUE,
         "applyIwtFee", `data`.`apply_iwt_fee` IS TRUE
     )
     WHERE `r`.`subject` = "CA"
SQL;

        $updateTBA = <<<SQL
     UPDATE `requests` r
     INNER JOIN `request_data_tba` `data` ON `data`.`request_id` = `r`.`id`
     INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`
     INNER JOIN `accounts` `dest` ON `dest`.`id` = `data`.`destination_account_id`
 	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
     LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1

     SET `input` = JSON_OBJECT(
         "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
         "sourceAccountId", `source`.`id`,
         "destinationAccountId", `dest`.`id`,
         "sourceAccountNumber", `source`.`number`,
         "destinationAccountNumber", `dest`.`number`,
         "revenueAccountId", `ra`.`id`,
         "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100
     )
     WHERE `r`.`subject` = "TBA"
SQL;

        $updateTBU = <<<SQL
     UPDATE `requests` r
     INNER JOIN `request_data_tbu` `data` ON `data`.`request_id` = `r`.`id`
     INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`
     INNER JOIN `accounts` `dest` ON `dest`.`id` = `data`.`destination_account_id`
 	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
     LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1

     SET `input` = JSON_OBJECT(
         "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
         "sourceAccountId", `source`.`id`,
         "destinationAccountId", `dest`.`id`,
         "sourceAccountNumber", `source`.`number`,
         "destinationAccountNumber", `dest`.`number`,
         "revenueAccountId", `ra`.`id`,
         "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100
     )
     WHERE `r`.`subject` = "TBU"
SQL;

        $updateTBUTransactions = <<<SQL
     UPDATE `transactions` `t`
            inner join `requests` `r` on `r`.`id` = `t`.`request_id`
            SET `t`.`purpose` = "revenue_tbu_transfer"
            WHERE `r`.`subject` = "TBU" AND `t`.`purpose` = "revenue_tba_transfer"
SQL;

        $updateCFT = <<<SQL
     UPDATE `requests` r
     INNER JOIN `request_data_cft` `data` ON `data`.`request_id` = `r`.`id`
     INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`
     INNER JOIN `cards` `dest` ON `dest`.`id` = `data`.`destination_card_id`
 	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
     LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1

     SET `input` = JSON_OBJECT(
         "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
         "sourceAccountId", `source`.`id`,
         "destinationCardId", `dest`.`id`,
         "sourceAccountNumber", `source`.`number`,
         "destinationCardNumber", `dest`.`number`,
         "revenueAccountId", `ra`.`id`,
         "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100
     )
     WHERE `r`.`subject` = "CFT"
SQL;

        $updateDA = <<<SQL
     UPDATE `requests` r
     INNER JOIN `request_data_da` `data` ON `data`.`request_id` = `r`.`id`
     INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`

     SET `input` = JSON_OBJECT(
         "sourceAccountId", `source`.`id`,
         "sourceAccountNumber", `source`.`number`,
         "revenueAccountId", `data`.`revenue_account_id`,
         "creditToRevenueAccount", `data`.`credit_to_revenue_account` IS TRUE
     )
     WHERE `r`.`subject` = "DA"
SQL;

        $updateDRA = <<<SQL
     UPDATE `requests` r
     INNER JOIN `request_data_dra` `data` ON `data`.`request_id` = `r`.`id`

     SET `input` = JSON_OBJECT(
         "revenueAccountId", `data`.`revenue_account_id`
     )
     WHERE `r`.`subject` = "DRA"
SQL;


        $updateAllExceptOWT = "UPDATE `requests` SET `rate_designation` = 'base/reference' WHERE `subject` != 'OWT'";
        $queries = [
            $updateOWT,
            $updateCA,
            $updateTBA,
            $updateTBU,
            $updateTBUTransactions,
            $updateCFT,
            $updateDA,
            $updateDRA,
            $updateAllExceptOWT,
        ];
        $updateOWT = <<<SQL
                       UPDATE `requests` `r`
                           INNER JOIN `request_data_owt` `data` ON `data`.`request_id` = `r`.`id`
                           INNER JOIN `accounts` `a` ON `a`.`id` = `data`.`source_account_id`
                           INNER JOIN `beneficiary_customers` `bc` ON `bc`.`id` = `data`.`beneficiary_customer_id`
                       	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
                           LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1
                       SET  `r`.`input` = JSON_OBJECT(
                           "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
                           "sourceAccountId", `data`.`source_account_id`,
                           "sourceAccountNumber", `a`.`number`,
                           "revenueAccountId", `ra`.`id`,
                           "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100,
                           "refMessage", `data`.`ref_message`,
                           "beneficiaryCustomerAccountName", `bc`.`account_name`
                       ),
                           `r`.`input_amount` = IFNULL(CAST(JSON_EXTRACT(metadata, '$."ConvertOutgoingAmount.OriginRequestedAmount"') as decimal(36,18)), `r`.`amount`),
                           `r`.`rate_designation` = 'reference/base'
                           WHERE `subject` = 'OWT'
SQL;

        $updateCA = <<<SQL
                       UPDATE `requests` r
                       INNER JOIN `request_data_ca` `data` ON `data`.`request_id` = `r`.`id`

                       SET `input` = JSON_OBJECT(
                           "destinationAccountId", `data`.`destination_account_id`,
                           "debitFromRevenueAccount", `data`.`debit_from_revenue_account` IS TRUE,
                           "applyIwtFee", `data`.`apply_iwt_fee` IS TRUE
                       )
                       WHERE `r`.`subject` = "CA"
SQL;

        $updateTBA = <<<SQL
                       UPDATE `requests` r
                       INNER JOIN `request_data_tba` `data` ON `data`.`request_id` = `r`.`id`
                       INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`
                       INNER JOIN `accounts` `dest` ON `dest`.`id` = `data`.`destination_account_id`
                   	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
                       LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1

                       SET `input` = JSON_OBJECT(
                           "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
                           "sourceAccountId", `source`.`id`,
                           "destinationAccountId", `dest`.`id`,
                           "sourceAccountNumber", `source`.`number`,
                           "destinationAccountNumber", `dest`.`number`,
                           "revenueAccountId", `ra`.`id`,
                           "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100
                       )
                       WHERE `r`.`subject` = "TBA"
SQL;

        $updateTBU = <<<SQL
                       UPDATE `requests` r
                       INNER JOIN `request_data_tbu` `data` ON `data`.`request_id` = `r`.`id`
                       INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`
                       INNER JOIN `accounts` `dest` ON `dest`.`id` = `data`.`destination_account_id`
                   	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
                       LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1

                       SET `input` = JSON_OBJECT(
                           "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
                           "sourceAccountId", `source`.`id`,
                           "destinationAccountId", `dest`.`id`,
                           "sourceAccountNumber", `source`.`number`,
                           "destinationAccountNumber", `dest`.`number`,
                           "revenueAccountId", `ra`.`id`,
                           "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100
                       )
                       WHERE `r`.`subject` = "TBU"
SQL;

        $updateTBUTransactions = <<<SQL
                       UPDATE `transactions` `t`
                              inner join `requests` `r` on `r`.`id` = `t`.`request_id`
                              SET `t`.`purpose` = "revenue_tbu_transfer"
                              WHERE `r`.`subject` = "TBU" AND `t`.`purpose` = "revenue_tba_transfer"
SQL;

        $updateCFT = <<<SQL
                       UPDATE `requests` r
                       INNER JOIN `request_data_cft` `data` ON `data`.`request_id` = `r`.`id`
                       INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`
                       INNER JOIN `cards` `dest` ON `dest`.`id` = `data`.`destination_card_id`
                   	LEFT JOIN `transactions` `exchangetx` ON `exchangetx`.`request_id` = `r`.`id` AND `exchangetx`.`purpose` = "fee_exchange_margin"
                       LEFT JOIN `revenue_accounts` `ra` ON `ra`.`currency_code` = `r`.`base_currency_code` AND `ra`.`is_default` = 1

                       SET `input` = JSON_OBJECT(
                           "transferFeeParams", JSON_EXTRACT(metadata, '$._feeParameters'),
                           "sourceAccountId", `source`.`id`,
                           "destinationCardId", `dest`.`id`,
                           "sourceAccountNumber", `source`.`number`,
                           "destinationCardNumber", `dest`.`number`,
                           "revenueAccountId", `ra`.`id`,
                           "exchangeMarginPercent", ABS(ROUND(`exchangetx`.`amount` / `r`.`amount`, 5)) * 100
                       )
                       WHERE `r`.`subject` = "CFT"
SQL;

        $updateDA = <<<SQL
                       UPDATE `requests` r
                       INNER JOIN `request_data_da` `data` ON `data`.`request_id` = `r`.`id`
                       INNER JOIN `accounts` `source` ON `source`.`id` = `data`.`source_account_id`

                       SET `input` = JSON_OBJECT(
                           "sourceAccountId", `source`.`id`,
                           "sourceAccountNumber", `source`.`number`,
                           "revenueAccountId", `data`.`revenue_account_id`,
                           "creditToRevenueAccount", `data`.`credit_to_revenue_account` IS TRUE
                       )
                       WHERE `r`.`subject` = "DA"
SQL;

        $updateDRA = <<<SQL
                       UPDATE `requests` r
                       INNER JOIN `request_data_dra` `data` ON `data`.`request_id` = `r`.`id`

                       SET `input` = JSON_OBJECT(
                           "revenueAccountId", `data`.`revenue_account_id`
                       )
                       WHERE `r`.`subject` = "DRA"
SQL;


        $updateAllExceptOWT = "UPDATE `requests` SET `rate_designation` = 'base/reference' WHERE `subject` != 'OWT'";
        $queries = [
            $updateOWT,
            $updateCA,
            $updateTBA,
            $updateTBU,
            $updateTBUTransactions,
            $updateCFT,
            $updateDA,
            $updateDRA,
            $updateAllExceptOWT,
        ];
        try {
            foreach ($queries as $sql) {
                app("db")->getPdo()->exec($sql);
            }
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
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('rate_designation');
            $table->dropColumn('input_amount');
        });
    }
}
