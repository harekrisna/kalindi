<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

final class MenuPresenter extends BasePresenter {    
    protected $category_id;
    
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        
        if(!$this->isAjax()) {
   	        $category = $this->category
                             ->findAll()
                             ->fetch();
            
            $this->category_id = $category->id;
        }
    }

    public function beforeRender() {
        $this->template->menu = array();
        $this->template->wide_layout = true;
    }

	private function loadCategoryPreparations($category_id) {
		$preparationCategory = $this->preparationCategory->findBy(['category_id' => $category_id])
												  		 ->order('preparation.title');
		
		$preparations = [];
		foreach($preparationCategory as $preparation) {
			$data['id'] = $preparation->preparation_id;
			$data['title'] = $preparation->ref('preparation')->title;
			$data['count_in_menu'] = $this->lunchPreparation->findBy(['preparation_id' => $preparation->preparation_id])
															->count();
			
			$last_in_menu = $this->lunchPreparation->findBy(['preparation_id' => $preparation->preparation_id])
												   ->order('lunch.lunch_date DESC')
												   ->limit(1)
												   ->fetch();
			
			$data['last_date'] = $this->menu->get($last_in_menu['lunch_id'])['lunch_date'];
			$preparations[] = $data;
		}
		
		return $preparations;
	}
	
    protected function createComponentChangeCategoryForm(){
	   	$form = new Nette\Application\UI\Form();

	    $categories = $this->category
                           ->findAll()
                           ->order('title');
                           
   	    $form->addSelect('category_id', 'Kategorie:', $categories->fetchPairs('id', 'title'))
             ->setValue($this->category_id);        
		
        $form->onSuccess[] = array($this, 'chooseCategorySubmitted');     
        return $form;
    }

    public function handleChangeCategory($category_id) {												  
		$this->template->preparations = $this->loadCategoryPreparations($category_id);
        $this->redrawControl('preparations');
    }
    
   	public function actionMenu() {
   	    if(!$this->isAjax()) {   													  
            $this->template->preparations = $this->loadCategoryPreparations($this->category_id);   
            $this->template->week_title = $this->menu->getWeekTitle();
        
            $lunchs = $this->menu->getWeekLunchs();
            $this->template->lunch = $lunchs;
            $this->template->week_offset = 0;
        }
        else {
            $this->template->lunch = array();
        }
        
	}
	
   	public function handleUpdateWeek($offset) {
        $this->template->week_title = $this->menu->getWeekTitle($offset);        
        $this->template->lunch = $this->menu->getWeekLunchs($offset, false);
        $this->template->week_offset = $offset;
        //$this->redrawControl("diet_button");
        $this->redrawControl("menu");
	}
	
	
    public function handleRemoveLunchPreparation($lunch_id, $position, $day) {
        $this->lunchPreparation
             ->deleteLunchPosition($lunch_id, $position);
        
        $lunch = $this->menu
                      ->findBy(array('id' => $lunch_id))
                      ->fetch();
                 
        // pokud ke dni nejsou nastaveny zadne preparace smaze se
        if($lunch->related("lunch_preparation")->count() == 0) {
            $this->menu
                 ->delete($lunch->id);
        }

        $this->template->lunch[$day]['preparation'][$position] = "";
        $this->template->lunch[$day]['date'] = $lunch->lunch_date;
        
        $snippet_name = $day."_".$position;
        $this->payload->snippet = $snippet_name;
        $this->redrawControl($snippet_name);
    }	
    
    
    public function handleSetLunchPreparation($lunch_date, $preparation_id, $position, $day) {        
        $lunch = $this->menu
                      ->findBy(array('lunch_date = ?' => $lunch_date))
                      ->fetch();
        
        // pokud neni den v db jeste vytvoren, vytvori se
        if(empty($lunch->id)) {
            $row = $this->menu
                        ->insert($lunch_date);
                        
            $lunch_id = $row->id;
            $this->template->lunch[$day]['id'] = $lunch_id;
        }
        else {
            $lunch_id = $lunch->id;
        }        
        
        $pre = $this->lunchPreparation->findBy(array('lunch_id' => $lunch_id, 
                                                     'preparation_id' => $preparation_id));
        
        // daná preparace v daném dni je již nastavena, tak ji vymažeme. Musí to být tady, pač v modelu nejde nastavit přímo payload (mozna pak predelat)
        // je potreba pouzit delete a pak insert, ne jenom update, pac na dane pozici uz nejak preparace muze byt, coz resi insert
        if($pre->count() > 0) {
            $old_pos = $pre->fetch()->position;
            $this->lunchPreparation->deleteLunchPosition($lunch_id, $old_pos);
            
            $this->template->lunch[$day]['preparation'][$old_pos] = "";

            $this->payload->snippet_old_pos = $day."_".$old_pos;
            $this->redrawControl($day."_".$old_pos);
        }
        
        $this->lunchPreparation->insert($lunch_id, $preparation_id, $position);
        $preparation = $this->preparation
                            ->find($preparation_id);

        $this->template->lunch[$day]['preparation'][$position] = $preparation->title;
        
         
        $snippet_name = $day."_".$position;
        $this->payload->snippet = $snippet_name;
        $this->template->lunch[$day]['id'] = $lunch_id;
        $this->template->lunch[$day]['date'] = $lunch_date;
        $this->redrawControl($snippet_name);
    }
    	
    public function handleChangeCookFlag($lunch_date, $nocook_flag) {
        $lunch = $this->menu
                      ->findBy(array('lunch_date = ?' => $lunch_date))
                      ->fetch();

        if($nocook_flag == 1) {
            if(empty($lunch->id)) {
                $this->menu
                     ->insert($lunch_date, 1);
            }
            else {
                $this->lunchPreparation->deleteDayPreparations($lunch->id);
                $this->menu->update(array('id' => $lunch->id), 
                                    array('nocook' => 1));
            }
        }
        else {
            $this->menu
                 ->delete($lunch->id);
        }
        
        $day = strtolower(date('l', strtotime($lunch_date)));
        $this->template->lunch[$day]['nocook'] = $nocook_flag;
        $this->template->lunch[$day]['date'] = $lunch_date;
        $this->template->lunch[$day]['preparation'][1] = "";
        $this->template->lunch[$day]['preparation'][2] = "";
        $this->template->lunch[$day]['preparation'][3] = "";
        $this->template->lunch[$day]['preparation'][4] = "";
                                                            
        $this->redrawControl($day);        
    }     
            
	public function renderDiet($week_offset) {
        $this->setLayout("layout.printout");
        $this->template->week_title = $this->menu->getWeekTitle($week_offset);
        $this->template->lunch = $this->menu->getWeekLunchs($week_offset);
    }  
}
