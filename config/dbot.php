<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 08.
 * Time: 21:50
 */

return [
    'type' => array(

        /* Simple Moving Average, SMA, TRADER_MA_TYPE_SMA */
        'sma'   => 3,

        /* Exponential Moving Average, EMA, TRADER_MA_TYPE_EMA */
        'ema'   => 3,

        /* Weighted Moving Average, WMA, TRADER_MA_TYPE_WMA */
        'wma'   => 3,

        /* Double Exponential Moving, DEMA, Average TRADER_MA_TYPE_DEMA */
        'dema'  => 3,

        /* Triple Exponential Moving Average, TEMA, TRADER_MA_TYPE_TEMA */
        'tema'  => 3,

        /* Triangular Moving Average, TRIMA, TRADER_MA_TYPE_TRIMA */
        'trima' => 3,

        /* Kaufman's Adaptive Moving Average, KAMA, TRADER_MA_TYPE_KAMA */
        'kama'  => 3,

        /* The Mother of Adaptive Moving Average, MAMA, TRADER_MA_TYPE_MAMA */
        'mama'  => 3,

        /* The Triple Exponential Moving Average, T3, TRADER_MA_TYPE_T3 */
        't3'    => 3
    ),

    'exchanges' => array(
        'default' => 'poloniex',
        'data_import' => [
            'poloniex'
        ],
        'trade' => 0
    )
];