<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 22.
 * Time: 20:40
 */

namespace App\Console\Commands;

use App\Models\{Exchanges,Ticker};
use App\Utils\Indicators;
use Illuminate\Console\Command;
use App\Traits\DataProcessing;

class TestIndicators extends Command
{

	use DataProcessing;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'test:indicators';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Simple tester for indicators';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$indicators = new Indicators();

		$pairs = Ticker::getPairs();

		$exchangeId = Exchanges::where('slug','poloniex')->first()->id;

		while(1) {
			$headers = array();
			$body = array();
			foreach ($pairs as $pair) {
				$pair = $pair['symbol'];
				$headers[] = $pair;
				$data = $this->getLatestData($pair,228,'1h' );

				$i = 0;
				foreach ($indicators::$indicators as $indicator) {
					$respons = $indicators->$indicator($data[$exchangeId]);

					$indicator = str_pad($indicator,20);
					$body[$i][] = ($respons > 0 ? "<bg=green>$indicator</>" : ($respons < 0 ? "<bg=red>$indicator</>" : $indicator));
					$i++;
				} // foreach
			} // foreach

			$this->table($headers, $body);
			sleep(5);
		} // while
	}
}
