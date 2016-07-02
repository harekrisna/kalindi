<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;

final class OrderPresenter extends BasePresenter {    
    /** @persistent int*/
    public $month_offset;

    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        
		if(empty($this->month_offset)) {
			$this->month_offset = 0;
		}
	}  
    
    function beforeRender() {
		$actual_year = date("Y", strtotime("first day of this month ".($this->month_offset)." month"));
		$previous_year = date("Y", strtotime("first day of this month ".($this->month_offset-1)." month"));
		$next_year = date("Y", strtotime("first day of this month ".($this->month_offset+1)." month"));       
		
		$actual_month = date("n", strtotime("first day of this month ".($this->month_offset)." month"));
		$previous_month = date("n", strtotime("first day of this month ".($this->month_offset-1)." month"));
		$next_month = date("n", strtotime("first day of this month ".($this->month_offset+1)." month"));
		      
		$next_title = $next_month.".".$next_year;
		$previous_title = $previous_month.".".$previous_year;
		
        $dates_in_month = $this->date
                               ->dates_month($actual_month, $actual_year);

		$dates = [];
	    foreach($dates_in_month as $date_format => $timestamp) {
			$lunch_count = $this->order->findBy(['lunch.lunch_date' => $date_format]);
			$dates[$date_format]['date'] = $date_format;
			$dates[$date_format]['timestamp'] = $timestamp;
			$dates[$date_format]['lunch_count'] = $lunch_count->sum('lunch_count');
	    }	  
        
        $this->template->dates = $dates;
		$this->template->offset = $this->month_offset;       
		$this->template->next = $next_title;
		$this->template->previous = $previous_title;       
    }
    
    function actionDefault() {
        $this->month_offset = 0;
        $this->redirect('list');
    }
    
    function actionSetOffset($offset) {
        $this->month_offset = $offset;
        if($offset == 0)
            $this->redirect('list');
        
        $year = date("Y", strtotime("first day of this month ".$offset." month"));
        $month = date("m", strtotime("first day of this month ".$offset." month"));
           
        $this->redirect('list', $year."-".$month."-01");
    }
	
	function actionList($date) {
		if(!$this->isAjax()) {
	        if(!$date)
    	        $date = date("Y-m-d");
				
			$this['insertOrderForm']['lunch_date']->setValue($date);
		}
	}

    function renderList($date) {
        if(!$date)
            $date = date("Y-m-d");

        $this->template->date = $date;
        		
        $this->template->orders = $this->order->findAll()
						                      ->where("lunch.lunch_date = ?", $date);;

        $this->template->zones = $this->zone->findAll();
        $this->template->payment_types = $this->paymentType->findAll();
        $this->template->cartages =  $this->cartage->findAll();;
    }  
    
    protected function createComponentInsertOrderForm(){
	   	$form = new Nette\Application\UI\Form();
								 
	    $form->addHidden('lunch_date', 'Date:');
        
	    $form->addText('person_name', 'Jméno:', 40, 255)
      	     ->setRequired('Zadejte jméno.');
      	     
	    $form->addText('address', 'Adresa:', 50, 255)
      	     ->setRequired('Zadejte adresu.');
      	     
	    $form->addText('phone', 'Telefon:', 9, 9);                   

   	    $form->addSelect('zone_id', 'Lokalita:', $this->zone->findAll()->fetchPairs('id', 'title'))
	    	 ->setPrompt("");	    
	    	 
   	    $form->addSelect('payment_type_id', 'Typ platby:', $this->paymentType->findAll()->fetchPairs('id', 'type'))
   	    	 ->setPrompt("");
	    	 
   	    $form->addSelect('cartage_id', 'Rozvoz:', $this->cartage->findAll()->fetchPairs('id', 'abbreviation'))
	    	 ->setPrompt("");
	    	 	    	 
   		$form->addText('lunch_count', 'Počet objedů:', 1, 2)
      	     ->setRequired('Zadejte počet obědů.');
   		
   		$form->addSubmit('insert', 'OK');
   		
        $form->onSuccess[] = array($this, 'insertOrder');
        return $form;
    }
    
    public function insertOrder(Nette\Application\UI\Form $form)    {
        $values = $form->getValues();

    	// kontrola, za adresa jiz exxistuje
        $address = $this->address->findBy(array("address_stamp" => $this->address->generateStamp($values['address'])));       
		
        if($address->count() == 0) { // adresa neni v databazi, vlozi se nova
        	$this->address->insert(array("address" => $values['address'],
        								 "address_stamp" => $this->address->generateStamp($values['address']),
										 "zone_id" => $values['zone_id'],
        								 "cartage_id" => $values['cartage_id']));
        }
        else { //adresa je v databazi 
        	$address = $address->fetch();
        	
	        if($values['zone_id'] == "") //nacte se lokalita pokud neni zadana
				$values['zone_id'] = $address['zone_id'];

	        if($values['cartage_id'] == "") // nacte se rozvozce pokud neni zadan
				$values['cartage_id'] = $address['cartage_id'];
        }

        $lunch = $this->menu
                      ->findBy(array("lunch_date" => $values['lunch_date']))
                      ->fetch();
						 
		$this->order->insert(array('id' => NULL,
								   'person_name' => $values['person_name'],
								   'address' => $values['address'],
								   'phone' => $values['phone'],
								   'email' => "",
                                   'lunch_id' => $lunch->id,
                               	   'zone_id' => $values['zone_id'],
                               	   'payment_type_id' => $values['payment_type_id'],
								   'cartage_id' => $values['cartage_id'],
                                   'lunch_count' => $values['lunch_count']
                             ));
                            

        $this->flashMessage('Objednávka byla přidána', 'success');
        $this->redirect('list', array("date" => $values['lunch_date']));
    }

	public function actionAddOrders() {
		$empty_days = 0;
        $now = time();
		$time_deadline = "12:00:00";
    	
	    foreach($this->menu->getWeekDates() as $date) {
	        $lunch = $this->menu
	                      ->findBy(array("lunch_date" => $date))
			 			  ->fetch();
			
			$day_name = $this->menu
							 ->getAbbrFromDate($date);
			
			$day_timestamp = $this->menu->strtotime($date." ".$time_deadline);
							 
			if(!$lunch || $lunch['nocook'] == 1 || $day_timestamp - $now < 0) {
				$empty_days++;
				$this['insertOrderExtendedForm']['this_week'][$day_name]->setDisabled();
			}
	    }
	    
	    if($empty_days == 5) {
		    $this['insertOrderExtendedForm']['this_week_all']->setDisabled();
	    }
	    	    

		$empty_days = 0;
    	
	    foreach($this->menu->getWeekDates(1) as $date) {
	        $lunch = $this->menu
	                      ->findBy(array("lunch_date" => $date))
			 			  ->fetch();			

			$day_name = $this->menu
							 ->getAbbrFromDate($date);

			if(!$lunch || $lunch['nocook'] == 1) {
				$empty_days++;
				$this['insertOrderExtendedForm']['next_week'][$day_name]->setDisabled();
			}
	    }
	    
	    if($empty_days == 5) {
		    $this['insertOrderExtendedForm']['next_week_all']->setDisabled();
	    }
	}

	protected function createComponentInsertOrderExtendedForm(){
	   	$form = new Nette\Application\UI\Form();
        
	    $form->addText('person_name', 'Jméno:', 40, 255)
      	     ->setRequired('Zadejte jméno.');
      	     
	    $form->addText('address', 'Adresa:', 40, 255)
      	     ->setRequired('Zadejte adresu.');
      	     
	    $form->addText('phone', 'Telefon:', 9, 9);                   

   	    $form->addSelect('zone_id', 'Lokalita:', $this->zone->findAll()->fetchPairs('id', 'title'))
	    	 ->setPrompt("");
	    	 
   	    $form->addSelect('payment_type_id', 'Typ platby:', $this->paymentType->findAll()->fetchPairs('id', 'type'))
	    	 ->setPrompt("");

   	    $form->addSelect('cartage_id', 'Rozvoz:', $this->cartage->findAll()->fetchPairs('id', 'abbreviation'))
	    	 ->setPrompt("");
	    	 
	    $this_week = $form->addContainer('this_week');

        $form->addText("this_week_all", "")
	        		  ->setType("number")
     	        	  ->setAttribute('min', '0')
     	        	  ->setAttribute('max', '99')
     	        	  ->setAttribute('autocomplete', 'off')
       	        	  ->setDefaultValue('0')
    		  		  ->addCondition($form::FILLED)
					  	  ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));

	    foreach($this->menu->getWeekDates() as $date) {
  	    	$day_name = strtolower(date("l",strtotime($date)));
  	    	
	        $this_week->addText($day_name, date("d.m.Y", strtotime($date)))
	        		  ->setType("number")
     	        	  ->setAttribute('min', '0')
     	        	  ->setAttribute('max', '99')
     	        	  ->setAttribute('autocomplete', 'off')
       	        	  ->setDefaultValue('0')
    		  		  ->addCondition($form::FILLED)
					  	  ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));			
	    }


	    $next_week = $form->addContainer('next_week');    	      	  
	    
        $form->addText("next_week_all", "")
	         ->setType("number")
     	     ->setAttribute('min', '0')
     	     ->setAttribute('max', '99')
     	     ->setAttribute('autocomplete', 'off')
       	     ->setDefaultValue('0')
    		 ->addCondition($form::FILLED)
				 ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));        	
				 
	    foreach($this->menu->getWeekDates(1) as $date) {
	    	$day_name = strtolower(date("l",strtotime($date)));
	        
	        $next_week->addText($day_name, date("d.m.Y", strtotime($date)))
	           		  ->setType("number")
					  ->setAttribute('min', '0')
					  ->setAttribute('max', '99')
					  ->setAttribute('autocomplete', 'off')
					  ->setDefaultValue('0')
					  ->addCondition($form::FILLED)
					  	  ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));
	    }
	    	    
   		$form->addSubmit('insert', 'Přidat objednávky');
   		
        $form->onSuccess[] = array($this, 'insertOrderExtendedSubmit');
        return $form;
    }


    public function insertOrderExtendedSubmit(Nette\Application\UI\Form $form)    {
        $values = $form->getValues();
		
    	// kontrola, za adresa jiz exxistuje
        $address = $this->address->findBy(array("address_stamp" => $this->address->generateStamp($values['address'])));       
		
        if($address->count() == 0) { // adresa neni v databazi, vlozi se nova
        	$this->address->insert(array("address" => $values['address'],
        								 "address_stamp" => $this->address->generateStamp($values['address']),
										 "zone_id" => $values['zone_id'],
        								 "cartage_id" => $values['cartage_id']));
        }
        else { //adresa je v databazi 
        	$address = $address->fetch();
	        if($values['zone_id'] == "") //nacte se lokalita pokud neni zadana
				$values['zone_id'] = $address['zone_id'];

	        if($values['cartage_id'] == "") // nacte se rozvozce pokud neni zadan
				$values['cartage_id'] = $address['cartage_id'];
        }
		
		foreach($values->this_week as $day_name => $lunch_count) {
			if($lunch_count > 0) {
				$lunch_date = $this->menu->getWeekDayDate($day_name);
				
		        $lunch = $this->menu
		                      ->findBy(array("lunch_date" => $lunch_date))
				 			  ->fetch();
				
				$this->order->insert(array('id' => NULL,
										   'person_name' => $values['person_name'],
										   'address' => $values['address'],
										   'phone' => $values['phone'],
										   'email' => "",
		                                   'lunch_id' => $lunch->id,
		                                   'zone_id' => $values['zone_id'],
		                                   'lunch_count' => $lunch_count,
		                             ));
			}
		}
		
		foreach($values->next_week as $day_name => $lunch_count) {
			if($lunch_count > 0) {
				$lunch_date = $this->menu->getWeekDayDate($day_name, 1);
				
		        $lunch = $this->menu
		                      ->findBy(array("lunch_date" => $lunch_date))
				 			  ->fetch();

				$this->order->insert(array('id' => NULL,
										   'person_name' => $values['person_name'],
										   'address' => $values['address'],
										   'phone' => $values['phone'],
										   'email' => "",
		                                   'lunch_id' => $lunch->id,
		                                   'zone_id' => $values['zone_id'],
		                                   'lunch_count' => $lunch_count,
								    ));
			}
		}
		
        $this->flashMessage('Objednávky byly přidány', 'success');
        $this->redirect('addOrders');
	}

    public function handleEditField($order_id, $column, $value) {
		$this->order->update($order_id, array($column => $value));
		$order = $this->order->find($order_id);	
		
		if($column == "lunch_count") {
			$this->payload->lunch_sum = $this->order->findBy(array("lunch_id" => $order->lunch_id))
									 	     		->sum("lunch_count");			
		}
		
		$this->payload->success = TRUE;
		$this->payload->value = $order->$column;
		$this->sendPayload();
        $this->terminate();
    }

    public function handleChangeZone($order_id, $zone_id) {
		$this->order->update(array("id" => $order_id), array("zone_id" => $zone_id));
		$order = $this->order->find($order_id);
		
		$address = $this->address->findBy(array("address_stamp" => $this->address->generateStamp($order->address)));

        if($address->count()) {
			$address = $address->fetch();
        	$this->address->update(array("id" => $address->id), array("zone_id" => $zone_id));
        }
		
		$this->payload->success = TRUE;
		$this->payload->value = $order->zone_id;
		$this->sendPayload();
        $this->terminate();	    
    }
    
    public function handleChangeCartage($order_id, $cartage_id) {
		$this->order->update($order_id, ['cartage_id' => $cartage_id]);
		$order = $this->order->find($order_id);
		
		$address = $this->address->findBy(array("address_stamp" => $this->address->generateStamp($order->address)));

        if($address->count()) {
			$address = $address->fetch();
        	$this->address->update(array("id" => $address->id), array("cartage_id" => $cartage_id));
        }
		
		$this->payload->success = TRUE;
		$this->payload->value = $order->cartage_id;
		$this->sendPayload();
        $this->terminate();	    
    }
    
    public function handleDeleteOrder($order_id) {
		$order = $this->order
	 		 	   	  ->find($order_id);
	 		 	   	  
    	$this->order
             ->delete($order_id);
	 		
 		$order = $this->order
			 		  ->findBy(array("lunch_id" => $order->lunch_id));
			
		$this->payload->lunch_sum = $order->sum("lunch_count");
		$this->payload->success = TRUE;
		$this->sendPayload();
        $this->terminate();
    }
        
    public function actionPrintout($date) {
        $this->setLayout("layout.printout");
    }
        
	public function renderPrintout($date) {            
    	$cartages = array();
    	
        $cartages_db = $this->cartage
                         	->findAll();
        
        foreach ($cartages_db as $cartage) {
	        $orders = $this->order->findAll()
							      ->where("lunch.lunch_date = ?", $date)
								  ->where("order.cartage_id = ?", $cartage->id)
								  ->order("zone.id DESC, address ASC");
        	
	    	$cartages[$cartage->id] = $orders;
        }

		$orders = $this->order->findAll()
		                      ->where("lunch.lunch_date = ?", $date)
							  ->where("cartage_id IS NULL");
    	
    	$cartages[] = $orders;

        $this->template->cartages = $cartages;
        $this->template->lunch_sum = $this->order->findAll()->where("lunch.lunch_date = ?", $date)->sum("lunch_count");
    }

}
