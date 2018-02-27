<?php

namespace Swef\Base;

class SwefMoment {

    // Timespaces for the same moment with different timezone offsets

    private $clt;               // Client (using javascript timezone offset)
    private $dat;               // Data   (using timezone SWEF_DATETIME_TZ_DATA)
    private $gmt;               // GMT    (using timezone SWEF_DATETIME_TZ_GMT)
    private $srv;               // Server (using PHP default timezone)

    public function __construct ($timestamp=null) {
        if ($timestamp) {
            $ts                 = intval ($timestamp);
        }
        else {
            $ts                 = time ();
        }
        $this->gmt              = new \DateTime ('@'.$ts);
        $this->gmt->setTimezone (new \DateTimeZone(SWEF_DATETIME_TZ_GMT));
        $this->srv              = new \DateTime ('@'.$ts);
        $this->clt              = new \DateTime ('@'.$ts);
        if (array_key_exists(SWEF_COOKIE_TZOM,$_COOKIE)) {
            $this->clt          = \Swef\Bespoke\Moment::dateTimeWithOffset (
                $this->clt, $_COOKIE[SWEF_COOKIE_TZOM]
            );
        }
    }

    public function __destruct ( ) {
    }

    public function client ($format=SWEF_FORMAT_8601) {
        return $this->clt->format ($format);
    }

    public function gmt ($format=SWEF_FORMAT_8601) {
        return $this->gmt->format ($format);
    }

    public function server ($format=SWEF_FORMAT_8601) {
        return $this->srv->format ($format);
    }

    public function unix ( ) {
        return $this->srv->getTimestamp ();
    }


    // Static functions

    public static function dateTimeWithOffset ($datetime,$minutesJS) {
        // Datetime for the same instant with a time zone offset
        // without needing to know the timezone string
        if ($minutesJS<0) {
            $datetime->add(new \DateInterval('PT'.(0-$minutesJS).'M'));
        }
        else {
            $datetime->sub(new \DateInterval('PT'.(0+$minutesJS).'M'));
        }
        $format     = SWEF_FORMAT_8601_ZONELESS;
        $format    .= \Swef\Bespoke\Moment::offsetJSMinsToISO8601 ($minutesJS);
        return new \DateTime ($datetime->format($format));
    }

    public static function offsetJSMinsToISO8601 ($minutesJS) {
        // Convert JS (-mins) to ISO8601 format (+hh:mm:ss)
        if (is_numeric($minutesJS)) {
            $m      = $minutesJS;
        }
        else {
            $m      = 0;
        }
/*
    Get nearest 30-minute standard-formatted local time offset
    SIGN REVERSAL:
        Reported javacript offset  = UTC - Local time
        Standard ISO8601 offset    = Local time - UTC
    For example:
            -330 -> +05:30 (eg. India)
            +180 -> -03:00 (eg. Brazil)
    https://en.wikipedia.org/wiki/ISO_8601#Time_offsets_from_UTC
*/
        $s          = ($m>0) - ($m<0);
        $m          = round (30*round($s*$m/30,0),0);
        if ($s>0) {
            $s      = '-';
        }
        else {
            $s      = '+';
        }
        $r          = str_pad (\bcmod($m,60),2,'0',STR_PAD_LEFT);
        $l          = bcdiv   (\bcsub($m,$r),60);
        return $s.str_pad($l.':'.$r,5,'0',STR_PAD_LEFT);
    }

}

?>
