<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('seller_id');
            $table->foreign('seller_id')->references('id')->on('users');
            $table->integer('rate');
            $table->timestamps();
        });
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('rate')->nullable()->after('participants_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seller_rates', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('seller_rates');
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['rate']);
        });

    }
}
