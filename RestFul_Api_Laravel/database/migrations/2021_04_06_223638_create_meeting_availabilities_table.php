<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->string('description')->nullable();
            $table->dateTime('starting_at');
            $table->integer('duration')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->timestamps();
        });
        Schema::create('meeting_availability_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_availability_id');
            $table->foreign('meeting_availability_id', 'm_a_p_a_id_foreign')->references('id')->on('meeting_availabilities');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
        Schema::create('meeting_availability_participant_proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_availability_id');
            $table->foreign('meeting_availability_id', 'm_a_p_p_a_id_foreign')->references('id')->on('meeting_availabilities');
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
        Schema::table('meeting_availabilities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('meeting_availability_participants', function (Blueprint $table) {
            $table->dropForeign('m_a_p_a_id_foreign');
            $table->dropForeign(['user_id']);
        });
        Schema::table('meeting_availability_participant_proposals', function (Blueprint $table) {
            $table->dropForeign('m_a_p_p_a_id_foreign');
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('meeting_availabilities');
        Schema::dropIfExists('meeting_availability_participants');
        Schema::dropIfExists('meeting_availability_participant_proposals');
    }
}
