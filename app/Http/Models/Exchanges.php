<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Exchanges
 * @package App\Models
 *
 * @property int $id
 * @property string $exchange
 * @property string $slug
 * @property boolean $ccxt
 * @property integer $use
 * @property string $url
 * @property string $url_api
 * @property string $url_doc
 * @property string $version
 * @property boolean $has_ticker
 * @property boolean $has_ohlcv
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Exchanges extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'exchange',
        'slug',
        'ccxt',
        'use',
        'url',
        'url_api',
        'url_doc',
        'version',
        'has_ticker',
        'has_ohlcv'
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
            'exchange' => 'Exchange Name',
            'ccxt' => 'Belongs to ccxt',
            'use' => 'Is used',
            'url' => 'Url to exchange',
            'url_api' => 'Exchange API url',
            'url_doc' => 'Exchange url documentation',
            'version' => 'API version',
            'has_ticker' => 'Ticker usage',
            'has_ohlcv' => 'OHLCV usage',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        );

        return $names[$attribute];
    }
}
