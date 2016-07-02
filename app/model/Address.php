<?php

namespace App\Model;
use Nette;
use Tracy\Debugger; 

class Address extends TableExtended
{
  	protected $tableName = 'address';  
 
  	public function generateStamp($address) {
 		$address_stamp = str_replace(" ", "", $address);
 		$address_stamp = Str_Replace(Array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž"),
 									 Array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z"),
 									 $address_stamp
 									);
 		
		$address_stamp = strtolower($address_stamp); 									
 		$address_stamp = preg_replace ("/[^[:alpha:][:digit:]]/", "", $address_stamp); // odstranění všeho kromě alfanumorických znaků
		return $address_stamp;
	}
}