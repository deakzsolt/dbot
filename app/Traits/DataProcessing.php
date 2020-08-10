<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 10. 25.
 * Time: 22:08
 */

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait DataProcessing
 * @package App\Traits
 */
trait DataProcessing
{
    use TimeWrapper;

    /**
     * @param     $datas
     * @param int $limit
     *
     * @return array|string
     */
    private function organizePairData($datas, $limit = 999)
    {
        if (count($datas) != 1) {
            $ret = [];
            foreach ($datas as $data) {
                $ret[$data->exchange_id]['timestamp'][] = $data->buckettime;
                $ret[$data->exchange_id]['date'][] = gmdate("j-M-y", $data->buckettime);
                $ret[$data->exchange_id]['low'][] = $data->low;
                $ret[$data->exchange_id]['high'][] = $data->high;
                $ret[$data->exchange_id]['open'][] = $data->open;
                $ret[$data->exchange_id]['close'][] = $data->close;
                $ret[$data->exchange_id]['bid'][] = $data->bid;
                $ret[$data->exchange_id]['ask'][] = $data->ask;
                $ret[$data->exchange_id]['volume'][] = $data->volume;
            } // foreach

            foreach ($ret as $ex => $opt) {
                $ret[$ex]['lastPrice'] = $ret[$ex]['close'][0];
                $ret[$ex]['prevPrice'] = $ret[$ex]['close'][1];
                $ret[$ex]['lastBid'] = $ret[$ex]['bid'][0];
                $ret[$ex]['lastAsk'] = $ret[$ex]['ask'][0];
                foreach ($opt as $key => $rettemmp) {
                    $ret[$ex][$key] = array_reverse($rettemmp);
                    $ret[$ex][$key] = array_slice($ret[$ex][$key], 0, $limit, true);
                }
            } // foreach
            return $ret;
        } else {
            Log::error('There is not enough data to show results.');
            return "There is not enough data to show results.";
        } // if
    }

    /**
     * Returns latest data
     *
     * @param string $pair
     * @param int    $limit
     * @param string $periodSize
     *
     * @return array
     */
    public function getLatestData($pair = 'BTC/USD', $limit = 168, $periodSize = '1m')
    {

        $time = $this->periodSize($periodSize);
        $timeSlice = $time['timeslice'];

        $current_time = time();
        $offset = ($current_time - ($timeSlice * $limit)) - 1;

        $results = DB::select(
            DB::raw(
                "
              SELECT
                exchange_id,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(open AS CHAR) ORDER BY datetime), ',', 1 ) AS `open`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(high AS CHAR) ORDER BY high DESC), ',', 1 ) AS `high`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(low AS CHAR) ORDER BY low), ',', 1 ) AS `low`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(close AS CHAR) ORDER BY datetime DESC), ',', 1 ) AS `close`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY datetime DESC), ',', 1 ) AS `bid`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(ask AS CHAR) ORDER BY datetime DESC), ',', 1 ) AS `ask`,
                SUM(basevolume) AS volume,
                ROUND((CEILING(UNIX_TIMESTAMP(`datetime`) / $timeSlice) * $timeSlice)) AS buckettime
              FROM tickers
              WHERE symbol = '$pair'
              AND UNIX_TIMESTAMP(`datetime`) > ($offset)
              GROUP BY exchange_id, buckettime
              ORDER BY buckettime DESC
          "
            )
        );

        return $this->organizePairData($results);
    }

    /**
     * Returns latest data
     *
     * @param string $pair
     * @param int    $limit
     * @param string $periodSize
     *
     * @return array
     */
    public function getLatestDataByBid($pair = 'BTC/USD', $limit = 168, $periodSize = '1m')
    {

        $time = $this->periodSize($periodSize);
        $timeSlice = $time['timeslice'];

        $current_time = time();
        $offset = ($current_time - ($timeSlice * $limit)) - 1;

        $results = DB::select(
            DB::raw(
                "
              SELECT
                exchange_id,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY datetime), ',', 1 ) AS `open`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY bid DESC), ',', 1 ) AS `high`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY bid), ',', 1 ) AS `low`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY datetime DESC), ',', 1 ) AS `close`,
                SUM(basevolume) AS volume,
                ROUND((CEILING(UNIX_TIMESTAMP(`datetime`) / $timeSlice) * $timeSlice)) AS buckettime,
                round(AVG(bid),11) AS avgbid,
                round(AVG(ask),11) AS avgask,
                AVG(baseVolume) AS avgvolume
              FROM tickers
              WHERE symbol = '$pair'
              AND UNIX_TIMESTAMP(`datetime`) > ($offset)
              GROUP BY exchange_id, buckettime
              ORDER BY buckettime DESC
          "
            )
        );

        return $this->organizePairData($results);
    }
}
