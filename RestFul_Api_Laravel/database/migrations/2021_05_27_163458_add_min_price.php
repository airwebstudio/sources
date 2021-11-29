<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_price_1_1_per_min', function (Blueprint $table) {
            $table->bigInteger('min_price')->default(0)->after('id');
        });

        Schema::table('meeting_price_1_m_per_min', function (Blueprint $table) {
            $table->bigInteger('min_price')->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_price_1_1_per_min', function (Blueprint $table) {
            $table->dropColumn(['min_price']);
        });
        Schema::table('meeting_price_1_m_per_min', function (Blueprint $table) {
            $table->dropColumn(['min_price']);
        });
    }
}
