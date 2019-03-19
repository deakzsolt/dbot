<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Models\Ticker;
use App\Models\Trades;
use App\Utils\Strategies;
use Illuminate\Console\Command;
use App\Traits\DataProcessing;

class trader extends Command
{
	use DataProcessing;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'run:trader';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Basic strategy with SMA, Stochastic and RSI.';

	/**
	 * @var Strategies
	 */
	private $strategies;

	/**
	 * trader constructor.
	 *
	 * @param Strategies $strategies
	 */
	public function __construct(Strategies $strategies)
	{
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

		$this->info("------------------------------------------------------------------");
		$this->info("  This is just an example when it should trade");
		$this->info("  1 buy signal");
		$this->info("  -1 sell signal");
		$this->info("  0 do nothing");
		$this->info("------------------------------------------------------------------\n");

		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;

		//        TODO remove the exchange id and get use from th db
		//        TODO move this to test or example refactor this to indicator with buy and sell
		while (1) {
			$this->line("Date: " . date('Y-m-d H:i:s'));
			$headers = array();
			$data = array();
			foreach (Ticker::getPairs() as $pairs) {
				$headers[] = $pairs['symbol'];
				$datas = $this->getLatestData($pairs['symbol'], 228, '1h');
				$response = $this->strategies->strategy_sma_stoch($datas[$exchangeId]);

				switch ($response['state']) {
					case 1:
						$state = "<fg=green>" . $response['state'] . "</>";

						$trade = new Trades();
						$trade->exchange_id = $exchangeId;
						$trade->order_id = '123';
						$trade->symbol = $pairs['symbol'];
						$trade->strategy = 'sma_stoch_rsi';
						$trade->price = $response['price'];
						$trade->order = 'buy';
						$trade->status = 'open';
						$trade->order_executed = true;
						$trade->save();

						break;
					case -1:
						$state = "<fg=red>" . $response['state'] . "</>";

						$trade = new Trades();
						$trade->exchange_id = $exchangeId;
						$trade->order_id = '123';
						$trade->symbol = $pairs['symbol'];
						$trade->strategy = 'sma_stoch_rsi';
						$trade->price = $response['price'];
						$trade->order = 'sell';
						$trade->status = 'open';
						$trade->order_executed = true;
						$trade->save();

						break;
					case 0:
						$state = "<fg=yellow>" . $response['state'] . "</>";
						break;
				} // switch

				$data[0][] = $response['strategy'];
				$data[1][] = 'price: ' . $response['price'];
				$data[2][] = 'SMA: <fg=' . $response['colors']['sma'] . '>' . $response['sma'] . '</>';
				//                $data[3][] = 'EMA: '.$response['ema'];
				$data[4][] = '%K: <fg=' . $response['colors']['slowk'] . '>' . $response['slowk'] . '</>';
				$data[5][] = '%D: <fg=' . $response['colors']['slowd'] . '>' . $response['slowd'] . '</>';
				//                $data[6][] = 'RSI: <fg='.$response['colors']['rsi'].'>'.$response['rsi'].'</>';
				$data[7][] = $response['side'];
				$data[8][] = $state;

			} // foreach

			$this->table($headers, $data);
			sleep(5);
		} // while
	}
}
