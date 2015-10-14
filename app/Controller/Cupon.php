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
					'distance'	=> $req->get('maxdist')
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
				
				$q->select('*');
				
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
			
			$query->whereHas('store', function($q) use ($req, $geo) {
				
				if ($geo)
				{
					$lat		= $req->get('lat');
					$lon		= $req->get('lon');
					$distance	= $req->get('maxdist');
					
					$q->whereRaw(
						"ST_DWithin(location, ST_GeographyFromText(?), ?)",
						["SRID=4326;POINT({$geo->lat} {$geo->lon})", $distance]
					);
				}
			});
			
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
