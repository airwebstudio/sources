<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeetingsIdToStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storage', function (Blueprint $table) {
            $table->string('meeting_hash')->nullable()->after('user_id');
            $table->foreign('meeting_hash')->references('hash')->on('meetings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storage', function (Blueprint $table) {
            $table->dropForeign(['meeting_hash']);
            $table->dropColumn(['meeting_hash']);
        });
    }
}
