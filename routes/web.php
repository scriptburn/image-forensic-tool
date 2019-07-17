<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->group(['middleware' => 'auth'], function () use ($router)
{
	$router->get('/', ['as' => 'home', function () use ($router)
	{
		return $router->app->make('view')->make('home');
	}]);

	$router->post('/visionapi', ['as' => 'visionapi', function () use ($router)
	{
 		try
		{
			if (empty($_FILES['files']))
			{
				throw new \Exception("Please uplaod an image");
			}
			else
			{
				$api = new ScriptBurn\Api\GoogleVison\VisionApi(getenv('SERVICE_ACCOUNT') );
				$response = $api->processImage($_FILES['files']['tmp_name']);

				if (!$response[0])
				{
					throw new \Exception($response[2]);
				}
				$data = ['status' => 1, 'message' => "", 'data' => view('response')->with('data', array_merge($response[1], ['name' => $_FILES['files']['name']]))->render()];
			}
		}
		catch (\Exception $e)
		{
			$data = ['status' => 0, 'message' => $e->getMessage(), 'data' => ""];
		}

		return response()->json($data);
	}]);
});

$router->get('/login', ['as' => 'login', function () use ($router)
{

	return $router->app->make('view')->make('login');
}]);
$router->post('/login', ['as' => 'doLogin', function () use ($router)
{
	$req = $router->app->make('request');
 	if ($req->input('email') == getenv('LOGIN_EMAIL') && $req->input('password') == getenv('LOGIN_PASS'))
	{
		$_SESSION['user'] = $req->input('email');

		return redirect(route('home'));
	}
	else
	{
		$_SESSION['error'] = 'Invalid user or password';

		return redirect(route('login'));
	}
}]);

$router->get('/logout', ['as' => 'logout', function () use ($router)
{
	unset($_SESSION['user']);

	return redirect(route('home'));
}]);