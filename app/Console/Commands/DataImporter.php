<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Models\Options;
use App\Models\Ticker;
use App\Traits\TimeWrapper;
use ccxt\ExchangeError;
use ccxt\NetworkError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class DataImporter
 * @package App\Console\Commands
 */
class DataImporter extends Command
{
	use TimeWrapper;

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
	protected $description = 'Imports data from exchanges, this should be used in the cron.';

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
		$getExchanges = unserialize(Options::where('item', 'DATA_IMPORTER')->first()->value);

		foreach ($getExchanges as $exchange => $pairs) {

			$className = '\ccxt\\' . $exchange;
			$exchange = new $className (array(
				'verbose' => false,
				'timeout' => 30000,
			));

			while (1) {
				try {

					foreach ($pairs as $symbol) {

						$response = $exchange->fetch_ticker($symbol);
						$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;

						$datetime = date('Y-m-d H:i:s', strtotime($response['datetime']));
						$exchangeTimestamp = intval($response['timestamp'] / 1000);
						$time = $this->timeSequence($exchangeTimestamp);

						$result = $this->preProcessData($response, $time['timestamp'], $symbol);

						$ticker = new Ticker();
						$ticker::updateOrCreate(array(
							'exchange_id' => $exchangeId,
							'symbol'      => $symbol,
							'timestamp'   => $time['timestamp'],
							'datetime'    => $time['datetime'],
						), array(
							'high'        => $result['high'],
							'low'         => $result['low'],
							'bid'         => $result['bid'],
							'ask'         => $result['ask'],
							'vwap'        => $result['vwap'],
							'open'        => $result['open'],
							'close'       => $result['close'],
							'last'        => $result['last'],
							'change'      => $result['change'],
							'percentage'  => $result['percentage'],
							'average'     => $result['average'],
							'baseVolume'  => $result['baseVolume'],
							'quoteVolume' => $result['quoteVolume'],
						));
					} // foreach

				} catch (NetworkError $e) {
					Log::warning('[Network Error] ' . $e->getMessage());
				} catch (ExchangeError $e) {
					Log::warning('[Exchange Error] ' . $e->getMessage());
				} catch (\Exception $e) {
					Log::error('[Error] ' . $e->getMessage());
				}
				sleep(5);
			} // while
		} // foreach
	}

	/**
	 * Preprocess data for timeWarp
	 *
	 * use bid and ask for high and low as most exchanges give back 24h high/low
	 *
	 * @param $data
	 * @param $timestamp
	 * @param $symbol
	 *
	 * @return mixed
	 */
	private function preProcessData($data, $timestamp, $symbol)
	{
		$prices = Ticker::where('timestamp', $timestamp)->where('symbol', $symbol);

		if ($prices->count() > 0) {
			$price = $prices->first();

			$data['high'] = $price->high;
			$data['low'] = $price->low;

			if ($data['ask'] > $price->high) {
				$data['high'] = $data['ask'];
			} // if

			if ($data['bid'] < $price->low) {
				$data['low'] = $data['bid'];
			} // if
		} else {
			$data['high'] = $data['ask'];
			$data['low'] = $data['bid'];
		} // if

		return $data;
	}

}
