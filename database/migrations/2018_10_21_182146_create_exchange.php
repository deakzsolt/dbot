<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchanges', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('exchange');
            $table->string('slug');
            $table->boolean('ccxt')->nullable()->default(0);
            $table->integer('use')->nullable()->default(0);
            $table->string('url')->nullable();
            $table->text('url_api')->nullable();
            $table->text('url_doc')->nullable();
            $table->string('version')->nullable();
            $table->boolean('has_ticker')->default(0);
            $table->boolean('has_ohlcv')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchanges');
    }
}
