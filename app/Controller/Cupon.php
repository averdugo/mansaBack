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
			$cupon->description = $req->get('description');
			$cupon->price = $req->get('price');
			$cupon->stock = $req->get('stock');
			$cupon->save();
			
			return $cupon->toJSON();
		});
		
		$controller->get('/', function(Application $app, Request $req) {
			
			$db = $app['capsule']->connection();
			$query = Model\Cupon::query();
			
			
			if ($req->get('lat') && $lon = $req->get('lon') && $req->get('maxdist'))
			{
				$geo = (object)[
					'lat'		=> $req->get('lat'),
					'lon'		=> $req->get('lon'),
					'dst'	=> $req->get('maxdist')
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
					'dst'	=> $parts[2],
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
			
			
			if ($geo)
			{
				$query->with(['store' => function($q) use ($req, $db, $geo) {
					
					$q->select('*');
					$q->addSelect(
						$db->raw('ST_Distance('.
							'location::geometry'.
							", ST_GeographyFromText('SRID=4326;POINT({$geo->lat} {$geo->lon})')) ".
							"as distance"
						)
					);
				
				}]);
				
				$query->whereHas('store', function($q) use ($req, $geo) {
					
					$q->whereRaw(
						"ST_DWithin(location, ST_GeographyFromText(?), ?)",
						["SRID=4326;POINT({$geo->lat} {$geo->lon})", $geo->dst]
					);
				});
			}
			else if ($req->get('q'))
			{
				$query->whereRaw(
					"to_tsvector('english', description) @@ to_tsquery(?)",
					[$req->get('q')]
				);
			}
			else
			{
				$query->whereHas('store', function($q) use ($app) {
					$q->where('login_id', '=', $app['session']->get('user_id'));
				});
			}
			
			
			$cupons = $query->get();
			return $cupons->toJSON();
		});
		
		$controller->get('/{id}', function(Application $app, $id) {
			$cupons = Model\Cupon::with('store')->find($id);
			return $cupons->toJSON();
		});
		
		
		return $controller;
	}
}
