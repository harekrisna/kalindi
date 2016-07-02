<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
    	$router[] = new Route('admin1896/page/<pageName>', 'Admin:Page:form', null);	
    	
    	$router[] = new Route('admin1896/<presenter>/<action>[/<id>]', array(
            'module'    => 'Admin',
            'presenter' => 'Menu',
            'action'    => 'menu',
            'id'        => null
    	));	
		$router[] = new Route('test/www', "Order:menu", Route::ONE_WAY);
		$router[] = new Route('test/www/tydeni-menu', "Order:menu", Route::ONE_WAY);
		$router[] = new Route('tydeni-menu', "Order:menu");
		$router[] = new Route('objednat-obed', "Order:order");
    		
		$router[] = new Route('<action>[/<id>]', array(
    		'presenter' => 'Page',
			'action' => array(
				Route::FILTER_TABLE => array(
					'o-nas' => 'about',
					'galerie' => 'galery',
					'kontakt' => 'contact',					
				)
			),
		));
		
		$router[] = new Route('', 'Order:menu');
		return $router;
	}

}
