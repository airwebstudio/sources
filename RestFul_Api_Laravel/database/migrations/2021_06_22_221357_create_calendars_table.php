<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->default('1:m');
            $table->dateTime('starting_at');
            $table->integer('duration')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->timestamps();
        });
        Schema::create('calendar_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->foreign('calendar_id', 'c_p_id_foreign')->references('id')->on('calendars');
            $table->foreignId('user_id')->constrained();
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
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('calendar_participants', function (Blueprint $table) {
            $table->dropForeign('c_p_id_foreign');
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('calendars');
        Schema::dropIfExists('calendar_participants');
    }
}
