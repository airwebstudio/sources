<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Meet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meets', function (Blueprint $table) {
            $table->id();
            $table->uuid('hash')->unique();
            $table->foreignId('seller_id')->constrained();
            $table->string('description');
            $table->dateTime('starting_at');
            $table->dateTime('finished_at');
            $table->integer('duration');
            $table->timestamps();
        });

        Schema::create('meet_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meet_id')->constrained();
            $table->foreignId('seller_id')->constrained();
            $table->unsignedBigInteger('buyer_id');
            $table->foreign('buyer_id')->references('id')->on('users');
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
        Schema::table('meets', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
        });
        Schema::table('meet_participants', function (Blueprint $table) {
            $table->dropForeign(['meet_id']);
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
        });
        Schema::dropIfExists('meets');
        Schema::dropIfExists('meet_participants');
    }
}
