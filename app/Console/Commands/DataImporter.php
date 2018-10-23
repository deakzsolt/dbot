<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Models\Options;
use App\Ticker;
use ccxt\ExchangeError;
use ccxt\NetworkError;
use Illuminate\Console\Command;

class DataImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This imports data from exchanges.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        TODO add checker does exchange have ticker or ohlcvs
        $getExchanges = unserialize(Options::where('item','DATA_IMPORTER')->first()->value);

        foreach ($getExchanges as $exchange => $pairs) {
            $className = '\ccxt\\' . $exchange;
            $exchange = new $className (array (
                'verbose' => false,
                'timeout' => 30000,
            ));

            while(1) {
                try {

                    foreach ($pairs as $symbol) {
                        $result = $exchange->fetch_ticker($symbol);
                        $exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;

                        $ticker = new Ticker();
                        $ticker::updateOrCreate(
                            array(
                                'exchange_id' => $exchangeId,
                                'symbol' => $symbol,
                                'timestamp' => $result['timestamp'],
                                'datetime' => date('Y-m-d H:i:s', strtotime($result['datetime'])),
                                'high' => $result['high'],
                                'low' => $result['low'],
                                'bid' => $result['bid'],
                                'ask' => $result['ask'],
                                'vwap' => $result['vwap'],
                                'open' => $result['open'],
                                'close' => $result['close'],
                                'last' => $result['last'],
                                'change' => $result['change'],
                                'percentage' => $result['percentage'],
                                'average' => $result['average'],
                                'baseVolume' => $result['baseVolume'],
                                'quoteVolume' => $result['quoteVolume'],
                            )
                        );
                    } // foreach

                } catch (NetworkError $e) {
                    echo '[Network Error] ' . $e->getMessage () . "\n";
                } catch (ExchangeError $e) {
                    echo '[Exchange Error] ' . $e->getMessage () . "\n";
                } catch (\Exception $e) {
                    echo '[Error] ' . $e->getMessage () . "\n";
                }
                sleep(5);
            } // while
        } // foreach
    }
}
