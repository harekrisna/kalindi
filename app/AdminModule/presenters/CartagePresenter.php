<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;

final class CartagePresenter extends BasePresenter { 
    /** @persistent int*/
    public $cartage_id;
    
    /** @var object */
    private $model;
    
    protected function startup()  {
        parent::startup();
   		$this->model = $this->cartage;
   		
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }  

    public function beforeRender() {
        $this->template->menu = array();
        $this->template->wide_layout = true;
    }
        
    function actionList() {
   	    $this->template->cartages = $this->model->findAll()
   	    										->order('cartage_name');
    }
    
	protected function createComponentCartageForm(){
	   	$form = new Nette\Application\UI\Form();
	   	
	   	$data = $form->addContainer('data'); 
	   	
	    $data->addText('cartage_name', 'Jméno:', 30, 255)
      	     ->setRequired('Zadejte jméno rozvozce.');
      	     
	    $data->addText('abbreviation', 'Zkratka:', 8, 255)
      	     ->setRequired('Zadejte zkratku rozvozce.');
                                      
        $form->addSubmit('insert', 'Uložit')
		     ->onClick[] = array($this, 'insertFormSubmit');

        $form->addSubmit('update', 'Uložit')
   		     ->onClick[] = array($this, 'updateFormSubmit');
		     
        return $form;
    }
    
    public function insertFormSubmit(\Nette\Forms\Controls\SubmitButton $button)   {
        $form = $button->form;
        $values = $form->getValues();
        $data = $values['data'];
        
        if($this->model->insert($data))
            $this->flashMessage('Rozvozce byl vložen.', 'success');
        else 
            $this->flashMessage('Rozvozce s tímto názvem již existuje.', 'info');
        
        $this->redirect('list');
    }
    
    public function updateFormSubmit(\Nette\Forms\Controls\SubmitButton $button) {
        $form = $button->form;
        $values = $form->getValues(); 
        $data = $values['data'];
        
        if($this->model->update($this->cartage_id, $data))
        	$this->flashMessage('Rozvozce byl aktualizován.', 'success');
        else
	        $this->flashMessage('Rozvozce s tímto názvem již existuje.', 'info');

        $this->redirect('list');
    }
    
    public function actionEdit($cartage_id) {
    	$this->setView("edit");
        
        $this->template->cartages = $this->model->findAll()
						                 ->order('cartage_name');
        
        $cartage = $this->model->find($cartage_id);
        
        $this['cartageForm']['data']->setDefaults($cartage);
        $this->cartage_id = $cartage_id;  
    }

    public function handleRemove($cartage_id) {
        $this->model->delete($cartage_id);
        $this->flashMessage('Rozvozce byl odstraněn.', 'success');    
        $this->redirect('list');
    }
}
