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
			
			return new JsonResponse($redemption->toArray());
		});
		
		$controller->patch("/{id}", function(Request $req, $id) {
			
			$redemption = Model\Redemption::find($id);
			if (!$redemption)
			{
				throw new NotFoundHttpException('no such redemption');
			}
			
			$redemption->is_confirmed = $req->get('is_confirmed');
			$redemption->save();
			
			return new JsonResponse($redemption->toArray());
		});
		
		$controller->get("/", function(Request $req) {
			
			$query = (new Model\Redemption)->newQuery();
			if ($req->get('device_id'))
			{
				$query->where('device_id', '=', $req->get('device_id'));
			}
			if ($req->get('cupon_id'))
			{
				$query->where('cupon_id', '=', $req->get('cupon_id'));
			}
			
			return new JsonResponse($query->get()->toArray());
		});
		
		$controller->get("/{id}", function(Request $req, $id) {
			$redemption = Model\Redemption::find($id);
			return new JsonResponse($redemption->toArray());
		});
		
		
		return $controller;
	}
}