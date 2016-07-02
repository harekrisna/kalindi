<?php

namespace App\Model;
use Nette;
use Tracy\Debugger; 

/**
 * Reprezentuje repozit?? pro datab?zovou tabulku
 */
abstract class Table extends Nette\Object
{

    /** @var Nette\Database\Connection */
    public $connection;

    /** @var string */
    protected $tableName;

    /**
     * @param Nette\Database\Connection $db
     * @throws \Nette\InvalidStateException
     */
    public function __construct(Nette\Database\Context $db)
    {
        $this->connection = $db;

        if ($this->tableName === NULL) {
            $class = get_class($this);
            throw new Nette\InvalidStateException("Název tabulky musí být definován v $class::\$tableName.");
        }
    }

    /**
     * Vrac? celou tabulku z datab?ze
     * @return \Nette\Database\Table\Selection
     */
    protected function getTable($tableName = "")
    {
        if($tableName != "")
            return $this->connection->table($tableName);
        else
            return $this->connection->table($this->tableName);
    }

    /**
     * Vrac? v?echny z?znamy z datab?ze
     * @return \Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * Vrac? vyfiltrovan? z?znamy na z?klad? vstupn?ho pole
     * (pole array('name' => 'David') se p?evede na ??st SQL dotazu WHERE name = 'David')
     * @param array $by
     * @return \Nette\Database\Table\Selection
     */
    public function findBy(array $by) {
        return $this->getTable()->where($by);
    }

    /**
     * To sam? jako findBy akor?t vrac? v?dy jen jeden z?znam
     * @param array $by
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */
    public function findOneBy(array $by)
    {
        return $this->findBy($by)->limit(1)->fetch();
    }
         
    public function query($sql) {
        return $this->connection->query($sql);        
    } 
    
    /**
     * Vrac? z?znam s dan?m prim?rn?m kl??em
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */

    public function find($id)
    {
        return $this->getTable()->get($id);
    }
    
    public function get($id)
    {
        return $this->getTable()->get($id);
    }    
    
    public function insert($data)	{
        return $this->getTable()->insert($data);
    }
    
    public function update($ids, $data)
    {
        return $this->getTable()
        			->where($ids)
        			->update($data);
    }
    
    public function delete($id)
    {
        return $this->getTable()
        						->where(array('id' => $id))
        						->delete();
    }
}
