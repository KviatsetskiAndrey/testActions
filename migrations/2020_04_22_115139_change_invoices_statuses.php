<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeInvoicesStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['buyer_pending_review', 'active', 'supplier_pending_review', 'supplier_pending_confirmation', 'funder_pending_review', 'funder_pending_confirmation', 'active_traded', 'paid_settled', 'overdue', 'cancelled'])->default('buyer_pending_review')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['buyer_pending_review', 'buyer_active', 'buyer_cancelled', 'buyer_stale', 'buyer_active_traded', 'buyer_paid', 'buyer_overdue', 'supplier_active', 'supplier_pending_review', 'supplier_awaiting_funder_response', 'supplier_accepted_traded', 'supplier_settled', 'supplier_overdue', 'supplier_stale', 'funder_pending_review', 'funder_pending_confirmation', 'funder_active', 'funder_settled', 'funder_overdue'])->default('buyer_pending_review')->nullable(false);
        });
    }
}
