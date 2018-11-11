<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 02.
 * Time: 14:42
 */

namespace App\Traits;

trait Strategies
{
    public $strategyNames = array(
        'sma_stoch_rsi',
        'sma_stoch',
        'ema_stoch_rsi'
    );


    /**
     * Basic Strategy with SMA, Stochastic and RSI
     * this should go with data on 1h
     *
     * @param $data
     * @param bool $indicator
     * @return array|int
     */
    public function strategy_sma_stoch_rsi($data, $indicator = false)
    {
        $price = array_pop($data['close']);
        $sma = @array_pop(trader_sma($data['close'], 150)) ?? 0;
        $ema = @array_pop($this->ema($data['close'], 150)) ?? 0;
        $stoch = trader_stoch($data['high'], $data['low'], $data['close'], 14, 3, config('dbot.type.sma'), 3, config('dbot.type.sma'));
        $slowk = @array_pop($stoch[0]);
        $slowd = @array_pop($stoch[1]);
        $rsi = @array_pop(trader_rsi($data['close'], 14));

//        TODO EMA is here for testing only remove when we have more information

        $return = array(
            'strategy' => 'sma_stoch_rsi',
            'price' => $price,
            'sma' => $sma,
            'ema' => $ema,
            'slowk' => $slowk ?? 0,
            'slowd' => $slowd ?? 0,
            'rsi' => $rsi ?? 0,
            'side' => '',
            'state' => 0
        );

        if ($rsi < 30) {
            $rsiColor = 'green';
        } elseif ($rsi > 70) {
            $rsiColor = 'red';
        } else {
            $rsiColor = 'white';
        } // if

        if ($slowk < 30) {
            $slowkColor = 'green';
        } elseif ($slowk > 70) {
            $slowkColor = 'red';
        } else {
            $slowkColor = 'white';
        } // if

        $return['colors'] = array(
            'sma' => $price > $sma ? 'green' : 'red',
            'slowk' => $slowkColor,
            'slowd' => $slowk > $slowd ? 'green' : 'red',
            'rsi' => $rsiColor
        );

        if ($price > $sma && $rsi < 30 && $slowk < 30 && $slowk > $slowd) {
            $return['side'] = 'long';
            $return['state'] = 1;
            return ($indicator ? 1 : $return);
        } // if

        if ($price < $sma && $rsi > 70 && $slowk > 70 && $slowk < $slowd) {
            $return['side'] = 'short';
            $return['state'] = -1;
            return ($indicator ? -1 : $return);
        } // if

        return ($indicator ? 0 : $return);
    }

    /*
     * Basic Strategy with SMA and Stochastic
     * SELF NOTE: test this on 1h for daily trading
     *
     * @param $data
     * @param bool $indicator
     * @return array|int
     */
    public function strategy_sma_stoch($data, $indicator = false)
    {
        $smoothness = config('dbot.type.sma');

        /* Get latest price */
        $price = array_pop($data['close']);

        $sma = @array_pop(trader_sma($data['close'], 150)) ?? 0;
        $stoch = trader_stoch(
            $data['high'],
            $data['low'],
            $data['close'],
            14,
            3,
            $smoothness,
            3,
            $smoothness
        );
        $slowk = @array_pop($stoch[0]);
        $slowd = @array_pop($stoch[1]);

        $return = array(
            'strategy' => 'sma_stoch',
            'price' => $price,
            'sma' => $sma,
            'slowk' => $slowk ?? 0,
            'slowd' => $slowd ?? 0,
            'side' => '',
            'state' => 0
        );

        if ($slowk < 30) {
            $slowkColor = 'green';
        } elseif ($slowk > 70) {
            $slowkColor = 'red';
        } else {
            $slowkColor = 'white';
        } // if

        $return['colors'] = array(
            'sma' => $price > $sma ? 'green' : 'red',
            'slowk' => $slowkColor,
            'slowd' => $slowk > $slowd ? 'green' : 'red'
        );

        if ($price > $sma && $slowk < 30 && $slowk > $slowd) {
            $return['side'] = 'long';
            $return['state'] = 1;
            return ($indicator ? 1 : $return);
        } // if

        if ($price < $sma && $slowk > 70 && $slowk < $slowd) {
            $return['side'] = 'short';
            $return['state'] = -1;
            return ($indicator ? -1 : $return);
        } // if

        return ($indicator ? 0 : $return);
    }

    public function strategy_ema_stoch_rsi($data, $indicator = false)
    {
//        TODO create EMA, Stochastic, RSI strategy
        /*
         * Note the EMA is much faster then SMA and it might not be good as it follows quicker the price movement.
         * This strategy should be tested out on long run.
         */
    }

    /* trader_ema in wrong calculate value
    this return just simple moving avrage
    for get ema correct use this code
    $number is data array and $n is number of period
    example:
    $number[0]    => last value
    $number[n]    =>first value */

    /**
     * Exponential Moving Average
     * as the trader_ema returns the same result as sma we need this custom calculation
     *
     * @param array $numbers
     * @param int $n
     * @return array
     */
    function ema(array $numbers, int $n): array
    {
        $numbers = array_reverse($numbers);
        $m = count($numbers);
        $a = 2 / ($n + 1);
        $EMA = [];

        // Start off by seeding with the first data point
        $EMA[] = $numbers[0];

        // Each day after: EMAtoday = α⋅xtoday + (1-α)EMAyesterday
        for ($i = 1; $i < $m; $i++) {
            $EMA[] = ($a * $numbers[$i]) + ((1 - $a) * $EMA[$i - 1]);
        }
        $EMA = array_reverse($EMA);
        return $EMA;
    }
}