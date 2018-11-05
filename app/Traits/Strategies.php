<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 02.
 * Time: 14:42
 */

namespace App\Traits;

const TRADER_MA_TYPE_SMA = 3;

trait Strategies
{
    /**
     * Returns average price
     * use same amount of data as period
     *
     * @param $data
     * @param $period
     * @param bool $prior
     * @return int|mixed
     */
    private function sma_maker($data, $period, $prior=false)
    {
        $smaArray = trader_sma($data, $period);
        $sma = @array_pop($smaArray) ?? 0;
        $sma_prior = @array_pop($smaArray) ?? 0;
        return ($prior ? $sma_prior : $sma);
    }

    /**
     * Basic Strategy with SMA, Stochastic and RSI
     * this should go with data on 1h
     *
     * @param $data
     * @param bool $indicator
     * @return array|int
     */
    public function strategy_sma_stoch_rsi($data, $indicator=false)
    {
        $price  = array_pop($data['close']);
        $sma150  = $this->sma_maker($data['close'], 150);
        $stoch = trader_stoch($data['high'], $data['low'], $data['close'], 10, 3, TRADER_MA_TYPE_SMA, 3, TRADER_MA_TYPE_SMA);
        $slowk = @array_pop($stoch[0]);
        $slowd = @array_pop($stoch[1]);
        $rsi = @array_pop(trader_rsi ($data['close'], 14));

        $return = array(
            'strategy' => 'sma_stoch_rsi',
            'price' => $price,
            'sma' => $sma150,
            'slowk' => $slowk ?? 0,
            'slowd' => $slowd ?? 0,
            'rsi' => $rsi ?? 0,
            'side' => '',
            'state' => 0
        );

        if ($rsi < 20) {
            $rsiColor = 'green';
        } elseif ($rsi > 80) {
            $rsiColor = 'red';
        } else {
            $rsiColor = 'yellow';
        } // if

        $return['colors'] = array(
            'sma' => $price > $sma150 ? 'green' : 'red',
            'slowk' => $slowk > 70 ? 'green' : 'red',
            'slowd' => $slowk > $slowd ? 'green' : 'red',
            'rsi' =>  $rsiColor
        );

        if ($price > $sma150 && $rsi < 20 && $slowk > 70 && $slowk > $slowd) {
            $return['side'] = 'long';
            $return['state'] = 1;
            return ($indicator ? 1 : $return);
        } // if

        if ($price < $sma150 && $rsi > 80 && $slowk > 70 && $slowk < $slowd) {
            $return['side'] = 'short';
            $return['state'] = -1;
            return ($indicator ? -1 : $return);
        } // if

        return ($indicator ? 0 : $return);
    }

    public function strategy_sma_stoch($data, $indicator=false)
    {
//        TODO create SMA Stochastic strategy
    }
}