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
		
		$controller->get('/{id}', function($id) use ($imgmgr) {
			
			$image = Model\Image::find($id);
			if (!$image)
			{
				return new NotFoundHttpException('No such image');
			}
			
			$img = $imgmgr->make(base64_decode($image->data));
			
			return new Response(
				$img->encode('jpg'), 200, 
				['Content-Type' => 'image/jpeg']
			);
		});
		
		$controller->get('/{sx}/{sy}/{id}', function($sx, $sy, $id) use ($imgmgr) {
			
			$image = Model\Image::find($id);
			if (!$image)
			{
				return new NotFoundHttpException('No such image');
			}
			
			if ($sx == 0) $sx = null;
			if ($sy == 0) $sy = null;
			
			$img = $imgmgr->make(base64_decode($image->data));
			$img->resize($sx, $sy, function($constraint) {
				$constraint->aspectRatio();
			})
			//->blur(2)
			//->sharpen(10)
			;
			
			return new Response(
				$img->encode('jpg'), 200, 
				['Content-Type' => 'image/png']
			);
		});
		
		return $controller;
	}
}
