<?php

namespace App\AdminModule\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

class Date extends Nette\Object    
{
    function dates_month($month, $year)  {
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dates_month = array();

        for($i=1; $i <= $num; $i++) {
            $mktime = mktime(0, 0, 0, $month, $i, $year);
            $date = date("Y-m-d", $mktime);
            $dates_month[$date] = $mktime;
        }
        
        return $dates_month;
    }
}