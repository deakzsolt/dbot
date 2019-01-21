<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesAddAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('trades', function (Blueprint $table) {
			$table->float('trade',10,0)->nullable()->after('price');
			$table->float('amount',10,0)->nullable()->after('trade');
			$table->longText('order_id')->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('trades', function (Blueprint $table) {
			$table->dropColumn('trade');
			$table->dropColumn('amount');
			$table->integer('order_id')->change();
		});
    }
}
