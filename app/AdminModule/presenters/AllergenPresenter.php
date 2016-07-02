<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;

final class AllergenPresenter extends PreparationPresenter {        
    /** @persistent int*/
    public $allergen_id;     
  
    public function renderList() {
   	    $this->template->allergens = $this->allergen->findAll()
   													->order('title');
    }
    
    protected function createComponentAllergenForm(){
	   	$form = new Nette\Application\UI\Form();

	    $form->addText('title', 'Název:', 30, 255)
      	     ->setRequired('Zadejte název alergenu.');
                                      
        $form->addSubmit('insert', 'Uložit')
		     ->onClick[] = array($this, 'allergenFormInsert');

        $form->addSubmit('update', 'Uložit')
   		     ->onClick[] = array($this, 'allergenFormUpdate');
		     
        return $form;
    }
    
    public function allergenFormInsert(\Nette\Forms\Controls\SubmitButton $button)   {
        $form = $button->form;
        $values = $form->getValues();
        
        $ok = $this->allergen->insert($values->title);
        
        
        if($ok)
            $this->flashMessage('Alergen byla přidán.', 'success');
        else 
            $this->flashMessage('Alergen již existuje.', 'info');
        
        $this->redirect('list');
    }
    
    public function allergenFormUpdate(\Nette\Forms\Controls\SubmitButton $button) {
        $form = $button->form;
        $values = $form->getValues(); 
        
        if($this->allergen->update($this->allergen_id, $values))
        	$this->flashMessage('Alergen byl aktualizován.', 'success');
        else
	        $this->flashMessage('Alergen s tímto názvem již existuje.', 'info');

        $this->redirect('list');
    }

    public function actionEditAllergen($allergen_id) {
    	$this->setView("edit");
        $allergens = $this->allergen->findAll()
			                        ->order('title');
        
        $this->template->allergens = $allergens;
        
        $allergen = $this->allergen
                         ->find($allergen_id);
        
        $this["allergenForm"]->setDefaults($allergen);
        $this->allergen_id = $allergen_id;  
    }

    public function handleRemoveAllergen($allregen_id) {
        try{
            $this->allergen->delete($allregen_id);
            $this->flashMessage('Alregen byl odstraněn.', 'success');    
            $this->redirect('list');
            
        }catch(\PDOException $e){
            if($e->getCode() == 23000)
                $this->flashMessage('Nelze odstranit allregen, dokud je použit v menu.', 'error');
            else
                throw $e;
        }
    }    
}
