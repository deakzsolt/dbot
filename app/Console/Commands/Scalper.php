<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Services\TradeServices;
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
	protected $description = 'This is a scalper trading mostly for day trading with SAR indicator as buy and trailing for sell';

	/**
	 * Time Frame for data
	 *
	 * day trade - 5m/15m/30m
	 * swing trade - 1h/4h/daily
	 * core trade - 4h/daily/weekly
	 * long-term investment - daily/weekly/monthly
	 *
	 * @var string
	 */
	protected $timeFrame = '30m';

	/**
	 * Sell if highest price goes down
	 * This is in percentage
	 * so min is 1 and max is 100
	 *
	 * @var int
	 */
	protected $trailing = 1;

	/**
	 * Stop loss if it goes below bought price
	 * This is in percentage
	 * so min is 1 and max is 100
	 *
	 * @var float
	 */
	protected $stopLoss = 0.5;

	/**
	 * @var TradeServices
	 */
	protected $tradeServices;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(TradeServices $tradeServices)
	{
		$this->tradeServices = $tradeServices;
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// TODO this is now mostly as testing setup now will be set as cron to run this and make a trades
		// TODO add trailing for sell
		// TODO add in stop loss for tighter sell
		// TODO when done move to Strategies

		$this->line(date('Y-m-d H:i:s') . " - <bg=yellow>Simple indicator test for Parabolic SAR ...</>");

		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;
		while (1) {

			$headers = '';
			$indicators = array();
			foreach (Ticker::getPairs() as $pairs) {

				$data = $this->getLatestData($pairs['symbol'], 25, $this->timeFrame);
				$_sar = trader_sar($data[$exchangeId]['high'], $data[$exchangeId]['low'], 0.02, 0.02);

				$current_sar = array_pop($_sar); #[count($_sar) - 1];
				$prior_sar = array_pop($_sar); #[count($_sar) - 2];
				$earlier_sar = array_pop($_sar); #[count($_sar) - 3];
				$currentHigh = array_pop($data[$exchangeId]['high']); #[count($data['high'])-1];
				$currentLow = array_pop($data[$exchangeId]['low']); #[count($data['low'])-1];
				$priorHigh = array_pop($data[$exchangeId]['high']);
				$priorLow = array_pop($data[$exchangeId]['low']);
				$earlierHigh = array_pop($data[$exchangeId]['high']);
				$earlierLow = array_pop($data[$exchangeId]['low']);

				/**
				 *  if the last three SAR points are above the candle (high) then it is a sell signal
				 *  if the last three SAR points are below the candle (low) then is a buy signal
				 */
				if (($current_sar > $currentHigh) && ($prior_sar > $currentHigh) && ($earlier_sar > $currentHigh)) {
					$state = " -1 | <bg=red>" . $pairs['symbol'] . "</>";

					$this->tradeServices->orderSell('strategy_trailing_sar', $pairs['symbol'],
						$exchangeId,array_pop($data[$exchangeId]['ask']));
					//					return -1; //sell
				} elseif (($current_sar < $currentLow) && ($prior_sar < $currentLow) && ($earlier_sar < $currentLow)) {
					$state = "  1 | <bg=green>" . $pairs['symbol'] . "</>";

					$this->tradeServices->orderBuy('strategy_trailing_sar', $pairs['symbol'],
						$exchangeId,array_pop($data[$exchangeId]['bid']));
					//					return 1; // buy
				} else {
					$state = "  0 | " . $pairs['symbol'];
					//					return 0; // hold
					$order = '<fg=magenta>just wait for signal ...</>';
				} // if

				$lastPrice = array_pop($data[$exchangeId]['close']);
				$prevPrice = array_pop($data[$exchangeId]['close']);
				$candle = $prevPrice < $lastPrice ? 'green' : 'red';
				$headers .= "| " . $pairs['symbol'] . " <bg=$candle>" . $lastPrice . "</> | ";

				if ($currentHigh < $current_sar) {
					$currentSarPoint = '<fg=red>above</>';
				} else {
					$currentSarPoint = '<fg=green>below</>';
				} // if

				if ($currentHigh < $prior_sar) {
					$priorSarPoint = '<fg=red>above</>';
				} else {
					$priorSarPoint = '<fg=green>below</>';
				} // if

				if ($currentHigh < $earlier_sar) {
					$earlierSarPoint = '<fg=red>above</>';
				} else {
					$earlierSarPoint = '<fg=green>below</>';
				} // if

				$indicators[] = $state . " => <fg=yellow>$current_sar, $prior_sar, $earlier_sar</> Price: | <fg=cyan>$currentHigh, $currentLow | $priorHigh, $priorLow | $earlierHigh, $earlierLow |</>
$currentSarPoint - $priorSarPoint - $earlierSarPoint";
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

	private function trailing($price) {
		$trailing = $this->trailing/100;

		$buy = $price-($price*$trailing);

		echo $buy;
	}
}