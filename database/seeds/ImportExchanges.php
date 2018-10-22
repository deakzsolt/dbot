<?php

use Illuminate\Database\Seeder;
use App\Models\Exchanges;
use ccxt\Exchange;

class ImportExchanges extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Exchange::$exchanges as $exchange) {
            $addExchange = new Exchanges();
            $addExchange->exchange = $exchange;
            $addExchange->save();
        } // foreach
    }
}
