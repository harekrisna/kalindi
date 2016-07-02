<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;
use Nette\Utils\Html;

class PagePresenter extends BasePresenter   {
	
	public function renderAbout() {
    	$page = $this->page->findBy(['page' => 'about'])
    	                   ->fetch();
    	                   
        $html = Html::el()->setHtml($page->text);
        $this->template->text = $html;
	}
	
	public function renderGalery() {
		$this->template->photos = $this->photo
									   ->findAll()
									   ->order("order_sort ASC");
	}
}

