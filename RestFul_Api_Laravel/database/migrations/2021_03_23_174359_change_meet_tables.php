<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMeetTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meets', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn(['seller_id']);
            $table->foreignId('user_id')->after('hash')->constrained();
            $table->string('name')->after('user_id');
        });

        Schema::table('meet_participants', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropColumn(['seller_id', 'buyer_id']);
            $table->foreignId('user_id')->after('meet_id')->constrained();
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
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);
            $table->dropColumn(['name']);
            $table->foreignId('seller_id')->after('hash')->constrained();
        });

        Schema::table('meet_participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);
            $table->foreignId('seller_id')->after('meet_id')->constrained();
            $table->unsignedBigInteger('buyer_id')->after('seller_id');
            $table->foreign('buyer_id')->references('id')->on('users');
        });
    }
}
