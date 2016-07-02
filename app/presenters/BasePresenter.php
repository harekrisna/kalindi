<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

abstract class BasePresenter extends Nette\Application\UI\Presenter {
	protected $menu;
	protected $order;
	protected $address;
	protected $photo;
	protected $page;

	protected function startup()	{
		parent::startup();
        $this->menu = $this->context->getService("menu");
        $this->order = $this->context->getService("order");
        $this->address = $this->context->getService("address");
        $this->photo = $this->context->getService("photo");
        $this->page = $this->context->getService("page");
	}	
}
