<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Ticker;
use Illuminate\Console\Command;
use App\Traits\DataProcessing;
use App\Traits\Strategies;

class Scalper extends Command
{
	use DataProcessing, Strategies;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'run:scalper';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

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
		$this->line(date('Y-m-d H:i:s') . " - <bg=yellow>Simple indicator test for Parabolic SAR ...</>");

		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;
		while (1) {

			$headers = '';
			$indicators = array();
			foreach (Ticker::getPairs() as $pairs) {

				$data = $this->getLatestData($pairs['symbol'], 25, '30m');

				$_sar = trader_sar($data[$exchangeId]['high'], $data[$exchangeId]['low'], 0.02, 0.2);

				$current_sar = array_pop($_sar); #[count($_sar) - 1];
				$prior_sar = array_pop($_sar); #[count($_sar) - 2];
				$earlier_sar = array_pop($_sar); #[count($_sar) - 3];
				$last_high = array_pop($data[$exchangeId]['high']); #[count($data['high'])-1];
				$last_low = array_pop($data[$exchangeId]['low']); #[count($data['low'])-1];

				/**
				 *  if the last three SAR points are above the candle (high) then it is a sell signal
				 *  if the last three SAE points are below the candle (low) then is a buy signal
				 */
				if (($current_sar > $last_high) && ($prior_sar > $last_high) && ($earlier_sar > $last_high)) {
					$state = " -1 | <bg=red>" . $pairs['symbol'] . "</>";
					//					return -1; //sell
				} elseif (($current_sar < $last_low) && ($prior_sar < $last_low) && ($earlier_sar < $last_low)) {
					$state = "  1 | <bg=green>" . $pairs['symbol'] . "</>";
					//					return 1; // buy
				} else {
					$state = "  0 | " . $pairs['symbol'];
					//					return 0; // hold
				} // if

				$lastPrice = array_pop($data[$exchangeId]['close']);
				$prevPrice = array_pop($data[$exchangeId]['close']);
				$candle = $prevPrice < $lastPrice ? 'green' : 'red';
				$headers .= "| " . $pairs['symbol'] . " <bg=$candle>" . $lastPrice . "</> | ";

				$indicators[] = $state . " => <fg=yellow>$current_sar, $prior_sar, $earlier_sar, $last_high, $last_low</>";

			} // foreach

			$this->line(date('Y-m-d H:i:s') . " - " . $headers);

			foreach ($indicators as $indicator) {
				$this->line($indicator);
				usleep(100000);
			}

			$this->info(date('Y-m-d H:i:s') . " - Count the sheep's now ...\n");
			sleep(5);

		} // while
	}
}
