<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

use Silex\Application;


require_once __DIR__ . '/../vendor/autoload.php';


$phinx = Yaml::parse(file_get_contents(__DIR__ . '/../phinx.yml'));
$database = $phinx['environments']['development'];


$app = new Application;
$app->register(
	new \BitolaCo\Silex\CapsuleServiceProvider(),
	['capsule.connection' => [
		'driver'	=> $database['adapter'],
		'host'		=> $database['host'],
		'database'	=> $database['name'],
		'username'	=> $database['user'],
		'password'	=> $database['pass'],
		'charset'	=> $database['charset'],
		//'collation'	=> 'utf8_unicode_ci',
		'prefix'	=> '',
		'logging'	=> true, // Toggle query logging on this connection.
	]]
);

$app->register(new Silex\Provider\SessionServiceProvider());

$app['capsule'];

class Model extends Illuminate\Database\Eloquent\Model
{
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}

class Login extends Model
{
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'created_at', 'updated_at', 'deleted_at'];
	
	public function stores()
	{
		return $this->hasMany('Store');
	}
}

class Store extends Model
{
}

class Cupon extends Model
{
}

//handling CORS preflight request
/*
$app->before(function (Request $request) {
	if ($request->getMethod() === "OPTIONS") {
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		$response->setStatusCode(200);
		return $response->send();
	}
}, Application::EARLY_EVENT);
*/

//handling CORS respons with right headers
$app->after(function (Request $req, Response $res) {
	$res->headers->set("Content-Type","application/json");
});

$app->before(function(Request $req) {
	
	if ($req->headers->get('Content-Type') == 'application/json')
	{
		$req->request->replace(
			$req->getContent() ?
				json_decode($req->getContent(), true) :
				[]
		);
	}
	
});

/******************************************************************************/
/*  User Controller                                                           */
/*                                                                            */
/* Manage Users and Sessions ... Maybe separate sessions out later?           */
/*                                                                            */
/******************************************************************************/
$app->put('/user/register', function(Request $req) {
	
	$login = new Login();
	$login->email		= $req->get('email');
	$login->password	= password_hash($req->get('password'), PASSWORD_BCRYPT);
	
	$login->save();
	return $login->toJSON();
});

$app->post('/user/login', function(Application $app, Request $req) {
	
	$login = Login::where('email', '=', $req->get('email'))
		->first();
	
	$rc = password_verify($req->get('password'), $login->password);
	if ($rc)
	{
		$app['session']->set('user_id', $login->id);
	}
	
	return json_encode(['is_logged_in' => $rc]);
});

$app->get('/user/current', function(Application $app) {
	return json_encode(['user_id' => $app['session']->get('user_id')]);
});

$app->post('/user/logout', function(Application $app, Request $req) {
	
	$app['session']->remove('user_id');
	return json_encode(['is_logged_out' => true]);
});

/******************************************************************************/
/*  Store Controller                                                          */
/*                                                                            */
/* Lets open a Speak Easy, Bitch! Time for women and booze, Sheen Style!      */
/*                                                                            */
/******************************************************************************/
$app->put('/store', function(Application $app, Request $req) {
	
	$store = new Store;
	$store->login_id = $app['session']->get('user_id');
	$store->address = $req->get('address');
	$store->comuna = $req->get('comuna');
	$store->region = $req->get('region');
	$store->save();
	
	return $store->toJSON();
});

$app->get('/store', function(Application $app) {
	
	$stores = Store::where('login_id', '=', $app['session']->get('user_id'))
		->get();
	
	return $stores->toJSON();
});

/******************************************************************************/
/*  Cupon Controller                                                          */
/*                                                                            */
/* Blow at Half Price! Going, Going... GONE!!!                                */
/*                                                                            */
/******************************************************************************/
$app->put('/cupon', function(Application $app, Request $req) {
	
	$cupon = new Cupon;
	$cupon->store_id = $req->get('store_id');
	$cupon->label = $req->get('label');
	$cupon->drink = $req->get('drink');
	$cupon->price = $req->get('price');
	$cupon->save();
	
	return $cupon->toJSON();
});


$app->error(function (\Exception $e, $code) use ($app) {
	return new JsonResponse([
		"statusCode"	=> $code,
		"message"	=> $e->getMessage(),
		"stacktrace"	=> $e->getTraceAsString()
	]);
});

$app->run();
