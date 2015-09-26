<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/******************************************************************************/
/*  Store Controller                                                          */
/*                                                                            */
/* Lets open a Speak Easy, Bitch! Time for women and booze, Sheen Style!      */
/*                                                                            */
/******************************************************************************/
class Store implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		$controller->put('/', function(Application $app, Request $req) {
			
			$store = new Model\Store;
			$store->login_id = $app['session']->get('user_id');
			$store->address = $req->get('address');
			$store->comuna = $req->get('comuna');
			$store->region = $req->get('region');
			$store->save();
			
			return $store->toJSON();
		});
		
		$controller->get('/', function(Application $app) {
			
			$user_id = $app['session']->get('user_id');
			$stores = Model\Store::where('login_id', '=', $user_id)
				->get();
			
			return $stores->toJSON();
		});
		
		return $controller;
	}
}
