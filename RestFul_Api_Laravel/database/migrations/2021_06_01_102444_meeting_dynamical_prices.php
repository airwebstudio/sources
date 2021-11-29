<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MeetingDynamicalPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_price_1_1_fixed', function (Blueprint $table) {
            $table->decimal('price', 7, 2)->default(0)->change();
        });

        Schema::table('meeting_price_1_1_per_min', function (Blueprint $table) {
            $table->decimal('price', 7, 2)->default(0)->change();
        });


        Schema::table('meeting_price_1_m_fixed', function (Blueprint $table) {
            $table->boolean('is_dynamic_price')->default(false);
            $table->decimal('price', 7, 2)->nullable()->change();
        });


        Schema::table('meeting_price_1_m_per_min', function (Blueprint $table) {
            $table->boolean('is_dynamic_price')->default(false);
            $table->decimal('price', 7, 2)->nullable()->change();
        });

        Schema::create('meeting_dynamical_prices', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_hash');
            $table->integer('count')->default(0);
            $table->decimal('price', 7, 2)->default(0);
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
        Schema::dropIfExists('meeting_dynamical_prices');
    }
}
