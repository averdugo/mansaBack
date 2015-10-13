<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
			
			try
			{
				// Generate a version 4 (random) UUID object
				return json_encode(['uuid' => uniqid()]);
			}
			catch (UnsatisfiedDependencyException $e)
			{
				// Some dependency was not met. Either the method cannot be called on a
				// 32-bit system, or it can, but it relies on Moontoast\Math to be present.
				throw new \Exception("server error: ".$e->getMessage());
			}
			
		});
		
		return $controller;
	}
}