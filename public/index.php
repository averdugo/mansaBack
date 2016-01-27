<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

use Silex\Application;

use Authority\Authority;


require_once __DIR__ . '/../vendor/autoload.php';


$phinx = Yaml::parse(file_get_contents(__DIR__ . '/../phinx.yml'));
if (isset($_ENV['ENVIRONMENT']))
{
	$database = @$phinx['environments'][$_ENV['ENVIRONMENT']];
}
else
{
	$database = @$phinx['environments'][@$phinx['environments']['default_database']];
}


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

$app->mount('/user', new App\Controller\Login);
$app->mount('/store', new App\Controller\Store);
$app->mount('/cupon', new App\Controller\Cupon);
$app->mount('/device', new App\Controller\Device);
$app->mount('/image', new App\Controller\Image);
$app->mount('/redemption', new App\Controller\Redemption);
$app->mount('/slideshow', new App\Controller\Slideshow);


$app['capsule'];


//handling CORS preflight request
$app->before(function (Request $request, Application $app) {
	
	$app['user'] = $app->share(function($c) use ($app) {
		//trigger_error("Retrieving user login for: ".$app['session']->get('user_id'));
		return App\Model\Login::find($app['session']->get('user_id'));
	});
	
	$app['authority'] = $app->share(function($c) use ($app) {
		return new Authority($app['user']);
	});
	
	if ($request->getMethod() === "OPTIONS") {
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type,X-DEVICE-ID");
		$response->setStatusCode(200);
		return $response;
	}
}, Application::EARLY_EVENT);


//handling CORS respons with right headers
$app->after(function (Request $req, Response $res) {
	
	if ($req->getMethod() != "OPTIONS")
	{
		$res->headers->set("Access-Control-Allow-Origin","*");
		$res->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
	}
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

$app->error(function (\Exception $e, $code) use ($app) {
	return new JsonResponse([
		"statusCode"	=> $code,
		"message"	=> $e->getMessage(),
		"stacktrace"	=> $e->getTraceAsString()
	]);
});

$app->run();
