<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;
use Nette\Utils\Image;

final class PagePresenter extends WebPresenter {        
    
    protected $pageName = "";
    
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
    public function beforeRender() {
        $this->template->wide_layout = true;
    }
	
	public function actionForm($pageName = "") {
    	if($pageName != "") {
        	$this->pageName = $pageName;
    	}
	}
	
	public function renderForm($pageName = "") {
		$page = $this->page->findBy(['page' => $this->pageName])
		                   ->fetch();

		if ($page) {
            $this["pageForm"]->setDefaults($page);
            
            $_SESSION['KCFINDER'] = array(
                'disabled' => false,
                'uploadURL' => "../../images/kcfinder",
    		);
        }
	}
					
    protected function createComponentPageForm(){
        $form = new Form;
	    $form->addTextArea('text', 'Text:', 40);
	    $form->addSubmit('update', 'Uložit');
        $form->onSuccess[] = array($this, 'pageFormSucceeded');
        return $form;
    }
    
	public function pageFormSucceeded(Form $form, $values)	{
    	$page = $this->page->findBy(['page' => $this->pageName])
    	                   ->fetch();
    	                   
		$page->update(['text' => $values['text']]);

        $this->flashMessage('Text uložen.', 'success');
        $this->redirect('form', $this->pageName);
    }
}

