<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->longText('description');
            $table->string('price');
            $table->string('payment_system');
            $table->json('credentials');
            $table->timestamps();
        });

/*        DB::table('streamers')->insert(
            [
                'credentials' => json_encode([
                    'client_id' => 'AbJwlwjyyklaR9K9mzJf6q2mYo91IPIPiqVqMFi9iBxeLCutHKiuUDJVOkIn1qsjlCskcxID8Q0Xx0NT',
                    'secret' => 'EK8SJHhc-An5Ni_CmqRVlmrZz7wg4YSNqUDKOugsTk7kc6bGJrfySCcMoIlaigryyrRQO3nGSsr6aAg0',
                ]),
                'payment_system' => 'PayPal'
            ]
        );*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('sellers');
    }
}
