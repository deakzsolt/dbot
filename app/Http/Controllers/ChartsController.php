<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2019-03-15
 * Time: 16:27
 */

namespace App\Http\Controllers;

use App\Models\Exchanges;
use App\Traits\DataProcessing;

class ChartsController extends Controller
{
	use DataProcessing;

	protected $timeFrame = '30m';

	public function chartData()
	{
		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;
		$data = $this->getLatestData('BTC/USDT', 150, $this->timeFrame)[$exchangeId];


		return trader_stoch($data['high'], $data['low'], $data['close'], 14, 3, 3, 14, 3);
	}

	/**
	 * Returns Json formatted data to the Charts
	 *
	 * @return array
	 */
	public function getChartData()
	{
		$exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;
		$data = $this->getLatestData('BTC/USDT', 100, $this->timeFrame)[$exchangeId];

		$stochastic = trader_stoch($data['high'], $data['low'], $data['close'], 14, 3, 3, 14, 3);
		$adx = trader_adx($data['high'], $data['low'], $data['close'],14);


		$response = array();
		foreach ($stochastic[0] as $item) {
			$response[] = array(
				"value1" => number_format($item,2)
			);
		} // foreach
		$i = 0;
		foreach ($stochastic[1] as $item) {
			$response[$i]["value2"] = number_format($item,2);
			$i++;
		} // foreach

		$times = array_slice($data['timestamp'], -count($response));
		$i = 0;
		foreach ($times as $time) {
			$response[$i]["category"] = date('H:i',$time);
			$i++;
		} // foreach
		$i = 0;

		$adx = array_slice($adx, -count($response));
		foreach ($adx as $adxValue) {
			$response[$i]["value3"] = $adxValue;
			$i++;
		} // foreach

		return $response;
	}
}