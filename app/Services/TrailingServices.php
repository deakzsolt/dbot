<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2019-01-28
 * Time: 19:54
 */

namespace App\Services;

use App\Models\Trades;
use App\Models\Trailing;

class TrailingServices
{

	/**
	 * @var
	 */
	protected $trades;

	/**
	 * @var
	 */
	protected $trailing;

	/**
	 * This is set now for testing
	 *
	 * @var int
	 */
	protected $tradingAmount = 100;

	/**
	 * TrailingServices constructor.
	 *
	 * @param Trades $trades
	 */
	public function __construct(Trades $trades, Trailing $trailing)
	{
		$this->trades = $trades;
		$this->trailing = $trailing;
	}

	public function handleTrailing() {

	}

	private function initialPrice($price) {
		$trailing = $this->trailing/100;

		$buy = $price-($price*$trailing);

		echo $buy;
	}
}