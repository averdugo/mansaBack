<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/******************************************************************************/
/*  User Controller                                                           */
/*                                                                            */
/* Manage Users and Sessions ... Maybe separate sessions out later?           */
/*                                                                            */
/******************************************************************************/
class Login implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller = $app['controllers_factory'];
		
		$controller->put('/register', function(Request $req) {
			
			$login = new Model\Login();
			$login->email		= $req->get('email');
			$login->fname		= $req->get('fname');
			$login->lname		= $req->get('lname');
			$login->phone		= $req->get('phone');
			$login->password	= password_hash($req->get('password'), PASSWORD_BCRYPT);
			
			$login->save();
			return $login->toJSON();
		});
		
		$controller->post('/login', function(Application $app, Request $req) {
			
			$login = Model\Login::where('email', '=', $req->get('email'))
				->with('stores')
				->first();
			
			if (!$login)
			{
				throw new \Exception("Unable to Login");
			}
			
			$rc = password_verify($req->get('password'), $login->password);
			if ($rc)
			{
				$app['session']->set('user_id', $login->id);
				return $login->toJSON();
			}
			
			throw new \Exception('Unable to Login');
		});
		
		$controller->get('/current', function(Application $app) {
			return json_encode(['user_id' => $app['session']->get('user_id')]);
		});
		
		$controller->post('/logout', function(Application $app, Request $req) {
			
			$app['session']->remove('user_id');
			return json_encode(['is_logged_out' => true]);
		});
		
		return $controller;
	}
}
