<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 10. 25.
 * Time: 22:08
 */

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait DataProcessing
{

    /**
     * @param $datas
     *
     * @return array
     */
    private function organizePairData($datas, $limit=999)
    {
        // TODO refactor this to normal response
        $ret = array();
        foreach ($datas as $data) {
            $ret[$data->exchange_id]['timestamp'][]   = $data->buckettime;
            $ret[$data->exchange_id]['date'][]   = gmdate("j-M-y", $data->buckettime);
            $ret[$data->exchange_id]['low'][]    = $data->low;
            $ret[$data->exchange_id]['high'][]   = $data->high;
            $ret[$data->exchange_id]['open'][]   = $data->open;
            $ret[$data->exchange_id]['close'][]  = $data->close;
            $ret[$data->exchange_id]['volume'][] = $data->volume;
        }
        foreach($ret as $ex => $opt) {
            foreach ($opt as $key => $rettemmp) {
                $ret[$ex][$key] = array_reverse($rettemmp);
                $ret[$ex][$key] = array_slice($ret[$ex][$key], 0, $limit, true);
            }
        }
        return $ret;
    }

    /**
     * Returns seconds on expected 1m,1h symbols
     *
     * @param $periodSize
     * @return array
     */
    private function periodSize($periodSize) {

        $secondsPerUnit = array(
            's' => 1,
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
            'w' => 604800
        );

        $time = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$periodSize);

        $seconds = $time[0]*$secondsPerUnit[$time[1]];

//        TODO add in timescale for pgsql
        return array(
            'timescale' => 0,
            'timeslice' => $seconds ?? 0
        );
    }

    /**
     * Returns latest data
     *
     * @param string $pair
     * @param int $limit
     * @param string $periodSize
     * @return array
     */
    public function getLatestData($pair='BTC/USD', $limit=168, $periodSize='1m') {

        $time = $this->periodSize($periodSize);
        $timeSlice = $time['timeslice'];

        $current_time = time();
        $offset = ($current_time - ($timeSlice * $limit)) -1;

        $results = DB::select(DB::raw("
              SELECT
                exchange_id,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(open AS CHAR) ORDER BY datetime), ',', 1 ) AS `open`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(high AS CHAR) ORDER BY high DESC), ',', 1 ) AS `high`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(low AS CHAR) ORDER BY low), ',', 1 ) AS `low`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(close AS CHAR) ORDER BY datetime DESC), ',', 1 ) AS `close`,
                SUM(basevolume) AS volume,
                ROUND((CEILING(UNIX_TIMESTAMP(`datetime`) / $timeSlice) * $timeSlice)) AS buckettime
              FROM tickers
              WHERE symbol = '$pair'
              AND UNIX_TIMESTAMP(`datetime`) > ($offset)
              GROUP BY exchange_id, buckettime
              ORDER BY buckettime DESC
          "));

        return $this->organizePairData($results);
    }

    /**
     * Returns latest data
     *
     * @param string $pair
     * @param int $limit
     * @param string $periodSize
     * @return array
     */
    public function getLatestDataByBid($pair='BTC/USD', $limit=168, $periodSize='1m') {

        $time = $this->periodSize($periodSize);
        $timeSlice = $time['timeslice'];

        $current_time = time();
        $offset = ($current_time - ($timeSlice * $limit)) -1;

        $results = DB::select(DB::raw("
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
          "));

        return $this->organizePairData($results);
    }
}