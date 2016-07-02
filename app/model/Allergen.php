<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

class Allergen extends Table   {
	protected $tableName = 'allergen'; 
	
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