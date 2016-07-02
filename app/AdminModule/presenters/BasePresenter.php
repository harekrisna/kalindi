<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter	{
    protected $date;    
    protected $preparation;
    protected $preparationAllergen;
    protected $preparationCategory;
    protected $category;
    protected $allergen;
    protected $menu;
    protected $lunchPreparation;
    protected $order;
    protected $address;
    protected $cartage;
    protected $zone;
    protected $paymentType;

    protected function startup() {
		parent::startup();
        $this->date = $this->context->getService("date");
        $this->preparation = $this->context->getService("preparations");
        $this->preparationAllergen = $this->context->getService("preparationAllergen");
        $this->preparationCategory = $this->context->getService("preparationCategory");
        $this->category = $this->context->getService("category");
        $this->allergen = $this->context->getService("allergen");
        $this->menu = $this->context->getService("menu");
        $this->lunchPreparation = $this->context->getService("lunchPreparation");
        $this->order = $this->context->getService("order");
        $this->address = $this->context->getService("address");
        $this->cartage = $this->context->getService("cartage");
        $this->zone = $this->context->getService("zone");
        $this->paymentType = $this->context->getService("paymentType");
    }
   	
    public function handleSignOut() {
        $this->getUser()->logout();
    $this->redirect('Sign:in');
  }
}

