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
     * @param      $data
     * @param      $period
     * @param bool $prior
     *
     * @return mixed
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
        $sma100  = $this->sma_maker($data['close'], 100);
        $stoch = trader_stoch($data['high'], $data['low'], $data['close'], 8, 3, TRADER_MA_TYPE_SMA, 3, TRADER_MA_TYPE_SMA);
        $slowk = $stoch[0];
        $slowd = $stoch[1];
        $slowk = array_pop($slowk);
        $slowd = array_pop($slowd);
        $rsi = trader_rsi ($data['close'], 3);
        $rsi = array_pop($rsi);
        $return = array(
            'strategy' => 'sma_stoch_rsi',
            'price' => $price,
            'sma' => $sma100,
            'slowk' => $slowk,
            'slowd' => $slowd,
            'rsi' => $rsi,
            'side' => '',
            'state' => 0
        );
        if ($price > $sma100 && $rsi < 20 && $slowk > 70 && $slowk > $slowd) {
            $return['side'] = 'long';
            $return['state'] = 1;
            return ($indicator ? 1 : $return);
        } // if
        if ($price < $sma100 && $rsi > 80 && $slowk > 70 && $slowk < $slowd) {
            $return['side'] = 'short';
            $return['state'] = -1;
            return ($indicator ? -1 : $return);
        } // if
        return ($indicator ? 0 : $return);
    }
}