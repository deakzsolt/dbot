<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2019-03-21
 * Time: 16:40
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AccountBalance extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'run:account';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Checks account balance and trades.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$exchange_id = 'coinbasepro';
		$exchange_class = "\\ccxt\\$exchange_id";
		$exchange = new $exchange_class (array (
			'apiKey' => env('COINBASEPRO_KEY'),
			'secret' => env('COINBASEPRO_SECRET'),
			'password' => env('COINBASEPRO_PASSWORD'),
			'urls'=> array(
				'api' => env('COINBASEPRO_URL')
			)

		));



		$exchange = $exchange->fetch_balance();

		dd($exchange['free']);
	}
}