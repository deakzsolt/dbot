<?php
/**
 * Created by PhpStorm.
 * User: deakzsolt
 * Date: 2018. 11. 15.
 * Time: 17:18
 */

namespace App\Traits;

/**
 * Trait TimeWrapper
 * @package App\Traits
 */
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
     * This generates timestamp sequence based on minutes
     *
     * @param $time
     * @param int $sequence
     * @return array
     */
    public function timeSequence($time,$sequence=5)
    {
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
