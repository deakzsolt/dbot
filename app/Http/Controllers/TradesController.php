<?php

namespace App\Http\Controllers;

use App\Models\Trades;
use Illuminate\Http\Request;

class TradesController extends Controller
{
	/*
	 *  Display the trader data
	 */
	public function index()
	{
		$buyTrades = Trades::where('status', 'closed')
			->where('order', 'buy')
			->join('trailings', 'trades.id', '=', 'trailings.trade_id')
			->orderBy('trades.created_at', 'desc')
			->get();
		$openTrades = Trades::where('status', 'open')
			->where('order', 'buy')
			->join('trailings', 'trades.id', '=', 'trailings.trade_id')
			->orderBy('trades.created_at', 'desc')
			->get();

		$sellTrades = $this->getClosedTrades();
		return view('trades.index', compact('buyTrades','openTrades', 'sellTrades'));
	}

	/**
	 * Return closed trades as order_id key in array
	 *
	 * @return array
	 */
	public function getClosedTrades()
	{
		$trades = Trades::where('status', 'closed')->where('order', 'sell')->orderBy('trades.created_at', 'desc')->get();

		$closed = array();
		foreach ($trades as $trade) {
			$closed[$trade->order_id] = array(
				'exchange_id'    => $trade->exchange_id,
				'symbol'         => $trade->symbol,
				'timestamp'      => $trade->timestamp,
				'strategy'       => $trade->strategy,
				'order'          => $trade->order,
				'status'         => $trade->status,
				'order_executed' => $trade->order_executed,
				'price'          => $trade->price,
				'trade'          => $trade->trade,
				'amount'         => $trade->amount,
				'profit'         => $trade->profit,
				'percentage'     => $trade->percentage,
				'created_at'     => $trade->created_at,
				'updated_at'     => $trade->updated_at,
				'deleted_at'     => $trade->deleted_at,
			);
		} // foreach

		return $closed;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 */
	public function store(Request $request)
	{
		$trade = new Trades();
		$trade->fill($request->all());
		$trade->save();
	}
}
