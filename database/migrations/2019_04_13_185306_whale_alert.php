<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WhaleAlert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('alerts', function (Blueprint $table) {
			$table->increments('id');
			$table->longText('cursor');
			$table->string('blockchain');
			$table->string('symbol');
			$table->string('transaction_id');
			$table->string('transaction_type');
			$table->longText('hash');
			$table->longText('from_address');
			$table->longText('from_owner_type');
			$table->longText('from_owner')->nullable();
			$table->longText('to_address');
			$table->longText('to_owner_type');
			$table->longText('to_owner')->nullable();
			$table->bigInteger('timestamp');
			$table->float('amount',10,0);
			$table->float('amount_usd',10,0);
			$table->integer('transaction_count');
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
		Schema::dropIfExists('alerts');
    }
}
