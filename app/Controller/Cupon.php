<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
		
		
		$app['authority.cupon'] = function($app) {
			
			$app['authority']->addAlias('manage', ['create', 'update', 'delete']);
			
			$app['authority']->allow('read', 'App\Model\Cupon');
			$app['authority']->allow('manage', 'App\Model\Cupon', function($self, Model\Cupon $cupon) {
				
				$canStore = $self->user()->stores()
					->where('id', '=', $cupon->store_id)
					->count() >= 1;
				
				$canImage = $cupon->image_id ?
					$self->user()->id == $cupon->image->login_id :
					true;
				
				return $canStore && $canImage;
			});
			
			return $app['authority'];
		};
		
		$controller->put('/', function(Application $app, Request $req) {
			
			$cupon = new Model\Cupon;
			
			
			if ($req->get('image_id'))
			{
				$image = Model\Image::find($req->get('image_id'));
				if (!$image)
				{
					return new NotFoundHttpException('No such image');
				}
				
				$cupon->image()->associate($image);
			}
			
			
			$cupon->store_id	= $req->get('store_id');
			$cupon->expires_at	= $req->get('expires_at');
			$cupon->title		= $req->get('title');
			$cupon->description	= $req->get('description');
			$cupon->price		= $req->get('price');
			$cupon->stock		= $req->get('stock');
			
			if (!$app['authority.cupon']->can('create', $cupon))
			{
				throw new AccessDeniedHttpException('Cannot create cupon (for this store)');
			}
			
			
			$cupon->save();
			return new JsonResponse($cupon->toArray());
		});
		
		$controller->get('/', function(Application $app, Request $req) {
			
			$db = $app['capsule']->connection();
			$query = Model\Cupon::query();
			
			
			if ($req->get('lat') && $lon = $req->get('lon') && $req->get('maxdist'))
			{
				$geo = (object)[
					'lat'		=> $req->get('lat'),
					'lon'		=> $req->get('lon'),
					'dst'		=> $req->get('maxdist')
				];
				
				foreach ($geo as $key => $val)
				{
					if (!is_numeric($val))
					{
						throw new \Exception('invalid value: '.$key);
					}
				}
			}
			else if ($req->get('g'))
			{
				$parts = explode(',', $req->get('g'));
				$geo = (object)[
					'lat'	=> $parts[0],
					'lon'	=> $parts[1],
					'dst'	=> @$parts[2],
				];
				
				foreach ($geo as $key => $val)
				{
					if (!is_numeric($val))
					{
						throw new \Exception('invalid value: '.$key);
					}
				}
			}
			else
			{
				$geo = null;
			}
			
			
			$query->with(['store' => function($q) use ($req, $db, $geo) {
				
				if ($geo)
				{
					$q->addSelect(
						$db->raw('ST_Distance('.
							'location::geometry'.
							", ST_GeographyFromText('SRID=4326;POINT({$geo->lat} {$geo->lon})')) ".
							"as distance"
						)
					);
				}
			}]);
			
			
			if ($geo && $geo->dst)
			{
				$query->whereHas('store', function($q) use ($req, $geo) {
					
					$q->whereRaw(
						"ST_DWithin(location, ST_GeographyFromText(?), ?)",
						["SRID=4326;POINT({$geo->lat} {$geo->lon})", $geo->dst]
					);
				});
			}
			
			if ($req->get('q'))
			{
				$query->whereRaw(
					"to_tsvector('english', title || ' ' || description) @@ plainto_tsquery(?)",
					[$req->get('q')]
				);
			}
			
			if ($req->get('p'))
			{
				$query->whereBetween(
					'price',
					explode('-', $req->get('p'))
				);
			}
			
			if ($req->get('c'))
			{
				$query->whereHas('store', function($q) use ($req, $db) {
					$q->where(
						$db->raw('LOWER(comuna)'), '=', 
						strtolower($req->get('c'))
					);
				});
			}
			
			if ($req->get('s'))
			{
				$query->where('store_id', '=', $req->get('s'));
			}
			
			if (!$geo && !$req->get('q') && !$req->get('p') && !$req->get('c') && !$req->get('s'))
			{
				$query->whereHas('store', function($q) use ($app) {
					$q->where('login_id', '=', $app['session']->get('user_id'));
				});
			}
			
			
			$cupons = array_filter(
				function($cupon) use ($app) {
					return $app['authority.cupon']->can('read', $cupon);
				},
				$query->get()
			);
			
			return new JsonResponse($cupons->toArray());
		});
		
		$controller->get('/view/{id}', function(Application $app, $id) {
			
			$cupon = Model\Cupon::withExpired()->with('store')->find($id);
			if (!$cupon)
			{
				throw new NotFoundHttpException("No existe el Cupon");
			}
			if (!$app['authority.cupon']->can('read', $cupon))
			{
				throw new AccessDeniedHttpException('Cannot read cupon');
			}
			
			if ($cupon->stock !== null)
			{
				$redemptions = Model\Redemption
					::where('cupon_id', '=', $cupon->id)
					->count();
				$cupon->left = $cupon->stock - $redemptions;
			}
			
			return (new \App\View('cupon/view'))->cupon($cupon);
		});
		
		$controller->get('/{id}', function(Application $app, Request $req, $id) {
			
			$db = $app['capsule']->connection();
			$query = Model\Cupon::withExpired();
			
			if ($req->get('g'))
			{
				$parts = explode(',', $req->get('g'));
				$geo = (object)[
					'lat'	=> $parts[0],
					'lon'	=> $parts[1]
				];
				
				foreach ($geo as $key => $val)
				{
					if (!is_numeric($val))
					{
						throw new \Exception('invalid value: '.$key);
					}
				}
			}
			else 
			{
				$geo = null;
			}
			
			$query->with(['store' => function($q) use ($db, $geo) {
				
				if ($geo)
				{
					$q->addSelect(
						$db->raw('ST_Distance('.
							'location::geometry'.
							", ST_GeographyFromText('SRID=4326;POINT({$geo->lat} {$geo->lon})')) ".
							"as distance"
						)
					);
				}
				
			}]);
			
			$cupon = $query->find($id);
			if (!$cupon)
			{
				throw new NotFoundHttpException("No existe el Cupon");
			}
			
			if ($cupon->stock !== null)
			{
				$redemptions = Model\Redemption
					::where('cupon_id', '=', $cupon->id)
					->count();
				
				$cupon->left = $cupon->stock - $redemptions;
			}	
			
			return new JsonResponse($cupon->toArray());
		});
		
		
		return $controller;
	}
}
