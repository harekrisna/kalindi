<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

class Galery extends Table   {
	protected $tableName = 'galery'; 

	public function insert($data)	{
	  	try {
	      	return $this->getTable()->insert($data);
	    } catch(\PDOException $e) {
	        if($e->getCode() == 23000)
	            return false;
	        else
	            throw $e;
	    }
	}

	public function getTitleById($id)  {
		$galery = $this->get($id);
		if($galery)
			return $galery->url;
	}
	
	public function getIdByTitle($url)  {
		$galery = $this->findBy(array("url" => $url))->fetch();
		if($galery) {
			return $galery->id;	
		}
	}
	
	public function generateNumberArray($start, $end) {
		$array = array();
		for($i = $start; $i <= $end; $i++) {
		  	$array[sprintf('%02d', $i)] = $i;
		}
		
		return $array;
	}
}