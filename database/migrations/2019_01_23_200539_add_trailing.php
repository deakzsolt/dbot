<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrailing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('trailing', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('trade_id')->references('id')->on('trades')->onDelete('cascade');
			$table->string('state');
			$table->integer('trailing');
			$table->float('fix_sell', 10, 0)->nullable();
			$table->float('difference', 10, 0)->nullable();
			$table->float('profit', 10, 0)->nullable();
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
		Schema::dropIfExists('trailing');
    }
}
