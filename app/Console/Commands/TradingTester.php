<?php

namespace App\Console\Commands;

use App\Models\ExchangePairs;
use Illuminate\Console\Command;

/**
 * Class TradingTester
 * @package App\Console\Commands
 */
class TradingTester extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'trade:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Simple command for trading strategy tests';

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
		$symbols = array(
			0  => "BTC/USD",
			1  => "BTC/EUR",
			2  => "BTC/CNY",
			3  => "BTC/RUB",
			4  => "BTC/CHF",
			5  => "BTC/JPY",
			6  => "BTC/GBP",
			7  => "BTC/CAD",
			8  => "BTC/AUD",
			9  => "BTC/AED",
			10 => "BTC/BGN",
			11 => "BTC/CZK",
			12 => "BTC/DKK",
			13 => "BTC/HKD",
			14 => "BTC/HRK",
			15 => "BTC/HUF",
			16 => "BTC/ILS",
			17 => "BTC/INR",
			18 => "BTC/MUR",
			19 => "BTC/MXN",
			20 => "BTC/NOK",
			21 => "BTC/NZD",
			22 => "BTC/PLN",
			23 => "BTC/RON",
			24 => "BTC/SEK",
			25 => "BTC/SGD",
			26 => "BTC/THB",
			27 => "BTC/TRY",
			28 => "BTA/ZAR");

		foreach (ExchangePairs::where('exchange_id', 2)->get() as $pair) {
			$this->line($pair->exchange_pair);
			if (!in_array($pair->exchange_pair, $symbols)) {
				$this->error($pair);
			}
		} // foreach
	}
}
