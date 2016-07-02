<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class WebPresenter extends Nette\Application\UI\Presenter
{
    protected $photo;
    protected $page;

    protected function startup()	{
		parent::startup();
        $this->photo = $this->context->getService("photo");
        $this->page = $this->context->getService("page");
    }
   	
    public function handleSignOut() {
        $this->getUser()->logout();
    $this->redirect('Sign:in');
  }
}

