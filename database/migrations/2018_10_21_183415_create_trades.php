<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('exchange_id')->nullable();
            $table->string('symbol', 90)->nullable();
            $table->bigInteger('timestamp')->nullable();
            $table->string('strategy');
            $table->string('order');
            $table->string('status');
            $table->boolean('order_executed')->default(false);
            $table->float('price',10,0);
            $table->float('profit',10,0)->nullable();
            $table->float('percentage', 10, 0)->nullable();
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
        Schema::dropIfExists('trades');
    }
}
