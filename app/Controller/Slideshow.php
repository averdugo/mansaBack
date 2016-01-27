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
/*  Slideshow Controller                                                      */
/*                                                                            */
/* ADS!                                                                       */
/*                                                                            */
/* Final Format for Slides (for now...):                                      */
/* [                                                                          */
/*   [                                                                        */
/*     'id'	=> 1,                                                         */
/*     'order'	=> 1,                                                         */
/*     'image'	=> ['id' => 19, 'mimetype' => 'image/png'],                   */
/*     'url'	=> '#app/cupons'                                              */
/*   ],                                                                       */
/* ]                                                                          */
/*                                                                            */
/******************************************************************************/
class Slideshow implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		
		$controller->get('/', function(Application $app, Request $req) {
			
			// Fake the current slideshows from the images loaded by
			// a migration (the first one *should* be the store logo)
			$images = Model\Image::where('login_id', '=', null)
				->orderBy('id')
				->get();
			
			$slideshows = $images->map(function($image) {
				return [
					'id'	=> $image->id,
					'image'	=> $image->toArray(),
					'url'	=> null
				];
			})->toArray();
			
			array_shift($slideshows);
			return new JsonResponse($slideshows);
		});
		
		return $controller;
	}
}
