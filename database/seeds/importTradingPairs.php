<?php

use Illuminate\Database\Seeder;

class importTradingPairs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $i = 1;
        foreach (\ccxt\Exchange::$exchanges as $exchange) {
            $className = '\ccxt\\' . $exchange;
            $exchange = new $className();

            echo "---------------------------------------------------------------------------------------------------\n";
            echo $i . " - " . $exchange->id . "\n";
            try {
                $markets = $exchange->load_markets();
                $symbols = array_keys($markets);
                $exchangeId = \App\Models\Exchanges::where('slug', $exchange->id)->first()->id;

                foreach ($symbols as $symbol) {
                    $tradingPair = new \App\Models\ExchangePairs();
                    $tradingPair->exchange_id = $exchangeId;
                    $tradingPair->exchange_pair = $symbol;
                    $tradingPair->save();
                } // foreach

                echo "\e[0;32m ".count($symbols) . "Symbols: " . implode(', ', $symbols) . "\e[0m\n";
            } catch (\Exception $e) {
                echo "\033[31m [Error] " . $e->getMessage() . " \033[0m\n";
            }
            echo "-------------------------------------------------------------------------------------------------\n\n";
            $i++;
        } // foreach
    }
}
