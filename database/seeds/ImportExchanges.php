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
//        TODO activate one exchange
        foreach (Exchange::$exchanges as $exchange) {
            $className = '\ccxt\\' . $exchange;
            $class = new $className;
            $describe = $class->describe();
            
            $url = serialize($describe['urls']['www']);
            $api = serialize($describe['urls']['api']);
            $docs = serialize($describe['urls']['doc']);

            $addExchange = new Exchanges();
            $addExchange->exchange = $describe['name'];
            $addExchange->slug = $describe['id'];
            $addExchange->ccxt = true;
            $addExchange->url = $url;
            $addExchange->url_api = $api;
            $addExchange->url_doc = $docs;
            $addExchange->version = isset($describe['version']) ? $describe['version'] : NULL;
            $addExchange->has_ticker = isset($describe['has']['fetchTickers']) ? true : false;
            $addExchange->has_ohlcv = isset($describe['has']['fetchOHLCV']) ? true : false;
            $addExchange->save();
        } // foreach
    }
}
