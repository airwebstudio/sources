<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuyingQueueItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buying_queue_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_account_id');
            $table->foreignId('seller_account_id');
            $table->string('meeting_hash');
            $table->timestamp('expired_date')->nullable();
            $table->json('data')->nullable();
            $table->json('error_data')->nullable();
            $table->integer('amount');
            $table->string('status')->default('Process');
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
        Schema::dropIfExists('buying_queue_item');
    }
}
