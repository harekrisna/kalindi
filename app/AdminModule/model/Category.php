<?php

namespace App\AdminModule\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

/**
 * Model starající se o tabulku person  
 */
class Category extends Table
{
  protected $tableName = 'category';

  public function insert($title)	{
  	return $this->getTable()
                ->insert(array('title' => $title));
  }

  public function update($id, $data)  {  	  
        return $this->getTable()
        			->where(array("id" => $id))
        			->update($data);
  } 
  
  public function delete($category_id)  {
  	  $this->getTable()
	       ->where('id = ?', $category_id)
	       ->delete();
  }

}