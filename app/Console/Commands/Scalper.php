<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Ticker;
use App\Trades;
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
	 * in percentage
	 *
	 * @var int
	 */
	protected $trailing = 1;

	/**
	 * Stop loss if it goes below bought price
	 * in percentage
	 *
	 * @var float
	 */
	protected $stopLoss = 0.5;

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
		// TODO this is now mostly as testing setup now will be set as cron to run this and make a trades
		// TODO set this up with tracking as buy indicator
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

					$order = $this->trade('strategy_trailing_sar', $pairs['symbol'],
						array_pop($data[$exchangeId]['ask']), $exchangeId, 'sell');
					//					return -1; //sell
				} elseif (($current_sar < $currentLow) && ($prior_sar < $currentLow) && ($earlier_sar < $currentLow)) {
					$state = "  1 | <bg=green>" . $pairs['symbol'] . "</>";

					$order = $this->trade('strategy_trailing_sar', $pairs['symbol'],
						array_pop($data[$exchangeId]['bid']), $exchangeId, 'buy');
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
$currentSarPoint - $priorSarPoint - $earlierSarPoint
$order";
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

	/**
	 * Execute trades on signals with tracking in db
	 *
	 * @param $strategy
	 * @param $symbol
	 * @param $price
	 * @param $exchange_id
	 * @param $order
	 *
	 * @return string
	 */
	private function trade($strategy, $symbol, $price, $exchange_id, $order)
	{
		$status = '<bg=green>All is done waiting for new orders</>';
		$lastTrade = Trades::where('strategy', $strategy)->where('symbol', $symbol)->orderBy('updated_at', 'desc');

		if ($lastTrade->count() > 0 && $order == 'sell') {
			$position = $lastTrade->first();

			if ($position->order == 'buy' && $position->status == 'open' && $position->order_executed == 1) {

				$position->status = 'closed';
				$position->save();

				$trade = new Trades();

				$trade->order_id = $position->order_id;
				$trade->exchange_id = $exchange_id;
				$trade->symbol = $symbol;
				$trade->timestamp = now()->timestamp;
				$trade->strategy = $strategy;
				$trade->order = 'sell';
				$trade->status = 'closed';
				$trade->price = $price;
				$trade->trade = $position->amount;

				$newAmount = $position->amount * $price;
				$trade->amount = $newAmount;
				$trade->profit = $price - $position->price;

				$difference = $newAmount - $position->trade;
				$percentage = $difference / $position->trade * 100;
				$trade->percentage = $percentage;

				$trade->save();

				$status = '<bg=green>Sold!</>';
				// TODO add here exchange sell
			} // if
		} // if

		if ($order == 'buy') {
			if ($lastTrade->count() == 0) {

				$tradingAmount = 100;

				$closedTrade = Trades::where('strategy', $strategy)
					->where('symbol', $symbol)
					->where('status', 'closed')
					->where('order', 'sell')
					->orderBy('updated_at', 'desc');
				if ($closedTrade->count() > 0) {
					$tradingAmount = $closedTrade->first()->amount;
				} // if

				$trade = new Trades();

				$trade->order_id = hash('sha256', now()->timestamp . $symbol);
				$trade->exchange_id = $exchange_id;
				$trade->symbol = $symbol;
				$trade->timestamp = now()->timestamp;
				$trade->strategy = $strategy;
				$trade->order = 'buy';
				$trade->status = 'open';
				$trade->order_executed = 1;
				$trade->price = $price;
				$trade->trade = $tradingAmount;
				$trade->amount = $tradingAmount / $price;

				$trade->save();

				$status = '<bg=green>Bought!</>';
				// TODO add here exchange buy
			} // if
		} // if

		return $status;
	}
}