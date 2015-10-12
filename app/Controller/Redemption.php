<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/******************************************************************************/
/* Redemption Controller                                                      */
/*                                                                            */
/* "Only Zambah Jeebahz can bring you TRUE Redemption!"                       */
/*                                                                            */
/******************************************************************************/
class Redemption implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		$controller->put("/", function(Request $req) {
			
			$redemption = new Model\Redemption;
			$redemption->cupon()->associate(Model\Cupon::find($req->get('cupon_id')));
			$redemption->device_id = $req->get('device_id');
			
			$redemption->save();
			
			return $redemption->toJSON();
		});
		
		return $controller;
	}
}