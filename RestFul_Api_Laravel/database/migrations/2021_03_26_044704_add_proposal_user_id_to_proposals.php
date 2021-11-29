<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddProposalUserIdToProposals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DELETE FROM meet_participants');
        DB::statement('DELETE FROM meet_proposal_participants');
        DB::statement('DELETE FROM meet_proposals');
        DB::statement('DELETE FROM meets');

        Schema::table('meet_proposals', function (Blueprint $table) {
            $table->bigInteger('proposal_user_id')->unsigned();

            $table->foreign('proposal_user_id')->references('id')->on('users');
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
            $table->dropColumn(['proposal_user_id']);
        });
    }
}
