<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveBhExchangesId23 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'tickers',
            function (Blueprint $table) {
                $table->dropUnique('bh_exchanges_id_23');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'tickers',
            function (Blueprint $table) {
                $table->unique(['exchange_id', 'symbol', 'timestamp'], 'bh_exchanges_id_23');
            }
        );
    }
}
