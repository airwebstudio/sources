<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BlockMinutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->integer('block_minutes_counter')->default(0);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->integer('block_minutes')->default(1)->nullable();
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
            $table->dropColumn('block_minutes_counter');
        });
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('block_minutes');
        });
    }
}
