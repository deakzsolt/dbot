<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 15.
 * Time: 17:18
 */

namespace App\Traits;

trait TimeWrapper
{
    /**
     * Returns seconds on expected 1m,1h symbols
     *
     * @param $periodSize
     * @return array
     */
    public function periodSize($periodSize)
    {
        $time = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $periodSize);

        $names = [
            's' => 'second',
            'm' => 'minute',
            'h' => 'hour',
            'd' => 'day',
            'w' => 'week',
        ];
        $name = $names[$time[1]];
        $timescale = ($time[0] == 1) ? '1 ' . $name : "$time[0] {$name}s";

        $secondsPerUnit = [
            's' => 1,
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
            'w' => 604800,
        ];
        $seconds = $time[0] * $secondsPerUnit[$time[1]];

        return [
            'timescale' => $timescale,
            'timeslice' => $seconds ?? 0,
        ];
    }

    /**
     * This generates timestamp sequence based on minutes
     *
     * @param $time
     * @param int $sequence
     * @return array
     */
    public function timeSequence($time,$sequence=5)
    {
        if (strlen($time) === 13) {
            $time = intval($time/1000);
        } // if

        $date = date('Y:m:d',$time);
        $hour = date('H',$time);
        $minutes = date('i',$time);

        $getSequence = (int)($minutes/$sequence)*$sequence;
        $getSequence = strlen($getSequence) == 1 ? '0'.$getSequence : $getSequence;
        $sequenceDateTime = $date.' '.$hour.':'.$getSequence.':00';
        $timestamp = strtotime($sequenceDateTime);

        return array(
            'datetime' => $sequenceDateTime,
            'timestamp' => $timestamp
        );
    }
}
