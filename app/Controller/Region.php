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
/*  Region Controller                                                         */
/*                                                                            */
/* All Your Regions are Belong to Us!                                         */
/*                                                                            */
/******************************************************************************/
class Region implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		
		$controller->get('/comune', function() {
			$comunes = Model\Comune::where('active', '=', true)
				->orderBy('name')
				->get();
			return new JsonResponse($comunes->toArray());
		});
		
		$controller->get('/comune/{id}', function($id) {
			$comune = Model\Comune::find($id);
			if (!$comune)
			{
				return new NotFoundHttpException(
					'No such comune'
				);
			}
			
			return new JsonResponse($cupon->toArray());
		});
		
		
		return $controller;
	}
}
