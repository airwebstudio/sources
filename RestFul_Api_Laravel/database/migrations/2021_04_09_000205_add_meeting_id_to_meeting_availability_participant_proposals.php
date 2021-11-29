<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeetingIdToMeetingAvailabilityParticipantProposals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_availability_participants', function (Blueprint $table) {
            $table->foreignId('meeting_id')->nullable()->after('meeting_availability_id')->constrained();
            $table->unsignedBigInteger('meeting_availability_id')->nullable()->change();
        });

        Schema::table('meeting_availability_participant_proposals', function (Blueprint $table) {
            $table->dropForeign('m_a_p_p_a_id_foreign');
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('meeting_availability_participant_proposals');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_availability_participants', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
            $table->dropColumn(['meeting_id']);
            $table->unsignedBigInteger('meeting_availability_id')->nullable(false)->change();
        });

        Schema::create('meeting_availability_participant_proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_availability_id');
            $table->foreign('meeting_availability_id', 'm_a_p_p_a_id_foreign')->references('id')->on('meeting_availabilities');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }
}
