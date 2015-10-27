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
/*  Cupon Controller                                                          */
/*                                                                            */
/* Blow at Half Price! Going, Going... GONE!!!                                */
/*                                                                            */
/******************************************************************************/
class Image implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		$imgmgr = new \Intervention\Image\ImageManager;
		$controller->put('/', function(Application $app, Request $req) use ($imgmgr) {
			
			$image = new Model\Image;
			$img = $imgmgr->make($req->getContent());
			$image->mimetype = $img->mime();
			$image->login_id = $app['session']->get('user_id');
			$image->data = base64_encode($req->getContent());
			$image->save();
			
			return json_encode($image->toJSON());
		});
		
		$controller->get('/{id}', function(Request $req, $id) use ($imgmgr) {
			
			$image = Model\Image::find($id);
			if (!$image)
			{
				return new NotFoundHttpException('No such image');
			}
			
			$img = $imgmgr->make(base64_decode($image->data));
			
			if ($req->get('sx') || $req->get('sy'))
			{
				$img->resize(
					$req->get('sx', null),
					$req->get('sy', null),
					function($constraint) use ($req) {
						
						if (!$req->get('sx') || !$req->get('sy'))
						{
							$constraint->aspectRatio();
						}
					}
				);
			}
			
			return new Response(
				$img->encode('jpg'), 200, 
				['Content-Type' => 'image/jpeg']
			);
		});
		
		return $controller;
	}
}
