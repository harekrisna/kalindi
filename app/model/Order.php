<?php

namespace App\Model;
use Nette;
use Nette\Environment;
use Tracy\Debugger; 

class Order extends TableExtended {
	protected $tableName = 'order';
}