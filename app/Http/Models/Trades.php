<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
 *
 */
class Trades extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'exchange_id',
        'symbol',
        'order',
        'status',
        'timestamp',
        'datetime',
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
            'order' => 'Buy or Sell',
            'status' => 'Open or Closed',
            'timestamp' => 'Timestamp',
            'datetime' => 'DateTime',
        );

        return $names[$attribute];
    }
}
