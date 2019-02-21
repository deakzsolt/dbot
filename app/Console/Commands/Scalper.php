<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Services\TradeServices;
use App\Models\Ticker;
use App\Services\TrailingServices;
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
	protected $timeFrame = '1h';

	/**
	 * Sell if highest price goes down
	 * This is in percentage
	 * so min is 1 and max is 100
	 *
	 * @var int
	 */
	protected $trailing = 2;

	/**
	 * @var tradeServices
	 */
	protected $tradeServices;

	/**
	 * @var trailingServices
	 */
	protected $trailingServices;

	/**
	 * Scalper constructor.
	 *
	 * @param TradeServices    $TradeServices
	 * @param TrailingServices $TrailingServices
	 */
	public function __construct(TradeServices $TradeServices, TrailingServices $TrailingServices)
	{
		$this->tradeServices = $TradeServices;
		$this->trailingServices = $TrailingServices;
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// TODO when done move to cron

		$this->line(date('Y-m-d H:i:s') . " - <bg=yellow>Simple indicator test for Parabolic SAR with Stochastic ...</>");

		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;
		while (1) {
			$headers = '';
			$indicators = array();
			foreach (Ticker::getPairs() as $pairs) {

				$params = array(
					'strategy' => 'strategy_trailing_sar',
					'symbol'   => $pairs['symbol'],
					'exchange' => $exchangeId,
				);

				$data = $this->getLatestData($pairs['symbol'], 60, $this->timeFrame);
				$candle = $data[$exchangeId]['prevPrice'] < $data[$exchangeId]['lastPrice'] ? 'green' : 'red';
				$headers .= "| " . $pairs['symbol'] . " <bg=$candle>" . $data[$exchangeId]['lastPrice'] . "</> | ";

				if ($this->trailingServices->checkTrailing($params)) {
					$indicators[] = $pairs['symbol'] . '<fg=yellow> -> Trailing in progress ...</>';
				} else {
					$response = $this->sar_stoch($data[$exchangeId]);

					switch ($response) {
						case 1:
							$state = "<bg=green>$response | Buy signal!</>";
							if ($this->tradeServices->orderBuy($params['strategy'], $pairs['symbol'], $exchangeId,
								$data[$exchangeId]['lastAsk'])) {
								$this->trailingServices->initialPrice($data[$exchangeId]['lastBid'], $this->trailing, $params);
							} // if
							break;
						case -1:
							$state = "<bg=red>$response | Sell signal by sar,stoch and stochf ... Do nothing.</>";
							break;
						case 0:
							$state = "$response | Nothing to do, its boring.";
							break;
					} // switch

					$indicators[] = $state = $pairs['symbol'] . " | " . $state;
				} // if
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