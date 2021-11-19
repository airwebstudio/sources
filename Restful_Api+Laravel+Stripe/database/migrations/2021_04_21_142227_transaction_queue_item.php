<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TransactionQueueItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_queue_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Payment', 'Subscription', 'Refund'])->default('Payment');
            $table->string('description')->nullable();
            $table->decimal('amount', 5, 2);
            $table->string('currency')->default('usd');
            $table->foreignId('internal_user_id');
            $table->json('card_data');
            $table->json('payment_data')->nullable();
            $table->json('source_data')->nullable();
            $table->json('error_data')->nullable();
            $table->enum('status', ['Process', 'Pending', 'AuthDone', 'Done', 'Fail'])->default('Process');
            $table->timestamp('created_at')->useCurrent();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_queue_items');
    }
}
