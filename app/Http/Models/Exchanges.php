<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Exchanges
 * @package App\Models
 *
 * @property int $id
 * @property string $exchange
 * @property boolean $ccxt
 * @property integer $use
 * @property text $request
 * @property string $url
 * @property string $url_api
 * @property string $url_doc
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
        'ccxt',
        'use',
        'request',
        'url',
        'url_api',
        'url_doc',
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
            'use' => 'From',
            'request' => 'Request type',
            'url' => 'Url to exchange',
            'url_api' => 'Exchange API url',
            'url_doc' => 'Exchange url documentation',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        );

        return $names[$attribute];
    }
}
