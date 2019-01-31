<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ticker
 * @package App\Models
 *
 * @property int $id
 * @property integer $exchange_id
 * @property string $symbol
 * @property int $timestamp
 * @property int $datetime
 * @property float $high
 * @property float $low
 * @property float $bid
 * @property float $ask
 * @property float $vwap
 * @property float $open
 * @property float $close
 * @property float $first
 * @property float $last
 * @property float $change
 * @property float $percentage
 * @property float $average
 * @property float $baseVolume
 * @property float $quoteVolume
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Ticker extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'exchange_id',
        'symbol',
        'timestamp',
        'datetime',
        'high',
        'low',
        'bid',
        'ask',
        'vwap',
        'open',
        'close',
        'first',
        'last',
        'change',
        'percentage',
        'average',
        'baseVolume',
        'quoteVolume',
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
            'exchange_id' => 'Exchange Id',
            'symbol' => 'Trading pair',
            'timestamp' => 'Timestamp',
            'datetime' => 'DateTime',
            'high' => 'Highest price',
            'low' => 'Lowest price',
            'bid' => 'Current best bid (buy) price',
            'ask' => 'Current best ask (sell) price',
            'vwap' => 'Volume weighed average price',
            'open' => 'Opening price',
            'close' => 'Price of last trade (closing price for current period)',
            'first' => '',
            'last' => 'Same as "close", duplicated for convenience',
            'change' => 'Absolute change, "last - open"',
            'percentage' => 'Relative change, "(change/open) * 100"',
            'average' => 'average price, "(last + open) / 2"',
            'baseVolume' => 'Volume of base currency traded for last 24 hours',
            'quoteVolume' => 'Volume of quote currency traded for last 24 hours',
        );

        return $names[$attribute];
    }

    /**
     * Return distinct trading pairs
     *
     * @return mixed
     */
    public static function getPairs()
    {
        return Ticker::select('symbol')->distinct()->get();
    }
}
