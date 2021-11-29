<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PayoutQueueItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payout_queue_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_user_id');
            $table->integer('amount');
            $table->json('data')->nullable();
            $table->json('error_data')->nullable();

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
        Schema::dropIfExists('payout_queue_item');
    }
}
