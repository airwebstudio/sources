<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingAttchmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_attchments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('meeting_hash');
            $table->foreign('meeting_hash')->references('hash')->on('meetings');
            $table->unsignedBigInteger('storage_id');
            $table->foreign('storage_id')->references('id')->on('storage');
            $table->timestamps();
        });
        Schema::table('storage', function (Blueprint $table) {
            $table->dropForeign(['meeting_hash']);
            $table->dropColumn(['meeting_hash']);
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
            $table->string('meeting_hash')->nullable()->after('user_id');
            $table->foreign('meeting_hash')->references('hash')->on('meetings');
        });
        Schema::dropIfExists('meeting_attchments');
    }
}
