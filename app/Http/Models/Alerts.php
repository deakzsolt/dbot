<?php


namespace App\Http\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Alerts
 * @package App\Http\Models
 *
 * @property string $cursor
 * @property string $blockchain
 * @property string $symbol
 * @property string $transaction_id
 * @property string $transaction_type
 * @property string $hash
 * @property string $from_address
 * @property string $from_owner_type
 * @property string $from_owner
 * @property string $to_address
 * @property string $to_owner_type
 * @property string $to_owner
 * @property integer $timestamp
 * @property integer $amount
 * @property integer $amount_usd
 * @property integer $transaction_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Alerts extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'cursor',
		'blockchain',
		'symbol',
		'transaction_id',
		'transaction_type',
		'hash',
		'from_address',
		'from_owner_type',
		'from_owner',
		'to_address',
		'to_owner_type',
		'to_owner',
		'timestamp',
		'amount',
		'amount_usd',
		'transaction_count',
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
			'cursor' => 'Pagination id',
			'blockchain' => 'BlockChain name',
			'symbol' => 'Symbol',
			'transaction_id' => 'Transaction id',
			'transaction_type' => 'Transaction type',
			'hash' => 'Hash',
			'from_address' => 'Owner from address',
			'from_owner_type' => 'From owner type name',
			'to_address' => 'To from address',
			'to_owner_type' => 'To owner type name',
			'timestamp' => 'Timestamp',
			'amount' => 'Amount',
			'amount_usd' => 'Amount in USD',
			'transaction_count' => 'Transaction count',
			'created_at' => 'Created at',
			'updated_at' => 'Updated at'
		);

		return $names[$attribute];
	}
}
