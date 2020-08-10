<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 02.
 * Time: 14:42
 */

namespace App\Utils;

class Strategies
{

    /**
     * @var Indicators
     */
    private $indicators;

    /**
     * Strategies constructor.
     *
     * @param Indicators $indicators
     */
    public function __construct(Indicators $indicators)
    {
        $this->indicators = $indicators;
    }

    public $strategyNames = [
        'sma_stoch_rsi',
        'sma_stoch',
        'ema_stoch_rsi',
        'strategy_trailing_sar',
        'strategy_rsi',
    ];

    /**
     * Basic Strategy with SMA, Stochastic and RSI
     * this should go with data on 1h
     *
     * @param      $data
     * @param bool $indicator
     *
     * @return array|int
     */
    public function strategy_sma_stoch_rsi($data, $indicator = false)
    {
        $price = array_pop($data['close']);

        $sma = @array_pop(trader_sma($data['close'], 150)) ?? 0;
        $ema = @array_pop($this->ema($data['close'], 150)) ?? 0;

        $smoothness = config('dbot.type.sma');
        $stoch = trader_stoch($data['high'], $data['low'], $data['close'], 14, 3, $smoothness, 3, $smoothness);
        $slowk = @array_pop($stoch[0]);
        $slowd = @array_pop($stoch[1]);

        $rsi = @array_pop(trader_rsi($data['close'], 14));

        //        TODO EMA is here for testing only remove when we have more information

        $return = [
            'strategy' => 'sma_stoch_rsi',
            'price'    => $price,
            'sma'      => $sma,
            'ema'      => $ema,
            'slowk'    => $slowk ?? 0,
            'slowd'    => $slowd ?? 0,
            'rsi'      => $rsi ?? 0,
            'side'     => '',
            'state'    => 0,
        ];

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

        $return['colors'] = [
            'sma'   => $price > $sma ? 'green' : 'red',
            'slowk' => $slowkColor,
            'slowd' => $slowk > $slowd ? 'green' : 'red',
            'rsi'   => $rsiColor,
        ];

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
        $stoch = trader_stoch($data['high'], $data['low'], $data['close'], 14, 3, $smoothness, 3, $smoothness);
        $slowk = @array_pop($stoch[0]);
        $slowd = @array_pop($stoch[1]);

        $return = [
            'strategy' => 'sma_stoch',
            'price'    => $price,
            'sma'      => $sma,
            'slowk'    => $slowk ?? 0,
            'slowd'    => $slowd ?? 0,
            'side'     => '',
            'state'    => 0,
        ];

        if ($slowk < 30) {
            $slowkColor = 'green';
        } elseif ($slowk > 70) {
            $slowkColor = 'red';
        } else {
            $slowkColor = 'white';
        } // if

        $return['colors'] = [
            'sma'   => $price > $sma ? 'green' : 'red',
            'slowk' => $slowkColor,
            'slowd' => $slowk > $slowd ? 'green' : 'red',
        ];

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
     * @param int   $n
     *
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

    public function strategy_trailing_sar()
    {
        // TODO move here from Scalper when testing done
    }

    /**
     * Strategy SAR with Stochastic and Fast Stochastic
     * this is now only for testing not final!
     *
     * @param $data
     *
     * @return int
     */
    public function sar_stoch(array $data)
    {
        $sar = $this->indicators->sar($data);
        $stoch = $this->indicators->stoch($data);
        $stochf = $this->indicators->stochf($data);

        if ($sar == -1 && ($stoch == -1 || $stochf == -1)) {
            return -1;
        } elseif ($sar == 1 && ($stoch == 1 || $stochf == 1)) {
            return 1;
        }
        return 0;
    }

    /**
     * Strategy Stochastic with ADX
     *
     * @param $data
     *
     * @return int
     */
    public function dbotStochAdx(array $data)
    {
        $stochastic = $this->indicators->dbotStochastic($data, 14, 3, 3, 14, 3);
        $adx = $this->indicators->dbotAdx($data, 10);

        if ($stochastic == 1 && $adx == 1) {
            return 1;
        } elseif ($stochastic == -1 && $adx == 1) {
            return -1;
        } // if
        return 0;
    }

    public function strategy_rsi(array $data, $low = 30, $high = 70)
    {

        $rsi = trader_rsi($data['close'], 14);

        if ($rsi && count($rsi) > 2) {

            $intactRsi = $rsi;
            $latestRsi = array_pop($rsi);
            $previousRsi = array_pop($rsi);

            $isUp = $previousRsi > $high && $latestRsi < $high;
            $isDown = $previousRsi < $low && $latestRsi > $low;

            return [
                'rsi'         => $intactRsi,
                'previousRsi' => $previousRsi,
                'latestRsi'   => $latestRsi,
                'isUp'        => $isUp,
                'isDown'      => $isDown,
            ];
        } else {
            return "There is not enough data and RSI fails to calculate!";
        } // if
    }
}
