<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;

final class ZonePresenter extends BasePresenter {    
    /** @persistent int*/
    public $zone_id;  
    
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
   	    $this->template->zones = $this->zone->findAll()
   	    									->order('title');
    }
    
	protected function createComponentZoneForm(){
	   	$form = new Nette\Application\UI\Form();

	    $form->addText('title', 'Název:', 30, 255)
      	     ->setRequired('Zadejte název zóny.');
                                      
        $form->addSubmit('insert', 'Uložit')
		     ->onClick[] = array($this, 'allergenFormInsert');

        $form->addSubmit('update', 'Uložit')
   		     ->onClick[] = array($this, 'allergenFormUpdate');
		     
        return $form;
    }
    
    public function allergenFormInsert(\Nette\Forms\Controls\SubmitButton $button)   {
        $form = $button->form;
        $values = $form->getValues();
        
        $ok = $this->zone->insert($values['title']);
        
        if($ok)
            $this->flashMessage('Lokalita byla vložena.', 'success');
        else 
            $this->flashMessage('Lokalita s tímto názvem již existuje.', 'info');
        
        $this->redirect('list');
    }
    
    public function allergenFormUpdate(\Nette\Forms\Controls\SubmitButton $button) {
        $form = $button->form;
        $values = $form->getValues(); 
        
        if($this->zone->update($this->zone_id, $values))
        	$this->flashMessage('Lokalita byla aktualizován.', 'success');
        else
	        $this->flashMessage('Lokalita s tímto názvem již existuje.', 'info');

        $this->redirect('list');
    }
    
    public function actionEditZone($zone_id) {
    	$this->setView("edit");
        $zones = $this->zone->findAll()
			                ->order('title');
        
        $this->template->zones = $zones;
        
        $zone = $this->zone
                     ->find($zone_id);
        
        $this["zoneForm"]->setDefaults($zone);
        $this->zone_id = $zone_id;  
    }

    public function handleRemoveZone($zone_id) {
        $this->zone->delete($zone_id);
        $this->flashMessage('Lokalita byla odstraněna.', 'success');    
        $this->redirect('list');            
    }
}
