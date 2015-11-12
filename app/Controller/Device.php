<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

/******************************************************************************/
/* Device Controller                                                          */
/*                                                                            */
/* Give the new guys a random ID, later link those IDs to logins?             */
/*                                                                            */
/******************************************************************************/
class Device implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		$controller->put('/', function(Application $app, Request $request) {
			
			$device = new Model\Device;
			
			$device->device_id	= uniqid();
			$device->email		= $request->get('email');
			$device->save();
			
			return new JsonResponse($device->toArray());
		});
		
		return $controller;
	}
}
