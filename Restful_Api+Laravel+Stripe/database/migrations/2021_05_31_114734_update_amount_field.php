<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAmountField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buying_queue_item', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });

        Schema::table('stripe_balance_transaction', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
            $table->decimal('fee', 7, 2)->default(0)->change();
        });
        
        Schema::table('payout_queue_item', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });

        Schema::table('wallet_transaction', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });

        Schema::table('wallet_reserve_transaction', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });

        Schema::table('transaction_queue_items', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
