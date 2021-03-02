<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLimits extends Migration
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

        $table =<<<SQL
CREATE TABLE `limits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_code` VARCHAR(16) NULL,
  `amount` DECIMAL(36,18) NULL,
  `name` VARCHAR(128) NOT NULL,
  `entity` VARCHAR(128) NOT NULL,
  `entity_id` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_entity_UNIQUE` (`name` ASC, `entity` ASC, `entity_id` ASC));
SQL;
        app("db")->getPdo()->exec($table);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('limits');
    }
}
