<?php

namespace App\AdminModule\Model;
use Nette;

abstract class TableExtended extends Table
{   
	public function insert($data)	{
	  	try {
	      	return $this->getTable()
	                    ->insert($data);
	                    
	    } catch(\PDOException $e) {
	        if($e->getCode() == 23000)
	            return false;
	        else
	            throw $e;
	    }
 	}
 	   
    public function update($id, $data)	{
        return $this->getTable()
        			->where(['id' => $id])
        			->update($data);
    }

    public function delete($id)	{
	    try {
	        return $this->getTable()
	        			->where(array('id' => $id))
	        			->delete();
        			
	    } catch(\PDOException $e) {
	        if($e->getCode() == 23000)
	            return false;
	        else
	            throw $e;
	    }
    }
}
