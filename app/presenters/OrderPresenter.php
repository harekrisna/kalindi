<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI\Form;
use Latte;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Tracy\Debugger;

class OrderPresenter extends BasePresenter   {

	private $time_deadline = "08:00:00";	
    private $days = array("monday" => "Po", 
                          "tuesday" => "Út",
                          "wednesday" => "St",
                          "thursday" => "Čt",
                          "friday" => "Pá");
	
								   
	public function renderOrder()	{
		$detect = new Mobile_Detect;
   		$this->template->isMobile = $detect->isMobile();
   		$this->template->isTablet = $detect->isTablet();
	}
	
	public function actionOrder() {
    	$this->template->title = "Objednat";
    	$this->template->submit_button = "send_from_order";
    	$this->order();
	}
	
	public function actionMenu() {
    	$this->template->title = "Týdenní menu";
    	$this->template->submit_button = "send_from_menu";
    	$this->setview("order");
    	$this->order();
	}
	
	public function order() {
		$detect = new Mobile_Detect;
        $now = time();
		$time_deadline = $this->time_deadline;
        $show_next_week_from = $this->menu->strtotime("friday this week 23:59:59");        
         
        if($now < $show_next_week_from) {
		    $offset = 0;  // tento týden
		    $week = "this";
	    }
	    else {
	        $offset = +1; // příští týden
	        $week = "next";
		}
		
        $lunchs = $this->menu->getWeekLunchs($offset);

        foreach ($this->days as $day => $day_cz) {
            if($detect->isMobile())
            	$this['orderForm']["{$week}_week"][$day]->setAttribute('type', 'number');
            	
            $deadline = $this->menu->strtotime("{$day} {$week} week ".$time_deadline);
            
            if($deadline - $now < 0 || $lunchs[$day]['nocook'] == 1) {
                $this['orderForm']["{$week}_week"][$day]->setDisabled();
                $lunchs[$day]['disabled'] = true;
            }    
            else
                $lunchs[$day]['disabled'] = false;
        }
        
        $this->template->week_title = $this->menu->getWeekTitle($offset);
        $this->template->lunch = $lunchs;
        $this->template->week = $week; 
        
        // DALŠÍ TÝDEN        
        /*
        $next_week_lunchs_count = $this->menu
				                       ->findAll()
				                       ->where("lunch_date", $this->menu->getWeekDates(+1))
				                       ->count();

        if($next_week_lunchs_count == 5) {
            $lunchs_next_week = $this->menu->getWeekLunchs(+1);
            foreach ($this->days as $day => $day_cz) {
                if($detect->isMobile())
                	$this['orderForm']['next_week'][$day]->setAttribute('type', 'number');
                	
                if($lunchs_next_week[$day]['nocook'] == 1)
                    $this['orderForm']['next_week'][$day]->setDisabled();
            }
            
            $show_next_week = true;
            $this->template->next_week_title = $this->menu->getWeekTitle(+1);
            $this->template->lunch_next_week = $lunchs_next_week;
        }
        else {
            $show_next_week = false;
            $this->template->next_week_title = "";
            $this->template->lunch_next_week = array();
        }
        
        
        $show_second_slide_from = $this->menu->strtotime("friday this week 08:00:00");
        $start_slide = $show_second_slide_from < $now ? 1 : 0;
        if($show_next_week === false) {
            $start_slide = 0;
        }
        */
        
        $this->template->today = date('N');
        $this->template->show_next_week = false;
        $this->template->next_week_title = "";
        $this->template->start_slide = 0;
	}
	
    protected function createComponentOrderForm(){
	   	$form = new Nette\Application\UI\Form();
	    
	    $form->addText('surname', 'Jméno a příjmení:', 30, 255)
		     ->setEmptyValue('Jméno a příjmení')
			 ->setDefaultValue("Jméno a příjmení");
             
		$form->addText('address', 'Adresa:', 30, 255)
			 ->setEmptyValue('Adresa');

   	    $form->addText('phone', 'Telefon:', 30, 16)
		  	 ->setEmptyValue('Telefon');

		$form->addText('email', 'e-mail:', 30, 255)
			 ->setEmptyValue('e-mail')
			 ->setType('email')			 
			 ->addCondition($form::FILLED)
			  	 ->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu');
		
		$form->addText('workemail', 'Work email:', 30, 255)
			 ->addRule(~$form::FILLED, "Toto pole musí zůstat prázdné.");
		
		$form->addText('note', 'Vaše poznámka:', 30)
			 ->setEmptyValue('Vaše poznámka');
		
		$form->addCheckbox('remember_me', "zapamatuj si mě");
	    
		$this_week = $form->addContainer('this_week');
        $next_week = $form->addContainer('next_week');
		
        foreach ($this->days as $day => $day_cz) {
            $this_week->addText($day, $day_cz)
            		  ->setAttribute('autocomplete', 'off');
            		  
			$next_week->addText($day, $day_cz)
            		  ->setAttribute('autocomplete', 'off');        
        }
           
        $form->addHidden('timestamp', time());
        
        $container = $this->presenter->context->getService("container");
		$httpRequest = $container->getByType('Nette\Http\Request');
	    $remember_me = $httpRequest->getCookie('remember_me');
	    
	    if($remember_me) {
		    $form['remember_me']->setDefaultValue("checked");
		    $form['surname']->setDefaultValue($httpRequest->getCookie('person_name'));
		    $form['address']->setDefaultValue($httpRequest->getCookie('address'));
		    $form['phone']->setDefaultValue($httpRequest->getCookie('phone'));
		    $form['email']->setDefaultValue($httpRequest->getCookie('email'));
	    }
	    
        $form->addSubmit('send_from_order', 'Objednat');
        $form->addSubmit('send_from_menu', 'Objednat');
		
		$form->onSuccess[] = $this->orderFormSubmit;
        return $form;
    }


    public function orderFormSubmit(Form $form) {
        $values = $form->getValues();
        $lunch_price = 90;
        $total_price = 0;
        $this_week = array();
        $next_week = array();
        $this_week_amount = 0;
        $next_week_amount = 0;

		// test zda byl formulář vygenerován před dedlineam a potvrzen po deadlinu
		$deadline_time = strtotime($this->time_deadline);
		$generated_time = $values->timestamp;
		$actual_time = time();
		
		// test zda byl formulář vygenerovám ve stejný týdem, chyba nastávala když byl formulář vygenerován v neděli a potvrzev v pondělí
		$generated_week = date("W", $generated_time);
		$actual_week = date("W", $actual_time);
	
		if((($deadline_time < $actual_time) && ($deadline_time > $generated_time)) || ($generated_week != $actual_week)) {
	        $this->flashMessage('Platnost formuláře vypršela.<br />Vaše objednávka nebyla zpracována!', 'error');
	        
	        if($form['send_from_order']->isSubmittedBy())
	            $this->redirect('order');
	        else {
	            $this->redirect('menu');
	        }
	        $this->terminate();
		}

    	// kontrola, za adresa jiz exxistuje
        $address = $this->address->findBy(array("address_stamp" => $this->address->generateStamp($values['address'])));       
		
        if($address->count() == 0) { // adresa neni v databazi, vlozi se nova
        	$this->address->insert(array("address" => $values['address'],
        								 "address_stamp" => $this->address->generateStamp($values['address']),
										 "zone_id" => NULL,
        								 "cartage_id" => NULL,
        								));
        	$zone_id = NULL;
        	$cartage_id = NULL;
        }
        else { //adresa je v databazi 
	        $address = $address->fetch();
			$zone_id = $address['zone_id'];
			$cartage_id = $address['cartage_id'];
        }
		        
	    $lunchs = $this->menu->getWeekLunchs();
            
        foreach ($values['this_week'] as $day => $amount) {
            if($amount == 0 || $amount == "")
                continue;
		 
           $lunch = $this->menu
                         ->findBy(array("lunch_date" => $this->menu->getWeekDayDate($day, 0)))
                         ->fetch();
           
           $this->order->insert(array('id' => NULL,
									  'person_name' => $values['surname'],
									  'address' => $values['address'],
									  'phone' => $values['phone'],
									  'email' => $values['email'],
									  'note' => $values['note'],
	                                  'lunch_id' => $lunch->id,
									  'zone_id' => $zone_id,
									  'cartage_id' => $cartage_id,
	                                  'lunch_count' => $amount,
									 ));
           
           $this_week[$day]['abbr']  = $lunchs[$day]['abbr'];
           $this_week[$day]['preparation']  = $lunchs[$day]['preparation'];
           $this_week[$day]['amount']  = $amount;
           $this_week[$day]['price'] = $amount * $lunch_price;
           $this_week_amount += $amount;
           $total_price += $this_week[$day]['price'];
        }

        $lunchs_next_week = $this->menu->getWeekLunchs(1);
        
        foreach ($values['next_week'] as $day => $amount) {
           if($amount == 0 || $amount == "")
               continue;
           
           $lunch = $this->menu
                         ->findBy(array("lunch_date" => $this->menu->getWeekDayDate($day, 1)))
                         ->fetch();
                       
           $this->order->insert(array('id' => NULL,
									  'person_name' => $values['surname'],
									  'address' => $values['address'],
									  'phone' => $values['phone'],
									  'email' => $values['email'],
									  'note' => $values['note'],
	                                  'lunch_id' => $lunch->id,
									  'zone_id' => $zone_id,
									  'cartage_id' => $cartage_id,
	                                  'lunch_count' => $amount
									 ));        
      
           $next_week[$day]['abbr']  = $lunchs_next_week[$day]['abbr'];
           $next_week[$day]['preparation']  = $lunchs_next_week[$day]['preparation'];                      
           $next_week[$day]['amount']  = $amount;
           $next_week[$day]['price'] = $amount * $lunch_price;                      
           $next_week_amount += $amount;
           $total_price += $next_week[$day]['price'];
        }
        
        $container = $this->presenter->context->getService("container");
		$httpRequest = $container->getByType('Nette\Http\Request');
		$httpResponse = $container->getByType('Nette\Http\Response');

		if($values->remember_me) {	
			$httpResponse->setCookie('remember_me', true, '100 days');
			$httpResponse->setCookie('person_name', $values['surname'], '100 days');
			$httpResponse->setCookie('address', $values['address'], '100 days');
			$httpResponse->setCookie('phone', $values['phone'], '100 days');
			$httpResponse->setCookie('email', $values['email'], '100 days');
		}
		elseif($httpRequest->getCookie('remember_me')) {
			$httpResponse->deleteCookie('remember_me');
			$httpResponse->deleteCookie('person_name', $values['surname'], '100 days');
			$httpResponse->deleteCookie('address', $values['address'], '100 days');
			$httpResponse->deleteCookie('phone', $values['phone'], '100 days');
			$httpResponse->deleteCookie('email', $values['email'], '100 days');
		}

        $latte = new Latte\Engine;
				
		$params = array(
			'surname' => $values['surname'],
			'email' => $values['email'],
	        'address' => $values['address'],
	        'phone' => $values['phone'],
	        'email' => $values['email'],
	        'week_title' => $this->menu->getWeekTitle(),
	        'next_week_title' => $this->menu->getWeekTitle(1),
	        'this_week' => $this_week,
	        'next_week' => $next_week,
	        'this_week_amount' => $this_week_amount,
	        'next_week_amount' => $next_week_amount,
	        'total_price' => $total_price,
		);
		
		$template = $latte->renderToString('../app/templates/components/order-email.latte', $params);
		
        if($values['email'] != "") {
            $from = $values['surname']." <".$values['email'].">";
            $mail = new Message;
			$mail->setFrom("Kálindí <kalindi@kalindi.cz>")
            	 ->addTo($from)
                 ->setSubject("Potvrzení objednávky obědů")
				 ->setHtmlBody($template);
                 
            $mailer = new SendmailMailer;
            $mailer->send($mail);
        }
        else {
            $from = "Kálindí <kalindi@kalindi.cz>";
        }
        
        if(!Debugger::isEnabled()) {
            $mail = new Message;
            $mail->setFrom($from)
                 ->addTo("Kálindí <kalindi@kalindi.cz>")
                 ->setSubject("Potvrzení objednávky obědů: {$values['surname']} - {$values['address']}")
    			 ->setHtmlBody($template);
            
            $mailer = new SendmailMailer;
            //$mailer->send($mail);    
        }
        
        $this->flashMessage('Vaše objednávka byla přijata. Děkujeme, dobrou chuť.', 'success');
        
        if($form['send_from_order']->isSubmittedBy())
            $this->redirect('order');
        else {
            $this->redirect('menu');
        }
    }
}

