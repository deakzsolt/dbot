<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Class Trailing
 * @package App\Models
 *
 * @property int $trade_id
 * @property string $state
 * @property int $trailing
 * @property int $fix_sell
 * @property int $difference
 * @property int $profit
 */
class Trailing extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'trade_id',
		'state',
		'trailing',
		'fix_sell',
		'difference',
		'profit'
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
			'trade_id' => 'Id of the trade',
			'state' => 'Is Active or Closed',
			'trailing' => 'Number as percentage',
			'fix_sell' => 'Price where it will be sold',
			'difference' => 'Shows price difference from percentage.',
			'profit' => 'Shows if it would sell now what the profit would be.',
		);

		return $names[$attribute];
	}
}
