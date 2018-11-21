<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 21.
 * Time: 20:49
 */

namespace App\Utils;

class Indicators
{

	/**
	 * Average True Range (ATR)
	 * http://www.investopedia.com/articles/trading/08/atr.asp
	 * The idea is to use ATR to identify breakouts, if the price goes higher than
	 * the previous close + ATR, a price breakout has occurred.
	 *
	 * The position is closed when the price goes 1 ATR below the previous close.
	 *
	 * This algorithm uses ATR as a momentum strategy, but the same signal can be used for
	 * a reversion strategy, since ATR doesn't indicate the price direction (like adx below)
	 *
	 * @param null   $data
	 * @param int    $period
	 *
	 * @return int
	 */
	public function atr($data, $period = 14)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		if ($period > count($data['close'])) {
			$period = round(count($data['close']) / 2);
		} // if

		$data2 = $data;

		$current = array_pop($data2['close']); #[count($data['close']) - 1];    // we assume this is current
		$prev_close = array_pop($data2['close']); #[count($data['close']) - 2]; // prior close
		$atr = trader_atr($data['high'], $data['low'], $data['close'], $period);
		$atr = array_pop($atr); #[count($atr)-1]; // pick off the last

		# An upside breakout occurs when the price goes 1 ATR above the previous close
		$upside_signal = ($current - ($prev_close + $atr));

		# A downside breakout occurs when the previous close is 1 ATR above the price
		$downside_signal = ($prev_close - ($current + $atr));

		if ($upside_signal > 0) {
			return 1; // buy
		} elseif ($downside_signal > 0) {
			return -1; // sell
		} // if
		return 0;
	}

	/**
	 * Bollinger Bands
	 *
	 * This algorithm uses the talib Bollinger Bands function to determine entry entry
	 * points for long and sell/short positions.
	 *
	 * When the price breaks out of the upper Bollinger band, a sell or short position
	 * is opened. A long position is opened when the price dips below the lower band.
	 *
	 *
	 * Used to measure the market’s volatility.
	 * They act like mini support and resistance levels.
	 * Bollinger Bounce
	 *
	 * A strategy that relies on the notion that price tends to always return to the middle of the Bollinger bands.
	 * You buy when the price hits the lower Bollinger band.
	 * You sell when the price hits the upper Bollinger band.
	 * Best used in ranging markets.
	 * Bollinger Squeeze
	 *
	 * A strategy that is used to catch breakouts early.
	 * When the Bollinger bands “squeeze”, it means that the market is very quiet, and a breakout is eminent.
	 * Once a breakout occurs, we enter a trade on whatever side the price makes its breakout.
	 *
	 * @param null   $data
	 * @param int    $period
	 *
	 * @return int
	 */
	public function bollingerBands($data, $period = 10, $devup = 2, $devdn = 2)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$data2 = $data;

		#$prev_close = array_pop($data2['close']); #[count($data['close']) - 2]; // prior close
		$current = array_pop($data2['close']); #[count($data['close']) - 1];    // we assume this is current

		# array $real [, integer $timePeriod [, float $nbDevUp [, float $nbDevDn [, integer $mAType ]]]]
		$bbands = trader_bbands($data['close'], $period, $devup, $devdn, 0);
		$upper = $bbands[0];

		#$middle = $bbands[1]; // we'll find a use for you, one day
		$lower = $bbands[2];

		# If price is below the recent lower band
		if ($current <= array_pop($lower)) {
			return 1; // buy long
			# If price is above the recent upper band
		} elseif ($current >= array_pop($upper)) {
			return -1; // sell (or short)
		} else {
			return 0; // notta
		} // if
	}

	/**
	 * Moving Average Crossover Divergence (MACD)
	 *
	 * Moving Average Crossover Divergence (MACD) indicator as a buy/sell signal.
	 * When the MACD signal less than 0, the price is trending down and it's time to sell.
	 * When the MACD signal greater than 0, the price is trending up it's time to buy.
	 *
	 * Used to catch trends early and can also help us spot trend reversals.
	 * It consists of 2 moving averages (1 fast, 1 slow) and vertical lines called a histogram,
	 * which measures the distance between the 2 moving averages.
	 * Contrary to what many people think, the moving average lines are NOT moving averages of the price.
	 * They are moving averages of other moving averages.
	 * MACD’s downfall is its lag because it uses so many moving averages.
	 * One way to use MACD is to wait for the fast line to “cross over” or “cross under” the slow line and
	 * enter the trade accordingly because it signals a new trend.
	 *
	 * @param string $pair
	 * @param null   $data
	 * @param int    $period
	 *
	 * @return int
	 */
	public function macd($data, $period1 = 12, $period2 = 26, $period3 = 9)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		# Create the MACD signal and pass in the three parameters: fast period, slow period, and the signal.
		# we will want to tweak these periods later for now these are fine.
		#  data, fast period, slow period, signal period (2-100000)
		# array $real [, integer $fastPeriod [, integer $slowPeriod [, integer $signalPeriod ]]]
		$macd = trader_macd($data['close'], $period1, $period2, $period3);
		$macd_raw = $macd[0];
		$signal = $macd[1];
		$hist = $macd[2];

		//If not enough Elements for the Function to complete
		if (!$macd || !$macd_raw) {
			return 0;
		} // if

		#$macd = $macd_raw[count($macd_raw)-1] - $signal[count($signal)-1];
		$macd = (array_pop($macd_raw) - array_pop($signal));

		# Close position for the pair when the MACD signal is negative
		if ($macd < 0) {
			return -1;
			# Enter the position for the pair when the MACD signal is positive
		} elseif ($macd > 0) {
			return 1;
		} else {
			return 0;
		} // if
	}

	/**
	 * TODO this needs to be transferred for my code
	 *
	 * @param string $pair
	 * @param null   $data
	 * @param int    $fastPeriod
	 * @param int    $fastMAType
	 * @param int    $slowPeriod
	 * @param int    $slowMAType
	 * @param int    $signalPeriod
	 * @param int    $signalMAType
	 *
	 * @return int
	 *
	 *      MACD indicator with controllable types and tweakable periods.
	 *
	 *      TODO This will be for various backtesting and tests
	 *      all periods are ranges of 2 to 100,000
	 */
//	public function macdext($pair = 'BTC/USD', $data = null, $fastPeriod = 12, $fastMAType = 0, $slowPeriod = 26, $slowMAType = 0, $signalPeriod = 9, $signalMAType = 0)
//	{
//		$fastMAType = $this->ma_type($fastMAType);
//		$slowMAType = $this->ma_type($slowMAType);
//		$signalMAType = $this->ma_type($signalMAType);
//		if (empty($data)) {
//			$data = $this->getRecentData($pair);
//		}
//		# Create the MACD signal and pass in the three parameters: fast period, slow period, and the signal.
//		# we will want to tweak these periods later for now these are fine.
//		$macd = trader_macdext($data['close'], $fastPeriod, $fastMAType, $slowPeriod, $slowMAType, $signalPeriod,
//			$signalMAType);
//		$macd_raw = $macd[0];
//		$signal = $macd[1];
//		$hist = $macd[2];
//		if (!empty($macd)) {
//			$macd = array_pop($macd[0]) - array_pop($macd[1]); #$macd_raw[count($macd_raw)-1] - $signal[count($signal)-1];
//			# Close position for the pair when the MACD signal is negative
//			if ($macd < 0) {
//				return -1;
//				# Enter the position for the pair when the MACD signal is positive
//			} elseif ($macd > 0) {
//				return 1;
//			} else {
//				return 0;
//			}
//		}
//		print_r($macd);
//		return -2;
//	}

	/**
	 * Relative Strength Index indicator (RSI)
	 *
	 * This can be used as a buy/sell signal.
	 * Similar to the stochastic in that it indicates overbought and oversold conditions.
	 * When RSI is above 70, it means that the market is overbought and we should look to sell.
	 * When RSI is below 30, it means that the market is oversold and we should look to buy.
	 * RSI can also be used to confirm trend formations. If you think a trend is forming, wait for
	 * RSI to go above or below 50 (depending on if you’re looking at an uptrend or downtrend) before you enter a trade.
	 *
	 * @param     $data
	 * @param int $period
	 * @param int $low
	 * @param int $high
	 *
	 * @return int|string
	 */
	public function rsi($data, $period = 14, $low = 30, $high = 70)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		#$data2 = $data;
		#$current = array_pop($data2['close']); #$data['close'][count($data['close']) - 1];    // we assume this is current
		#$prev_close = array_pop($data2['close']); #$data['close'][count($data['close']) - 2]; // prior close
		$rsi = trader_rsi($data['close'], $period);
		$rsi = array_pop($rsi);

		# RSI is above 70 and we own, sell
		if ($rsi > $low) {
			return -1;
			# RSI is below 30, buy
		} elseif ($rsi < $high) {
			return 1;
		} else {
			return 0;
		} // if
	}

	/**
	 * Stochastic
	 *
	 * STOCH function to determine entry and exit points.
	 * When the stochastic oscillator dips below 10, the pair is determined to be oversold
	 * and a long position is opened. The position is exited when the indicator rises above 90
	 * because the pair is thought to be overbought.
	 *
	 * Used to indicate overbought and oversold conditions.
	 * When the moving average lines are above 80, it means that the market is overbought and we should look to sell.
	 * When the moving average lines are below 20, it means that the market is oversold and we should look to buy.
	 *
	 * @param     $data
	 * @param int $slowk_matype
	 * @param int $slowd_matype
	 *
	 * @return int|string
	 */
	public function stoch($data, $slowk_matype = 3, $slowd_matype = 3)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		if (empty($data['high'])) {
			return 0;
		} // if

		#$prev_close = $data['close'][count($data['close']) - 2]; // prior close
		#$current = $data['close'][count($data['close']) - 1];    // we assume this is current
		#high,low,close, fastk_period, slowk_period, slowk_matype, slowd_period, slowd_matype
		$stoch = trader_stoch($data['high'], $data['low'], $data['close'], 13, 3, $slowk_matype, 3, $slowd_matype);
		$slowk = $stoch[0];
		$slowd = $stoch[1];
		$slowk = array_pop($slowk); #$slowk[count($slowk) - 1];
		$slowd = array_pop($slowd); #$slowd[count($slowd) - 1];

		#echo "\n(SLOWK: $slowk SLOWD: $slowd)";
		# If either the slowk or slowd are less than 10, the pair is
		# 'oversold,' a long position is opened
		if ($slowk < 10 || $slowd < 10) {
			return 1;
			# If either the slowk or slowd are larger than 90, the pair is
			# 'overbought' and the position is closed.
		} elseif ($slowk > 90 || $slowd > 90) {
			return -1;
		} else {
			return 0;
		} // if
	}

	/**
	 * Fast Stochastic
	 *
	 * @param     $data
	 * @param int $matype
	 *
	 * @return int|string
	 */
	public function stochf($data, $matype = 3)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		if (empty($data['high'])) {
			return 0;
		} // if

		#$prev_close = $data['close'][count($data['close']) - 2]; // prior close
		#$current = $data['close'][count($data['close']) - 1];    // we assume this is current
		#high,low,close, fastk_period, slowk_period, slowk_matype, slowd_period, slowd_matype
		$stoch = trader_stochf($data['high'], $data['low'], $data['close'], 13, 3, $matype);
		$fastk = $stoch[0];
		$fastd = $stoch[1];
		$fastk = array_pop($fastk); #$slowk[count($slowk) - 1];
		$fastd = array_pop($fastd); #$slowd[count($slowd) - 1];

		# If either the slowk or slowd are less than 10, the pair is
		# 'oversold,' a long position is opened
		if ($fastk < 10 || $fastd < 10) {
			return 1;
			# If either the slowk or slowd are larger than 90, the pair is
			# 'overbought' and the position is closed.
		} elseif ($fastk > 90 || $fastd > 90) {
			return -1;
		} else {
			return 0;
		} // if
	}

	/**
	 * Awesome Oscillator
	 *
	 * NO specific TALib function
	 *
	 * created based on calculation here
	 * https://www.tradingview.com/wiki/Awesome_Oscillator_(AO)
	 * AO = SMA(High+Low)/2, 5 Periods) - SMA(High+Low/2, 34 Periods)
	 *
	 * a momentum indicator
	 * This function just watches for zero-line crossover.
	 * using return_raw you can watch for saucers and peaks and will need to
	 * create a strategy for those if you want to use them.
	 *
	 * @param null   $data
	 * @param bool   $return_raw
	 *
	 * @return int|mixed
	 */
	public function awesome_oscillator($data, $return_raw = false)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$data['mid'] = [];
		foreach ($data['high'] as $high_key => $high_alue) {
			$data['mid'][$high_key] = (($data['high'][$high_key] + $data['low'][$high_key]) / 2);
		} // foreach

		$ao_sma_1 = trader_sma($data['mid'], 5);
		$ao_sma_2 = trader_sma($data['mid'], 34);
		array_pop($data['mid']); // take most recent off.
		$ao_sma_3 = trader_sma($data['mid'], 5);
		$ao_sma_4 = trader_sma($data['mid'], 34);

		if ($return_raw) {
			return ($ao_sma_1 - $ao_sma_2); // return the actual values of the oscillator
		} else {
			$ao_prior = (array_pop($ao_sma_3) - array_pop($ao_sma_4)); // last 'tick'
			$ao_now = (array_pop($ao_sma_1) - array_pop($ao_sma_2)); // current 'tick'
			/** Bullish cross */
			if ($ao_prior <= 0 && $ao_now > 0) {
				return 100;
				/** Bearish cross */
			} elseif ($ao_prior >= 0 && $ao_now < 0) {
				return -100;
			} else {
				return 0;
			} // if
		} // if
	}

	/**
	 * Money flow index (MFI)
	 *
	 * @param null   $data
	 * @param int    $period
	 *
	 * @return int
	 */
	public function mfi($data, $period = 14)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$mfi = trader_mfi($data['high'], $data['low'], $data['close'], $data['volume'], $period);
		$mfi = array_pop($mfi); #[count($mfi) - 1];

		if ($mfi > 80) {
			return -1; // overbought
		} elseif ($mfi < 10) {
			return 1;  // underbought
		} else {
			return 0;
		} // if
	}

	/**
	 * On Balance Volume (OBV)
	 *
	 * http://stockcharts.com/school/doku.php?id=chart_school:technical_indicators:on_balance_volume_obv
	 * signal assumption that volume precedes price on confirmation, divergence and breakouts
	 *
	 * use with mfi to confirm
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int
	 */
	public function obv($data, $period = 14)
	{
		if (empty($data)) {
//			TODO get better data flow
//			$data = $this->getRecentData($pair, $period, true, 12); // getting day 'noon' data for last two weeks
		} // if

		$_obv = trader_obv($data['close'], $data['volume']);
		$current_obv = array_pop($_obv); #[count($_obv) - 1];
		$prior_obv = array_pop($_obv); #[count($_obv) - 2];
		$earlier_obv = array_pop($_obv); #[count($_obv) - 3];

		/**
		 *   This forecasts a trend in the last three periods
		 *   TODO: this needs to be tested more, we might need to look closer for crypto currencies
		 */
		if (($current_obv > $prior_obv) && ($prior_obv > $earlier_obv)) {
			return 1; // upwards momentum
		} elseif (($current_obv < $prior_obv) && ($prior_obv < $earlier_obv)) {
			return -1; // downwards momentum
		} else {
			return 0;
		} // if
	}

	/**
	 * Parabolic Stop And Reversal (SAR)
	 *
	 * http://www.babypips.com/school/elementary/common-chart-indicators/parabolic-sar.html
	 *
	 * This indicator is made to spot trend reversals, hence the name Parabolic Stop And Reversal (SAR).
	 * This is the easiest indicator to interpret because it only gives bullish and bearish signals.
	 * When the dots are above the candles, it is a sell signal.
	 * When the dots are below the candles, it is a buy signal.
	 * These are best used in trending markets that consist of long rallies and downturns.
	 * $acceleration=0.02, $maximum=0.02 are tradingview defaults
	 *
	 * @param       $data
	 * @param int   $period
	 * @param float $acceleration
	 * @param float $maximum
	 *
	 * @return int|string
	 */
	public function sar($data, $period = 14, $acceleration = 0.02, $maximum = 0.02)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
//			$data = $this->getRecentData($pair, $period, true, 12); // getting day 'noon' data for last two weeks
		} // if

		// SEE TODO: http://stockcharts.com/school/doku.php?id=chart_school:technical_indicators:parabolic_sar
		# array $high , array $low [, float $acceleration [, float $maximum ]]
		$_sar = trader_sar($data['high'], $data['low'], $acceleration, $maximum);
		$current_sar = array_pop($_sar); #[count($_sar) - 1];
		$prior_sar = array_pop($_sar); #[count($_sar) - 2];
		$earlier_sar = array_pop($_sar); #[count($_sar) - 3];
		$last_high = array_pop($data['high']); #[count($data['high'])-1];
		$last_low = array_pop($data['low']); #[count($data['low'])-1];

		/**
		 *  if the last three SAR points are above the candle (high) then it is a sell signal
		 *  if the last three SAE points are below the candle (low) then is a buy signal
		 */
		if (($current_sar > $last_high) && ($prior_sar > $last_high) && ($earlier_sar > $last_high)) {
			return -1; //sell
		} elseif (($current_sar < $last_low) && ($prior_sar < $last_low) && ($earlier_sar < $last_low)) {
			return 1; // buy
		} else {
			return 0; // hold
		}
	}

	/**
	 * Commodity Channel Index (CCI)
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function cci($data, $period = 14)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		# array $high , array $low , array $close [, integer $timePeriod ]
		$cci = trader_cci($data['high'], $data['low'], $data['close'], $period);
		$cci = array_pop($cci); #[count($cci) - 1];

		if ($cci > 100) {
			return -1; // overbought
		} elseif ($cci < -100) {
			return 1;  // underbought
		} else {
			return 0;
		} // if
	}

	/**
	 * Chande Momentum Oscillator
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function cmo($data, $period = 14)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$cmo = trader_cmo($data['close'], $period);
		$cmo = array_pop($cmo); #[count($cmo) - 1];

		if ($cmo > 50) {
			return -1; // overbought
		} elseif ($cmo < -50) {
			return 1;  // underbought
		} else {
			return 0;
		} // if
	}

	/**
	 * Aroon Oscillator
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function aroonosc($data, $period = 14)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$aroonosc = trader_aroonosc($data['high'], $data['low'], $period);
		$aroonosc = array_pop($aroonosc); #[count($aroonosc) - 1];

		if ($aroonosc < -50) {
			return -1; // overbought
		} elseif ($aroonosc > 50) {
			return 1;  // underbought
		} else {
			return 0;
		} // if
	}

	/**
	 * Average Directional Movement Index (ADX)
	 *
	 * The ADX calculates the potential strength of a trend.
	 * It fluctuates from 0 to 100, with readings below 20 indicating a weak trend and readings above 50 signaling a strong trend.
	 * ADX can be used as confirmation whether the pair could possibly continue in its current trend or not.
	 * ADX can also be used to determine when one should close a trade early. For instance, when ADX starts to slide below 50,
	 * it indicates that the current trend is possibly losing steam.
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function adx($data, $period = 14)
	{
		/**
		 * TODO, this one needs more research for the returns
		 * http://www.investopedia.com/terms/a/adx.asp
		 */
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$adx = trader_adx($data['high'], $data['low'], $data['close'], $period);
		if (empty($adx)) {
			return -9;
		} // if

		$adx = array_pop($adx); #[count($adx) - 1];
		if ($adx > 50) {
			return -1; // overbought
		} elseif ($adx < 20) {
			return 1;  // underbought
		} else {
			return 0;
		} // if
	}

	/**
	 * Price Rate of Change (ROC)
	 *
	 * ROC = [(Close - Close n periods ago) / (Close n periods ago)] * 100
	 * Positive values that are greater than 30 are generally interpreted as indicating overbought conditions,
	 * while negative values lower than negative 30 indicate oversold conditions.
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function roc($data, $period = 14)
	{
		// trader_roc ( array $real [, integer $timePeriod ] )
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$roc = trader_roc($data['close'], $period);
		$roc = array_pop($roc);

		if ($roc < -30) {
			return 1; // oversold
		} elseif ($roc > 30) {
			return -1; // overbought
		} else {
			return 0;
		} // if
	}

	/**
	 *  Williams R% (WILLR)
	 *  %R = (Highest High – Closing Price) / (Highest High – Lowest Low) x -100
	 *  When the indicator produces readings from 0 to -20, this indicates overbought market conditions.
	 *  When readings are -80 to -100, it indicates oversold market conditions.
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function willr($data, $period = 14)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$willr = trader_willr($data['high'], $data['low'], $data['close'], $period);
		$willr = array_pop($willr);

		if ($willr <= -80) {
			return 1; // oversold
		} elseif ($willr >= -20) {
			return -1; // overbought
		} else {
			return 0;
		} // if
	}

	/**
	 * ULTIMATE OSCILLATOR
	 *
	 * BP = Close - Minimum(Low or Prior Close).
	 * TR = Maximum(High or Prior Close)  -  Minimum(Low or Prior Close)
	 *
	 * Average7 = (7-period BP Sum) / (7-period TR Sum)
	 * Average14 = (14-period BP Sum) / (14-period TR Sum)
	 * Average28 = (28-period BP Sum) / (28-period TR Sum)
	 *
	 * UO = 100 x [(4 x Average7)+(2 x Average14)+Average28]/(4+2+1)
	 *
	 *  levels below 30 are deemed to be oversold
	 *  levels above 70 are deemed to be overbought.
	 *
	 * @param     $data
	 * @param int $period1
	 * @param int $period2
	 * @param int $period3
	 *
	 * @return int|string
	 */
	public function ultosc($data, $period1 = 7, $period2 = 14, $period3 = 28)
	{
		//trader_ultosc ( array $high , array $low , array $close [, integer $timePeriod1 [, integer $timePeriod2 [, integer $timePeriod3 ]]] )
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$ultosc = trader_ultosc($data['high'], $data['low'], $data['close'], $period1, $period2, $period3);
		//TODO verify if bug or not, it returned 0 with few data

		if (!$ultosc) {
			return 0;
		} // if

		$ultosc = array_pop($ultosc);
		if ($ultosc <= 30) {
			return 1; // oversold
		} elseif ($ultosc >= 70) {
			return -1; // overbought
		} else {
			return 0;
		} // if
	}

	/**
	 * High-Low index (HLI)
	 *
	 * NO TALib function
	 *
	 * Record High Percent = {New Highs / (New Highs + New Lows)} x 100
	 * High-Low Index = 10-day SMA of Record High Percent
	 *
	 * Readings consistently above 70 usually coincide with a strong uptrend.
	 * Readings consistently below 30 usually coincide with a strong downtrend.
	 *
	 * @param     $data
	 * @param int $period
	 *
	 * @return int|string
	 */
	public function hli($data, $period = 28)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		if (count($data['high']) < $period) {
			return 0;
		} // if

		$rhp = [];
		$total = count($data['high']);

		for ($a = 0; $a < $total; $a++) {
			$slices_high = array_slice($data['high'], $a, $period);
			$slices_low = array_slice($data['low'], $a, $period);
			$high = $total_highs = 0;

			foreach ($slices_high as $slice) {
				$total_highs += ($slice > $high ? 1 : 0); // incr if new high
				$high = ($slice > $high ? $slice : $high); // set new high?
			} // foreach

			$low = $total_lows = 0;
			foreach ($slices_low as $slice) {
				$total_lows += ($slice < $low ? 1 : 0); // incr if new low
				$low = ($slice < $low ? $slice : $low); // set new low
			} // foreach

			// Record High Percent
			$rhp[] = (($total_highs / ($total_highs + $total_lows)) * 100);
		} // for

		$hli = trader_sma($rhp, 10);
		$hli = array_pop($hli);

		if ($hli > 70) {
			return 1; // bullish
		} elseif ($hli < 30) {
			return -1; // bearish
		} else {
			return 0;
		} // if
	}

	/**
	 * Elder ray (ER)
	 * Bull/Bear power
	 *
	 * NO TALib specific function
	 *
	 * Elder uses a 13-day exponential moving average (EMA) to indicate the consensus market value.
	 * Bull Power is calculated by subtracting the 13-day EMA from the day’s high.
	 * Bear Power is derived by subtracting the 13-day EMA from the day’s low.
	 *
	 * @param $data
	 *
	 * @return int|string
	 */
	public function er($data)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$highs = $data['high'];
		$lows = $data['low'];
		$highs_current = array_pop($highs);
		$lows_current = array_pop($lows);
		$macd = trader_macd($data['close'], 12, 26, 9);
		$macd_raw = $macd[0];
		$signal = $macd[1];
		#$hist     = $macd[2];
		//Not enough Data

		if (!$macd_raw || !$signal) {
			return 0;
		} // if

		$macd_current = (array_pop($macd_raw) - array_pop($signal));
		$ema = trader_ema($data['close'], 13);
		$ema_current = array_pop($ema);
		$bull_current = $ema_current - array_pop($data['high']);
		$bear_current = $ema_current - array_pop($data['low']);

		if ($bull_current > 0 && $highs_current > $macd_current) {
			return 1;
		} elseif ($bear_current < 0 && $lows_current < $macd_current) {
			return -1;
		} else {
			return 0;
		} // if
	}

	/**
	 * Market Meanness Index (MMI)
	 * tendency to revert to the mean
	 *
	 * NO TALib specific funciton
	 *
	 * currently moving in our out of a trend?
	 * prevent loss by false trend signals
	 *
	 * if mmi > 75 then not trending
	 * if mmi < 75 then trending
	 *
	 * @param $data
	 *
	 * @return int|string
	 */
	public function mmi($data)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$data_close = [];
		foreach ($data['close'] as $point) {
			$data_close[] = $point;
		} // foreach

		$nl = $nh = 0;
		$len = count($data_close);
		$median = (array_sum($data_close) / $len);

		for ($a = 0; $a < $len; $a++) {
			if ($data_close[$a] > $median && $data_close[$a] > @$data_close[$a - 1]) {
				$nl++;
			} elseif ($data_close[$a] < $median && $data_close[$a] < @$data_close[$a - 1]) {
				$nh++;
			} // if
		} // for

		$mmi = 100. * ($nl + $nh) / ($len - 1);

		if ($mmi < 75) {
			return 1;
		} // if

		if ($mmi > 75) {
			return -1;
		} // if
		return 0;
	}

	/**
	 * Hilbert Transform - Sinewave (HTS)
	 *
	 * negative numbers = uptrend
	 * positive numbers = downtrend
	 *
	 * If leadSine crosses over DCSine then buy
	 * If leadSine crosses under DCSine then sell
	 *
	 * TODO check this as it is from bowhead
	 * This is correct to the best of my knowledge, the TAlib funcitons are a little
	 * different than the Mesa one I think.
	 * If this is incorrect, please let me know.
	 *
	 * @param      $data
	 * @param bool $trend
	 *
	 * @return int|string
	 */
	public function hts($data, $trend = false)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // fi

		$hts = trader_ht_sine($data['open'], $data['close']);
		$dcsine = array_pop($hts[1]);
		$p_dcsine = array_pop($hts[1]);
		// leadsine is the first one it looks like.
		$leadsine = array_pop($hts[0]);
		$p_leadsine = array_pop($hts[0]);

		if ($trend) {
			/** if the last two sets of both are negative */
			if ($dcsine < 0 && $p_dcsine < 0 && $leadsine < 0 && $p_leadsine < 0) {
				return 1; // uptrend
			} // if

			/** if the last two sets of both are positive */
			if ($dcsine > 0 && $p_dcsine > 0 && $leadsine > 0 && $p_leadsine > 0) {
				return -1; // downtrend
			} // if
			return 0;
		} // if

		/** WE ARE NOT ASKING FOR THE TREND, RETURN A SIGNAL */
		if ($leadsine > $dcsine && $p_leadsine <= $p_dcsine) {
			return 1; // buy
		} // if

		if ($leadsine < $dcsine && $p_leadsine >= $p_dcsine) {
			return -1; // sell
		} // if
		return 0;
	}

	/**
	 * Hilbert Transform - Instantaneous Trendline (HTL)
	 * WMA(4)
	 *
	 * if WMA(4) < htl for five periods then in downtrend (sell in trend mode)
	 * if WMA(4) > htl for five periods then in uptrend   (buy in trend mode)
	 *
	 * if price is 1.5% more than trendline, then  declare a trend
	 * (WMA(4)-trendline)/trendline >= 0.15 then trend = 1
	 *
	 * @param $data
	 *
	 * @return int|string
	 */
	public function htl($data)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$declared = $uptrend = $downtrend = 0;
		$a_htl = $a_wma4 = [];
		$htl = trader_ht_trendline($data['close']);
		$wma4 = trader_wma($data['close'], 4);

		for ($a = 0; $a < 5; $a++) {
			$a_htl[$a] = array_pop($htl);
			$a_wma4[$a] = array_pop($wma4);
			$uptrend += ($a_wma4[$a] > $a_htl[$a] ? 1 : 0);
			$downtrend += ($a_wma4[$a] < $a_htl[$a] ? 1 : 0);
			$declared = (($a_wma4[$a] - $a_htl[$a]) / $a_htl[$a]);
		} // for

		if ($uptrend || $declared >= 0.15) {
			return 1;
		} // if

		if ($downtrend || $declared <= 0.15) {
			return -1;
		} // if
		return 0;
	}

	/**
	 * Hilbert Transform - Trend vs Cycle Mode (HTTC)
	 *
	 * @param      $data
	 * @param bool $numperiods
	 *
	 * @return int|string
	 */
	public function httc($data, $numperiods = false)
	{
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$a_htm = trader_ht_trendmode($data['close']);
		$htm = array_pop($a_htm);

		/**
		 *  We can return the number of periods we have been
		 *  in either a trend or a cycle by calling this again with
		 *  $numperiods == true
		 */
		if ($numperiods) {
			$nump = 1;
			$test = $htm;
			for ($b = 0; $b < count($a_htm); $b++) {
				$test = array_pop($a_htm);
				if ($test == $htm) {
					$nump++;
				} else {
					break;
				} // if
			} // for
			return $nump;
		} // if

		/**
		 *  Otherwise we just return if we are in a trend or not.
		 */
		if ($htm == 1) {
			return 1; // we are in a trending mode
		} // if
		return 0; // we are cycling.
	}

	/**
	 * Stochastic and relative strength index
	 *
	 * above .80 is considered overbought
	 * below .20 is considered oversold
	 * uptrend when consistently above .50
	 * downtrend when consistently below .50
	 *
	 * @param      $data
	 * @param int  $period
	 * @param bool $trend
	 * @param int  $trend_period
	 *
	 * @return int|string
	 */
	public function stochrsi($data, $period = 14, $trend = false, $trend_period = 5)
	{
		// trader_stochrsi ( array $real [, integer $timePeriod [, integer $fastK_Period [, integer $fastD_Period [, integer $fastD_MAType ]]]] )
		if (empty($data)) {
			return "The data is empty! Provide some data";
		} // if

		$stochrsi_trend = $stochrsi = trader_stochrsi($data['close'], $period);
		$stochrsi = array_pop($stochrsi);

		/**
		 *  Lets determine if there is a trend over period 5
		 */
		if ($trend) {
			$trending = 0;
			$parts = [];
			for ($a = 0; $a < $trend_period; $a++) {
				$parts[] = array_pop($stochrsi_trend);
			} // for

			foreach ($parts as $part) {
				$trending += ($part >= 0.5 ? 1 : -1);
			} // foreach

			if ($trending == 5) {
				return 1;
			} // if

			if ($trending == -5) {
				return -1;
			} // if
			return 0;
			/**
			 *  or, just see if we have overbought/oversold
			 */
		} else {
			if ($stochrsi < 0.2) {
				return 1; // oversold
			} elseif ($stochrsi > 0.8) {
				return -1; // overbought
			} else {
				return 0;
			} // if
		} // if
	}
}