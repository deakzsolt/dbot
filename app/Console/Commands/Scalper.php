<?php

namespace App\Console\Commands;

use App\Models\{Exchanges,Ticker};
use App\Services\{TradeServices,TrailingServices};
use App\Traits\DataProcessing;
use App\Utils\Strategies;
use Illuminate\Console\Command;

class Scalper extends Command
{
	use DataProcessing;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'run:scalper {display=false}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This is a scalper trading mostly for day trading, under testing!';

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
	protected $trailing = 2;

	/**
	 * @var TradeServices
	 */
	protected $tradeServices;

	/**
	 * @var TrailingServices
	 */
	protected $trailingServices;

	/**
	 * @var Strategies
	 */
	private $strategies;

	/**
	 * Scalper constructor.
	 *
	 * @param TradeServices    $TradeServices
	 * @param TrailingServices $TrailingServices
	 * @param Strategies       $strategies
	 */
	public function __construct(TradeServices $TradeServices, TrailingServices $TrailingServices, Strategies $strategies)
	{
		$this->tradeServices = $TradeServices;
		$this->trailingServices = $TrailingServices;
		$this->strategies = $strategies;
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$display = $this->argument('display') == 'false' ? false : true;

		if ($display) {
			$this->line(date('Y-m-d H:i:s') . " - <bg=yellow>Simple indicator test for Parabolic SAR with Stochastic ...</>");
		} // if

		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;
		while (1) {
			$headers = '';
			$indicators = array();
			foreach (Ticker::getPairs() as $pairs) {

				$params = array(
					'strategy' => 'strategy_stoch_adx',
					'symbol'   => $pairs['symbol'],
					'exchange' => $exchangeId,
				);

				$data = $this->getLatestData($pairs['symbol'], 150, $this->timeFrame);
				$candle = $data[$exchangeId]['prevPrice'] < $data[$exchangeId]['lastPrice'] ? 'green' : 'red';
				$headers .= "| " . $pairs['symbol'] . " <bg=$candle>" . $data[$exchangeId]['lastPrice'] . "</> | ";

//				if ($this->trailingServices->checkTrailing($params)) {
//					$indicators[] = $pairs['symbol'] . '<fg=yellow> -> Trailing in progress ...</>';
//				} else {
					$response = $this->strategies->dbotStochAdx($data[$exchangeId]);

					switch ($response) {
						case 1:
							$state = "<bg=green>$response | Buy signal!</>";
							$this->tradeServices->orderBuy($params['strategy'], $pairs['symbol'], $exchangeId,
								$data[$exchangeId]['lastAsk']);

//							if ($this->tradeServices->orderBuy($params['strategy'], $pairs['symbol'], $exchangeId,
//								$data[$exchangeId]['lastAsk'])) {
//								$this->trailingServices->initialPrice($data[$exchangeId]['lastBid'], $this->trailing,
//									$params);
//							} // if
							break;
						case -1:
							$state = "<bg=red>$response | Sell signal by strategy!</>";
							$this->tradeServices->orderSell($params['strategy'], $pairs['symbol'], $exchangeId,
								$data[$exchangeId]['lastBid']);
							break;
						case 0:
							$state = "$response | Nothing to do, it's boring.";
							break;
					} // switch

					$indicators[] = $state = $pairs['symbol'] . " | " . $state;
//				} // if
			} // foreach

			if ($display) {
				$this->line(date('Y-m-d H:i:s') . " - " . $headers);

				foreach ($indicators as $indicator) {
					$this->line($indicator);
					usleep(100000);
				}

				$this->info(date('Y-m-d H:i:s') . " - Count the sheep's now ...\n");
			} // if
			sleep(5);

		} // while
	}
}