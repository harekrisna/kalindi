<?php

namespace App\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

class Photo extends Table
{
    protected $tableName = 'photo';  
     
    public function insert($data)	{
        if(isset($data['file'])) {
            $data['file'] = Str_Replace(Array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž"),
 			       						Array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z"),
                                        $data['file']
                                       );
        }
        
        return $this->getTable()->insert($data);
    }
    
    public function findBy(array $by) {
        if(isset($by['file'])) {
            $by['file'] = Str_Replace(Array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž"),
 			       					  Array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z"),
                                      $by['file']
                                     );
        }
        
        return $this->getTable()->where($by);
    }
}