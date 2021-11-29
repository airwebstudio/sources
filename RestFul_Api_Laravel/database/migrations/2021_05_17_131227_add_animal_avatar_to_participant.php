<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnimalAvatarToParticipant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->string('avatar_animal_name')->after('user_id')->nullable();
            $table->string('avatar_animal_color')->after('user_id')->nullable();
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
            $table->dropColumn('avatar_animal_name');
            $table->dropColumn('avatar_animal_color');
        });
    }
}
