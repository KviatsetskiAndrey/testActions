<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultAccountTypeEur extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $types = DB::select('SELECT * FROM account_types WHERE currency_code = "EUR" LIMIT 1');
        if (!empty($types)) {
            return;
        }

        DB::table('account_types')->insert([
            'name' => 'Default EUR account type',
            'currency_code' => "EUR",
            'auto_number_generation' => 1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
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

