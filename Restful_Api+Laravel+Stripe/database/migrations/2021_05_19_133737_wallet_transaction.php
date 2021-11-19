<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WalletTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transaction', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_account_id');
            $table->foreignId('buyer_account_id');
            $table->string('meeting_hash');
            $table->integer('amount');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('wallet_transaction');
    }
}
