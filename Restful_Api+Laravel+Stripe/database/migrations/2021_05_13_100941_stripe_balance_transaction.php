<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StripeBalanceTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_balance_transaction', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['charge', 'payout'])->default('charge');
            $table->foreignId('internal_user_id')->nullable();
            $table->string('balance_transaction_id')->nullable();
            $table->string('status')->default('availible');
            $table->integer('amount');
            $table->integer('fee')->default(0);
            $table->timestamp('available_on')->useCurrent();

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
        Schema::dropIfExists('stripe_balance_transaction');
    }
}
