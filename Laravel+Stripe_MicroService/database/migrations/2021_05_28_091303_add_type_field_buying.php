<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeFieldBuying extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buying_queue_item', function (Blueprint $table) {
            $table->string('type')->default('charge')->after('meeting_hash');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buying_queue_item', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        

    }
}
