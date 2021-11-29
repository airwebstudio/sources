<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeetingType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('type')->default('1:m')->after('description');
        });
        Schema::table('meeting_availabilities', function (Blueprint $table) {
            $table->string('type')->default('1:m')->after('description');
        });
        Schema::table('meeting_proposals', function (Blueprint $table) {
            $table->string('type')->default('1:m')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
        Schema::table('meeting_availabilities', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
        Schema::table('meeting_proposals', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
    }
}
