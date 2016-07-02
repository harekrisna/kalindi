<?php

namespace App\AdminModule\Model;
use Nette;
use Tracy\Debugger;

/**
 * Model starající se o tabulku person  
 */
class Zone extends Table
{
  	protected $tableName = 'zone';  
  	
	public function insert($title)	{
		try {
		  	return $this->getTable()
		                ->insert(array('title' => $title));
		}catch(\Nette\Database\UniqueConstraintViolationException $e) {
        	return false;
		}			
	}
	
    public function update($id, $data)  {  	  
   		try {
	        return $this->getTable()
	        			->where(array("id" => $id))
	        			->update($data);
	        			
		}catch(\Nette\Database\UniqueConstraintViolationException $e) {
        	return false;
		}			
    }  		
}