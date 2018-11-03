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
    public function organizePairData($datas, $limit=999)
    {
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

    public function getLatestData($pair='BTC/USD', $limit=168, $periodSize='1m') {
        $timeslice = 60;
        switch($periodSize) {
            case '1m':
                $timescale = '1 minute';
                $timeslice = 60;
                break;
            case '5m':
                $timescale = '5 minutes';
                $timeslice = 300;
                break;
            case '10m':
                $timescale = '10 minutes';
                $timeslice = 600;
                break;
            case '15m':
                $timescale = '15 minutes';
                $timeslice = 900;
                break;
            case '30m':
                $timescale = '30 minutes';
                $timeslice = 1800;
                break;
            case '1h':
                $timescale = '1 hour';
                $timeslice = 3600;
                break;
            case '4h':
                $timescale = '4 hours';
                $timeslice = 14400;
                break;
            case '1d':
                $timescale = '1 day';
                $timeslice = 86400;
                break;
            case '1w':
                $timescale = '1 week';
                $timeslice = 604800;
                break;
        }
        $current_time = time();

        $offset = ($current_time - ($timeslice * $limit)) -1;

        $results = DB::select(DB::raw("
              SELECT 
                exchange_id,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY created_at), ',', 1 ) AS `open`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY bid DESC), ',', 1 ) AS `high`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY bid), ',', 1 ) AS `low`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(bid AS CHAR) ORDER BY created_at DESC), ',', 1 ) AS `close`,
                SUM(basevolume) AS volume,
                ROUND((CEILING(UNIX_TIMESTAMP(`created_at`) / $timeslice) * $timeslice)) AS buckettime,
                round(AVG(bid),11) AS avgbid,
                round(AVG(ask),11) AS avgask,
                AVG(baseVolume) AS avgvolume
              FROM tickers
              WHERE symbol = '$pair'
              AND UNIX_TIMESTAMP(`created_at`) > ($offset)
              GROUP BY exchange_id, buckettime 
              ORDER BY buckettime DESC
          "));

        return $this->organizePairData($results);
    }
}