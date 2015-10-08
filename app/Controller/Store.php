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
			
			$db = $app['capsule']->connection();
			
			
			if (!$req->get('lat') || !$req->get('lon'))
			{
				throw new \Exception("la tienda no tiene locacion");
			}
			
			if (!is_numeric($req->get('lat')) || !is_numeric($req->get('lon')))
			{
				throw new \Exception("la locacion es invalida");
			}
			
			$lat = $req->get('lat');
			$lon = $req->get('lon');
			
			
			$store = new Model\Store;
			$store->login_id = $app['session']->get('user_id');
			$store->address = $req->get('address');
			$store->comuna = $req->get('comuna');
			$store->region = $req->get('region');
			$store->location = $db->raw("ST_GeographyFromText('SRID=4326;POINT({$lat} {$lon})')");
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
