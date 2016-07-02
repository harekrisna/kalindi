<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;

final class AddressPresenter extends BasePresenter {    
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }  

    public function beforeRender() {
        $this->template->menu = array();
        $this->template->wide_layout = true;
    }
        
    function actionList() {
   	    $this->template->addresses = $this->address->findAll();
        $this->template->cartages = $this->cartage->findAll();
    }

    public function handleSetZone($address_id, $zone_id) {
		if($zone_id == "")
			$zone_id = NULL;
			
        $this->address->update(array("id" => $address_id), 
        					   array("zone_id" => $zone_id)
        				   );
        $this->sendPayload();             
        $this->terminate();
    } 

    public function handleSetCartage($address_id, $cartage_id) {
		if($cartage_id == "")
			$cartage_id = NULL;
			
        $this->address->update($address_id, ["cartage_id" => $cartage_id]);
        $this->sendPayload();					  
        $this->terminate();
    }   
}
