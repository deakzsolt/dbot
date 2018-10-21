<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOhlcvsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ohlcvs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exchange_id')->nullable();
            $table->string('symbol', 90)->nullable();
            $table->bigInteger('timestamp')->nullable();
            $table->dateTime('datetime')->nullable()->index('datetime_ohlcvs');
            $table->float('open', 10, 0)->nullable();
            $table->float('high', 10, 0)->nullable();
            $table->float('low', 10, 0)->nullable();
            $table->float('close', 10, 0)->nullable();
            $table->float('volume', 10, 0)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['bh_exchanges_id','symbol','timestamp'], 'bh_exchanges_id_ohlcvs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ohlcvs');
    }
}
