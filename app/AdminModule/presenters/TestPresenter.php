<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Nette\Diagnostics\Debugger;

final class TestPresenter extends BasePresenter {        

    public function beforeRender() {
        $this->template->menu = array();
        $this->setLayout("layout.printout");
    }

    public function renderDefault($date) {            
        $cartages = $this->cartage
                         ->findAll();
        
        $orders = $this->order
                       ->findAll()
                       ->where("lunch.lunch_date = ?", $date);

        $this->template->orders = $orders;
        $this->template->cartages = $cartages;
    }
}
