<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MeetingPrice1M extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_price_1_m_fixed', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_hash');
            $table->foreignId('seller_account_id');
            $table->integer('price')->default(0);
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
        Schema::dropIfExists('meeting_price_1_m_fixed');
    }
}
