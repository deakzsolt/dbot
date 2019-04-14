<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class exchangePairs
 * @package App\Models
 *
 * @property int $id
 * @property int $exchange_id
 * @property string $exchange_pair
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExchangePairs extends Model
{
	use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'exchange_id',
        'exchange_pair',
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
            'exchange_id' => 'Exchange id',
            'exchange_pair' => 'Exchange trading pairs',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        );

        return $names[$attribute];
    }
}
