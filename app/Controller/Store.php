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
		
		
		$app['authority.redemption'] = function($app) {
			
			$app['authority']->allow('read', 'App\Model\Redemption',
				// TODO: restrict access to redemptions to the
				// redeeming user and/or store owners...
				function($self, Model\Redemption $redemption) {
					return true;
				}
			);
			
			$app['authority']->allow('create', 'App\Model\Redemption', 
				function($self, Model\Redemption $redemption) {
					return $self->user()->stores()
						->where('id', '=', $redemption->cupon->store_id)
						->count() >= 1;
				}
			);
			
			$app['authority']->allow('update', 'App\Model\Redemption',
				function($self, Model\Redemption $redemption) {
					
					$changed = array_keys($redemption->getDirty());
					
					if (count($changed) == 1 && $changed[0] == 'is_redeemed')
					{
						return true;
					}
					
					return false;
				}
			);
			
			return $app['authority'];
		};
		
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
			
			$db = $app['capsule']->connection();
			
			
			$store = new Model\Store;
			
			
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
			
			
			if ($req->get('image_id'))
			{
				$image = Model\Image::find($req->get('image_id'));
				if (!$image)
				{
					throw new NotFoundHttpException('No such image');
				}
				
				$store->image()->associate($image);
			}
			
			if ($req->get('storetype_id'))
			{
				$storetype = Model\Storetype::find($req->get('storetype_id'));
				if (!$storetype)
				{
					throw new NotFoundHttpException('No such store type');
				}
				
				$store->storetype()->associate($storetype);
			}
			
			$store->login_id	= $app['session']->get('user_id');
			$store->address		= $req->get('address');
			$store->comuna		= $req->get('comuna');
			$store->region		= $req->get('region');
			$store->name		= $req->get('name');
			$store->hours		= json_encode($req->get('hours'));
			$store->phone		= $req->get('phone');
			$store->location	= $db->raw("ST_GeographyFromText('SRID=4326;POINT({$lat} {$lon})')");
			$store->save();
			
			return new JsonResponse($store->toArray());
		});
		
		$controller->patch('/{id}', function(Application $app, Request $req, $id) {
			
			$db = $app['capsule']->connection();
			
			
			$lat = $req->get('lat');
			$lon = $req->get('lon');
			
			
			$store = Model\Store::find($id);
			
			foreach (['address', 'name', 'comuna', 'region', 'location', 'hours', 'telephone'] as $field)
			{
				
				if (($value = $req->get($field)) !== null)
				{
					if ($field == 'location')
					{
						list($lat, $lon) = explode(',', $value);
						$store->location = $db->raw("ST_GeographyFromText('SRID=4326;POINT({$lat} {$lon})')");
					}
					else
					{
						$store->{$field} = $value;
					}
				}
			}
			
			
			if ($req->get('image_id'))
			{
				$image = Model\Image::find($req->get('image_id'));
				if (!$image)
				{
					throw new NotFoundHttpException('No such image');
				}
				
				$store->image()->associate($image);
			}
			
			
			if ($req->get('storetype_id'))
			{
				$storetype = Model\StoreType::find($req->get('storetype_id'));
				if (!$storetype)
				{
					throw new NotFoundHttpException('No such store type');
				}
				
				$store->storetype()->associate($storetype);
			}
			
			
			$store->save();
			return new JsonResponse($store->toArray());
		});
		
		$controller->get('/{id}', function(Application $app, Request $req, $id) {
			
			$store = Model\Store::find($id);
			if (!$store)
			{
				throw new NotFoundHttpException('No such image');
			}
			
			
			$store->cupons = ['redeemed' => 
				Model\Redemption::whereHas('cupon', function($q) use ($store) {
					$q->where('store_id', '=', $store->id);
				})
				->count()
			];
			
			
			return new JsonResponse($store->toArray());
		});
		
		$controller->get('/', function(Application $app, Request $req) {
			
			if ($req->get('lat') && $lon = $req->get('lon') && $req->get('maxdist'))
			{
				$db = $app['capsule']->connection();
				
				$geo = (object)[
					'lat'		=> $req->get('lat'),
					'lon'		=> $req->get('lon'),
					'distance'	=> $req->get('maxdist')
				];
				
				foreach ($geo as $key => $val)
				{
					if (!is_numeric($val))
					{
						throw new \Exception('invalid value: '.$key);
					}
				}
				
				$query = Model\Store::
					addSelect(
						$db->raw('ST_Distance('.
							'location::geometry'.
							", ST_GeographyFromText('SRID=4326;POINT({$geo->lat} {$geo->lon})')) ".
							"as distance"
						)
					);
					/*
					->whereRaw(
						"ST_DWithin(location, ST_GeographyFromText(?), ?)",
						["SRID=4326;POINT({$geo->lat} {$geo->lon})", $geo->distance]
					);
					*/
			}
			else
			{
				$user_id = $app['session']->get('user_id');
				$query = Model\Store::where('login_id', '=', $user_id);
			}
			
			
			$stores = $query->get();
			return new JsonResponse($stores->toArray());
		});
		
		return $controller;
	}
}
