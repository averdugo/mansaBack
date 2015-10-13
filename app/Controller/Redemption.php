<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
			
			$cupon = Model\Cupon::find($req->get('c'));
			if (!$cupon)
			{
				throw new NotFoundHttpException("No existe el Cupon");
			}
			
			$count = Model\Redemption
				::where('cupon_id', '=', $cupon->id)
				->count();
			
			if ($count >= $cupon->stock)
			{
				throw new \Exception("cupon se ha quedado sin stock");
			}
			
			$redemption = new Model\Redemption;
			$redemption->cupon()->associate($cupon);
			$redemption->device_id = $req->get('d');
			
			$redemption->save();
			
			return $redemption->toJSON();
		});
		
		return $controller;
	}
}