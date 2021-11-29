<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameMeetsToMeetings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_participants', function (Blueprint $table) {
            $table->dropForeign(['meet_id']);
        });
        Schema::table('meet_proposal_participants', function (Blueprint $table) {
            $table->dropForeign(['meet_id']);
            $table->dropForeign(['meet_proposal_id']);
        });

        Schema::rename('meets', 'meetings');
        Schema::rename('meet_participants', 'meeting_participants');

        Schema::rename('meet_proposals', 'meeting_proposals');
        Schema::rename('meet_proposal_participants', 'meeting_proposal_participants');

        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->renameColumn('meet_id','meeting_id');
            $table->foreign('meeting_id')->references('id')->on('meetings');
        });
        Schema::table('meeting_proposal_participants', function (Blueprint $table) {
            $table->renameColumn('meet_id','meeting_id');
            $table->renameColumn('meet_proposal_id','meeting_proposal_id');
            $table->foreign('meeting_id')->references('id')->on('meetings');
            $table->foreign('meeting_proposal_id')->references('id')->on('meeting_proposals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
        });
        Schema::table('meeting_proposal_participants', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
            $table->dropForeign(['meeting_proposal_id']);
        });

        Schema::rename('meetings','meets');
        Schema::rename('meeting_participants', 'meet_participants');

        Schema::rename('meeting_proposals', 'meet_proposals');
        Schema::rename('meeting_proposal_participants', 'meet_proposal_participants');

        Schema::table('meet_participants', function (Blueprint $table) {
            $table->renameColumn('meeting_id','meet_id');
            $table->foreign('meet_id')->references('id')->on('meets');
        });
        Schema::table('meet_proposal_participants', function (Blueprint $table) {
            $table->renameColumn('meeting_id','meet_id');
            $table->renameColumn('meeting_proposal_id','meet_proposal_id');
            $table->foreign('meet_id')->references('id')->on('meets');
            $table->foreign('meeting_proposal_id')->references('id')->on('meet_proposals');
        });
    }
}
