<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2019-01-28
 * Time: 19:54
 */

namespace App\Services;

use App\Models\Ticker;
use App\Models\Trades;
use App\Models\Trailing;

class TrailingServices
{

	/**
	 * @var Trades
	 */
	protected $trades;

	/**
	 * @var Trailing
	 */
	protected $trailing;

	/**
	 * @var Ticker
	 */
	protected $ticker;

	/**
	 * @var TradeServices
	 */
	protected $tradesServices;

	/**
	 * TrailingServices constructor.
	 *
	 * @param Trades        $Trades
	 * @param Trailing      $Trailing
	 * @param Ticker        $Ticker
	 * @param TradeServices $TradeServices
	 */
	public function __construct(Trades $Trades, Trailing $Trailing, Ticker $Ticker, TradeServices $TradeServices)
	{
		$this->trades = $Trades;
		$this->trailing = $Trailing;
		$this->ticker = $Ticker;
		$this->tradesServices = $TradeServices;
	}

	/**
	 * Set initial sell price for trailing
	 * this should be used on the buy
	 *
	 * @param       $price
	 * @param int   $trailing
	 * @param array $params
	 */
	public function initialPrice($price, int $trailing, array $params)
	{
		$tradeID = $this->trades->getLatestOrder($params['strategy'], $params['symbol'], $params['exchange'])
			->first()->id;

		$percentage = $trailing / 100;
		$sell = $price - ($price * $percentage);

		$insert = new Trailing();
		$insert->fill(array(
			'trade_id' => $tradeID,
			'state'    => 'open',
			'trailing' => $trailing,
			'fix_sell' => $sell,
		))->save();
	}

	/**
	 * We check the data for Trailing
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function checkTrailing(array $params)
	{
		$openTrade = $this->trades->getLatestOpenOrder($params['strategy'], $params['symbol'], $params['exchange']);

		if ($openTrade->count() > 0) {
			$trade = $openTrade->first();

			$lastTrailing = $this->trailing::where('trade_id', $trade->id);

			if ($lastTrailing->count() > 0) {

				$trailing = $lastTrailing->first();
				$this->updateTrailing($trailing, $params);

				return true;
			} // if
		} // if

		return false;
	}

	/**
	 * Update trailing or Sell if the price is below fixed price
	 *
	 * @param $trailing
	 * @param $params
	 *
	 * @return bool
	 */
	private function updateTrailing($trailing, $params)
	{
		$price = $this->ticker->getLastDataByPair($params['symbol'], $params['exchange'])->first();

		if ($price->bid > $trailing->fix_sell) {
			$percentage = $trailing->trailing / 100;
			$sell = $price->bid - ($price->bid * $percentage);

			$trailing->update(
				array(
					'fix_sell' => $sell
				)
			);
		} // if

		if ($price->bid <= $trailing->fix_sell) {
			$this->tradeServices->orderSell($params['strategy'], $params['symbol'],
				$params['exchange'],$price->bid);
		} // if

		return true;
	}
}