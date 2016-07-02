<?php

namespace App\AdminModule\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

/**
 * Model starající se o tabulku person  
 */
class Preparations extends Table
{

  protected $tableName = 'preparation';

  public function findUncategorized() {
	  return $this->query('SELECT `id`, `title` 
		  				   FROM '.$this->tableName.' 
		  				   LEFT JOIN `preparation_category` ON `preparation_category`.`preparation_id` = `preparation`.`id` 
		  				   WHERE (`category_id` IS NULL) 
		  				   ORDER BY `preparation`.`title`');
  }

  public function insert($title)	{
  	try {
      	return $this->getTable()
                    ->insert(array('title' => $title));
    } catch(\PDOException $e) {
        if($e->getCode() == 23000)
            return false;
        else
            throw $e;
    }
 }
 
  public function update($id, $data)  {  	  
        return $this->getTable()
        			->where(array("id" => $id))
        			->update($data);
  } 
 
  public function delete($preparation_id)  {  	  
  
  	  $this->getTable()
	       ->where('id = ?', $preparation_id)
	       ->delete();
  
  }
  
  
}