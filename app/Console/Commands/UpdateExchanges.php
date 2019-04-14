<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2019-03-21
 * Time: 21:18
 */

namespace App\Console\Commands;


use App\Models\ExchangePairs;
use App\Models\Exchanges;
use ccxt\Exchange;
use Illuminate\Console\Command;

class UpdateExchanges extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'update:exchanges';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'From time to time we should update the exchanges information\'s in database.';

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
		foreach (Exchange::$exchanges as $exchange) {
			if (Exchanges::where('slug', $exchange)->count() == 0) {
				$this->info($exchange);

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
				$addExchange->version = isset($describe['version']) ? $describe['version'] : null;
				$addExchange->has_ticker = isset($describe['has']['fetchTickers']) ? true : false;
				$addExchange->has_ohlcv = isset($describe['has']['fetchOHLCV']) ? true : false;
				$addExchange->save();

				$this->insertPairs($exchange);
			} else {
				$updateExchange = Exchanges::where('slug', $exchange)->first();

				$this->info("[".$updateExchange->id."] ".$exchange . " ==> " . $updateExchange->exchange);

				$className = '\ccxt\\' . $exchange;
				$class = new $className;
				$describe = $class->describe();

				$url = serialize($describe['urls']['www']);
				$api = serialize($describe['urls']['api']);
				$docs = serialize($describe['urls']['doc']);

				$updateExchange->exchange = $describe['name'];
				$updateExchange->slug = $describe['id'];
				$updateExchange->ccxt = true;
				$updateExchange->url = $url;
				$updateExchange->url_api = $api;
				$updateExchange->url_doc = $docs;
				$updateExchange->version = isset($describe['version']) ? $describe['version'] : NULL;
				$updateExchange->has_ticker = isset($describe['has']['fetchTickers']) ? true : false;
				$updateExchange->has_ohlcv = isset($describe['has']['fetchOHLCV']) ? true : false;
				$updateExchange->save();

				$this->updatePairs($updateExchange);
			} // if
		} // foreach
	}

	/**
	 * Save new Exchange Pairs
	 *
	 * @param string $exchange
	 */
	private function insertPairs(string $exchange)
	{
		$className = '\ccxt\\' . $exchange;
		$exchange = new $className();

		$this->line(
			"---------------------------------------------------------------------------------------------------"
		);
		$this->line(" - " . $exchange->id);
		try {
			$markets = $exchange->load_markets();
			$symbols = array_keys($markets);
			$exchangeId = Exchanges::where('slug', $exchange->id)->first()->id;

			foreach ($symbols as $symbol) {
				$tradingPair = new ExchangePairs();
				$tradingPair->exchange_id = $exchangeId;
				$tradingPair->exchange_pair = $symbol;
				$tradingPair->save();
			} // foreach

			$this->info(count($symbols) . " - Symbols: " . implode(', ', $symbols));
		} catch (\Exception $e) {
			$this->error("[Error] " . $e->getMessage());
		}
		$this->line(
			"-------------------------------------------------------------------------------------------------\n"
		);
	}

	/**
	 * Update Existing Exchange Pairs
	 *
	 * @param Exchanges $exchange
	 */
	private function updatePairs(Exchanges $exchange)
	{
		$className = '\ccxt\\' . $exchange->slug;
		$ccxtExchange = new $className();

		$this->line(
			"---------------------------------------------------------------------------------------------------"
		);
		$this->line(" - " . $ccxtExchange->id);
		try {
			$markets = $ccxtExchange->load_markets();
			$symbols = array_keys($markets);

			foreach (ExchangePairs::where('exchange_id', $exchange->id)->get() as $pair) {
				if (!in_array($pair->exchange_pair,$symbols)) {
					$this->error("Deleted: ".$pair->exchange_pair);
					$pair->delete();
				}
			} // foreach

			foreach ($symbols as $symbol) {
				$pairs = new ExchangePairs();
				$pairs::updateOrCreate(array(
					'exchange_id' => $exchange->id,
					'exchange_pair' => $symbol
				));
				$this->info("Updated: ".$symbol);
			} // foreach

		} catch (\Exception $e) {
			$this->error("[Error] " . $e->getMessage());
		}
		$this->line(
			"-------------------------------------------------------------------------------------------------\n"
		);
	}
}
