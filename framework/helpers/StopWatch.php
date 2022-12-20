<?php
namespace bin\helpers;

class StopWatch
{
    const DECIMALS        = 3;
    const DEC_POINT       = ',';
    const THOUSANDS_SEP   = '.';

    private static $fTimeStart = 0.00;
    private static $fTotal = 0.00;
    private static $oTimezone;

    public static function setTimeZone($sTimezone='UTC')
    {
        $oRetval = null;
        try {
            $oRetval   = new \dateTime();// null,new \DateTimeZone($sTimezone)
            $oRetval->setTimezone(new \DateTimezone($sTimezone));
            self::$oTimezone = $oRetval;
        } catch(Exception $e) {
            echo $e->getMessage().'<br />';
        }
        return $oRetval->getOffset();
    }

    public static function startTime()
    {
        return \microtime(true);
    }

    public static function start()
    {
        self::$fTimeStart = (float)self::startTime();
        self::$fTotal     = 0.00;
        $sYear = date("Ymd");
        $dateTime = new \DateTime($sYear, new \DateTimeZone('Europe/Amsterdam'));
    }

    public static function endTime()
    {
        return \microtime(true);
    }

    public static function stop()
    {
        $fTimeEnd = (float)self::endtime()-(float)self::$fTimeStart;
        return \number_format($fTimeEnd, self::DECIMALS, self::DEC_POINT,self::THOUSANDS_SEP)."";
    }
}
