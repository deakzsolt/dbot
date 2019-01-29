<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2019-01-28
 * Time: 19:53
 */

namespace App\Services;

use App\Models\Exchanges;
use App\Trades;
use Illuminate\Support\Facades\Log;

class TradeServices
{
	/**
	 * @var
	 */
	protected $trades;

	/**
	 * This is set now for testing
	 * TODO separate USD from other currency
	 *
	 * @var int
	 */
	protected $tradingAmount = 100;

	public function __construct(Trades $trades)
	{
		$this->trades = $trades;
	}

	/**
	 * Saves data into db and makes order on exchange
	 *
	 * @param string $strategy
	 * @param string $symbol
	 * @param int    $exchange
	 * @param        $price
	 *
	 * @return bool
	 */
	public function orderBuy(string $strategy, string $symbol, int $exchange, $price)
	{
		$lastTrade = $this->trades->getLatestOrder($strategy, $symbol, $exchange);

		$order = false;
		if ($lastTrade->count() == 0) {
			$order = true;
		} else {
			$position = $lastTrade->first();
			if ($position->order == 'sell' && $position->status == 'closed' && $position->order_executed == 1) {
				$order = true;
			} // if
		} // if

		if ($order) {

			$closedTrade = $this->trades->getClosedOrder($strategy, $symbol, $exchange);

			if ($closedTrade->count() > 0) {
				$this->tradingAmount = $closedTrade->first()->amount;
			} // if

			$trade = new Trades();
			$trade->fill(array(
					'order_id'       => hash('sha256', now()->timestamp . $symbol),
					'exchange_id'    => $exchange,
					'symbol'         => $symbol,
					'timestamp'      => now()->timestamp,
					'strategy'       => $strategy,
					'order'          => 'buy',
					'status'         => 'open',
					'order_executed' => 1,
					'price'          => $price,
					'trade'          => $this->tradingAmount,
					'amount'         => $this->tradingAmount / $price,
				))->save();

			$this->handleOrder($exchange);

			return true;
		} // if
		return false;
	}

	/**
	 * Saves data into db and makes order on exchange
	 *
	 * @param string $strategy
	 * @param string $symbol
	 * @param int    $exchange
	 * @param        $price
	 *
	 * @return bool
	 */
	public function orderSell(string $strategy, string $symbol, int $exchange, $price)
	{
		$lastTrade = $this->trades->getLatestOrder($strategy, $symbol, $exchange);
		$position = $lastTrade->first();

		if ($position->order == 'buy' && $position->status == 'open' && $position->order_executed == 1) {

			$position->status = 'closed';
			$position->save();

			$newAmount = $position->amount * $price;
			$difference = $newAmount - $position->trade;
			$percentage = $difference / $position->trade * 100;

			$trade = new Trades();
			$trade->fill(array(
					'order_id'       => $position->order_id,
					'exchange_id'    => $exchange,
					'symbol'         => $symbol,
					'timestamp'      => now()->timestamp,
					'strategy'       => $strategy,
					'order'          => 'sell',
					'status'         => 'closed',
					'order_executed' => 1,
					'price'          => $price,
					'trade'          => $position->amount,
					'amount'         => $newAmount,
					'profit'         => $difference,
					'percentage'     => $percentage,
				))->save();

			$this->handleOrder($exchange);

			return true;
		} // if
		return false;
	}

	/**
	 * TODO finish placing orders
	 *
	 * @param $exchange
	 */
	private function handleOrder($exchange)
	{
		$trade = null;
//		try {
//			$exchangeName = Exchanges::find($exchange)->first()->slug;
//
//			$className = '\ccxt\\' . $exchangeName;
//			$exchange = new $className;
//			$exchange->parse_trade($trade);
//
//		} catch (\Exception $e) {
//			Log::error('[Error] ' . $e->getMessage());
//		}
	}
}