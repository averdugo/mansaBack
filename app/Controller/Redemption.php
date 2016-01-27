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
			
			if ($cupon->stock !== null)
			{
				$count = Model\Redemption
					::where('cupon_id', '=', $cupon->id)
					->count();
				
				if ($count >= $cupon->stock)
				{
					throw new \Exception("cupon se ha quedado sin stock");
				}
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
			
			if ($req->request->has('rating'))
			{
				$redemption->rating = $req->rquest->get('rating');
			}
			
			if ($req->request->has('is_confirmed'))
			{
				$redemption->is_confirmed = $req->get('is_confirmed');
			}
			
			$redemption->save();
			
			return new JsonResponse($redemption->toArray());
		});
		
		$controller->get("/", function(Request $req) {
			
			$perpage = 50;
			if ($req->get('perpage'))
			{
				$perpage = $req->get('perpage');
				if ($perpage > 100)
				{
					$perpage = 100; 
				}
			}
			
			
			$resp = new JsonResponse;
			
			$query = (new Model\Redemption)->newQuery();
			if ($req->get('device_id'))
			{
				$query->where('device_id', '=', $req->get('device_id'));
			}
			if ($req->get('cupon_id'))
			{
				$query->where('cupon_id', '=', $req->get('cupon_id'));
			}
			$count = $query->count();
			
			
			if ($req->get('p'))
			{
				$page = $req->get('p');
			}
			else
			{
				$page = 0;
			}
			$query->take($perpage);
			$query->skip($perpage * $page);
			
			
			$links = [];
			if ($page > 0)
			{
				$prev = clone $req;
				$prev->query->remove('p');
				$prev->query->set('p', $page-1);
				
				$links[] = '<'.$prev->getSchemeAndHttpHost().
						$prev->getBaseUrl() . 
						$prev->getPathInfo() . '?' .
						http_build_query($prev->query->all()).">; ".
					"rel=\"prev\"";
			}
			if ($page < (round($count/$perpage)))
			{
				$next = clone $req;
				$next->query->set('p', $page+1);
				
				$links[] = '<'.$next->getSchemeAndHttpHost().
						$next->getBaseUrl() . 
						$next->getPathInfo() . '?' .
						http_build_query($next->query->all()).">; ".
					"rel=\"next\"";
			}
			
			
			if (count($links) > 0)
			{
				$resp->headers->set('Link', implode(', ', $links));
			}
			
			$resp->headers->set('X-Total-Count', $count);
			$resp->headers->set('X-Total-Pages', $count/$perpage);
			$resp->setData($query->get()->toArray());
			
			return $resp;
		});
		
		$controller->get("/{id}", function(Request $req, $id) {
			$redemption = Model\Redemption::find($id);
			return new JsonResponse($redemption->toArray());
		});
		
		
		return $controller;
	}
}
