<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->string('description');
            $table->dateTime('starting_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->integer('duration')->nullable();
            $table->timestamps();
        });
        Schema::create('meet_proposal_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meet_id')->nullable()->constrained();
            $table->foreignId('meet_proposal_id')->nullable()->constrained();
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
        Schema::table('meet_proposals', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('meet_proposal_participants', function (Blueprint $table) {
            $table->dropForeign(['meet_id']);
            $table->dropForeign(['meet_proposal_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('meet_proposals');
        Schema::dropIfExists('meet_proposal_participants');
    }
}
