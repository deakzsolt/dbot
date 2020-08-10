<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Options
 * @package App\Models
 *
 * @property int $id
 * @property string $item
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Options extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'item',
        'value'
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
            'item' => 'Option Name',
            'value' => 'Option values',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        );

        return $names[$attribute];
    }
}
