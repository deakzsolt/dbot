<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Class Trades
 * @package App\Models
 *
 * @property string $order_id
 * @property int $exchange_id
 * @property string $symbol
 * @property int $timestamp
 * @property string $strategy
 * @property string $order
 * @property string $status
 * @property boolean $order_executed
 * @property int $price
 * @property int $trade
 * @property int $amount
 * @property int $profit
 * @property int $percentage
 */
class Trades extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'order_id',
        'exchange_id',
        'symbol',
        'timestamp',
        'strategy',
        'order',
        'status',
        'order_executed',
        'price',
        'trade',
        'amount',
        'profit',
        'percentage'
    );

    /**
     * Returns readable names of attributes
     *
     * @param string $attribute
     * @return string $name
     */
    public static function attributeNames($attribute)
    {
        $names = array(
            'order_id' => 'Order pair id for buy and sell',
            'exchange_id' => 'Exchange Id',
            'symbol' => 'Trading pair',
            'timestamp' => 'Timestamp',
            'strategy' => 'Used strategy for order',
            'order' => 'Buy or Sell',
            'status' => 'Open or Closed',
            'order_executed' => 'Is order filled',
            'price' => 'Order price',
            'trade' => 'Amount of invested money',
            'amount' => 'Bought from invested money',
            'profit' => 'Buy/sell closed show profit',
            'percentage' => 'Show profit in percentage'

        );

        return $names[$attribute];
    }

	/**
	 * Returns latest trade
	 *
	 * @param string $strategy
	 * @param string $symbol
	 * @param int    $exchange
	 *
	 * @return mixed
	 */
	public function getLatestOrder(string $strategy, string $symbol, int $exchange)
	{
		return Trades::where('strategy', $strategy)
			->where('symbol', $symbol)
			->where('exchange_id', $exchange)
			->orderBy('created_at', 'desc');
	}

	/**
	 * Returns latest closed trade
	 *
	 * @param string $strategy
	 * @param string $symbol
	 *
	 * @return mixed
	 */
	public function getClosedOrder(string $strategy, string $symbol, int $exchange)
	{
		return Trades::where('strategy', $strategy)
			->where('symbol', $symbol)
			->where('exchange_id', $exchange)
			->where('status', 'closed')
			->where('order', 'sell')
			->orderBy('created_at', 'desc');
	}
}
