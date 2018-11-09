<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
 * Class Trades
 * @package App\Models
 *
 * @property int $order_id
 * @property int $exchange_id
 * @property string $symbol
 * @property int $timestamp
 * @property string $strategy
 * @property string $order
 * @property string $status
 * @property boolean $order_executed
 * @property int $price
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
            'profit' => 'Buy/sell closed show profit',
            'percentage' => 'Show profit in percentage'

        );

        return $names[$attribute];
    }
}
