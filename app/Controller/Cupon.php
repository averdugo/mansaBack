<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/******************************************************************************/
/*  Cupon Controller                                                          */
/*                                                                            */
/* Blow at Half Price! Going, Going... GONE!!!                                */
/*                                                                            */
/******************************************************************************/
class Cupon implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		$controller->put('/', function(Application $app, Request $req) {
			
			$cupon = new Model\Cupon;
			$cupon->store_id = $req->get('store_id');
			$cupon->label = $req->get('label');
			$cupon->drink = $req->get('drink');
			$cupon->price = $req->get('price');
			$cupon->save();
			
			return $cupon->toJSON();
		});
		
		$controller->get('/', function(Application $app) {
			
			$cupons = Model\Cupon::with('store')->get();
			return $cupons->toJSON();
		});
		
		return $controller;
	}
}
