<?php

namespace App\AdminModule\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

/**
 * Model starající se o tabulku person  
 */
class Lunch extends Table
{
  protected $tableName = 'lunch';
  private $days = array("monday" => "Po", 
                        "tuesday" => "Út",
                        "wednesday" => "St",
                        "thursday" => "Čt",
                        "friday" => "Pá");

  public function insert($date, $nocook_flag = 0)	{
  	return $this->getTable()
                ->insert(array('lunch_date' => $date,
                               'nocook' => $nocook_flag));  
  }
  
  public function delete($lunch_id)	{
  	  return $this->getTable()
	              ->where('id = ?', $lunch_id)
                  ->delete();
  
  }
  
  public function getAbbrFromDate($date) {
      return strtolower(date("l", strtotime($date)));
  }
  
  public function getWeekDayDate($day_name, $offset = 0) {
      if(date("l") == "Sunday")
        $offset--; // úprava, aby týden začínal pondělkem
      return date("Y-m-d", strtotime("{$day_name} this week {$offset} week"));
  }

  public function strtotime($string) {
      $timestamp = strtotime($string);
      if(date("l") == "Sunday")
        $timestamp -= 604800; // úprava, aby týden začínal pondělkem

      return $timestamp;
  }
  
  public function getWeekDates($offset = 0) {
      $dates = array();
      $days = array("monday" => $this->getWeekDayDate("monday", $offset),
                    "tuesday" => $this->getWeekDayDate("tuesday", $offset),
                    "wednesday" => $this->getWeekDayDate("wednesday", $offset),
                    "thursday" => $this->getWeekDayDate("thursday", $offset),
                    "friday" => $this->getWeekDayDate("friday", $offset));

      foreach($days as $day => $date) {
          $dates[] = $date;
      }
      
      return $dates;
  }
  
  public function getWeekLunchs($offset = 0) {
      $lunch = array();
      /*
      $lunch = array('monday - friday' => array('abbr' => 'Po - Pá',
                                                'date' => DateTime(d-m-Y),
                                                'id' => '1 - xxx',
                                                'name' => 'monday - friday',
                                                'nocook' => (0/1),
                                                'preparation' = array('1 - 4' => 'subji - halva')
                                               )
                    );
      */
        foreach($this->days as $day => $abbr) {
            $lunch[$day]['id'] = "";
            $lunch[$day]['name'] = $day;
            $lunch[$day]['abbr'] = $abbr;
            $lunch[$day]['preparation'] = array();
            $lunch[$day]['nocook'] = false;
            $lunch[$day]['date'] = $this->getWeekDayDate($day, $offset);
            for($i = 1; $i <= 4; $i++) {
                 $lunch[$day]['preparation'][$i] = "";
            }
        }
        
        
        $lunchs = $this->findAll()
                       ->where("lunch_date", $this->getWeekDates($offset));
                       
        foreach ($lunchs as $lunch_row) {
            $day = strtolower(date('l', strtotime($lunch_row->lunch_date)));
            $lunch[$day]['id'] = $lunch_row->id;
            $lunch[$day]['date'] = $lunch_row->lunch_date;
            $lunch[$day]['nocook'] = $lunch_row->nocook;
            $preparations = $lunch_row->related("lunch_preparation")
                                      ->order('position');
                               
            foreach($preparations as $preparation) {
              $lunch[$day]['preparation'][$preparation->position] = $preparation->preparation->title;
            }
        }
        return $lunch;
    }
}