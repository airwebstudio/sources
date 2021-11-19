<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DescBuyQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buying_queue_item', function (Blueprint $table) {
            $table->string('description')->nullable();
        });

        Schema::table('wallet_transaction', function (Blueprint $table) {
            $table->integer('reservation_id')->nullable();
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
