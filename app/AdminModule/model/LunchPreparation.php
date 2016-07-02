<?php

namespace App\AdminModule\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

/**
 * Model starající se o tabulku person  
 */
class LunchPreparation extends Table
{

  protected $tableName = 'lunch_preparation';
 
  public function deleteLunchPosition($lunch_id, $position)	{  	  
  	  $this->getTable()
	       ->where('lunch_id = ? AND position = ?', $lunch_id, $position)
	       ->delete();
  }

  public function deleteDayPreparations($lunch_id)	{  	  
  	  $this->getTable()
	       ->where('lunch_id = ?', $lunch_id)
	       ->delete();
  }
  	
  public function insert($lunch_id, $preparation_id, $position)	{                                
    $pos = $this->findBy(array('lunch_id' => $lunch_id, 
                               'position' => $position));
    
    // v daném dni a na dané pozici již nějaká preparce je, tak se změní                               
    if($pos->count() > 0) {
       $this->getTable()
            ->where('lunch_id', $lunch_id)
            ->where('position', $position)
            ->update(array('preparation_id' => $preparation_id));
    }
    // jinak se vloží
    else {
        $data['lunch_id'] = $lunch_id;
        $data['preparation_id'] = $preparation_id;
        $data['position'] = $position;
        
    	return $this->getTable()
	                ->insert($data);
    }
  }
}