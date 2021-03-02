<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConvertRequestSubject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL
INSERT INTO `request_subjects`
(`subject`,
`description`)
VALUES
('CONVERT',
'Convert');
SQL;
        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = <<<SQL
DELETE FROM `request_subjects`
WHERE subject = 'CONVERT';
SQL;
        DB::connection()->getPdo()->exec($sql);
    }
}
