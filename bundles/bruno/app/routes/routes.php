<?php

namespace bundles\bruno\app\routes;

use \libs\Vanquish;
use \bundles\bruno\wrapper\models\Action;

$app = \Slim\Slim::getInstance();

$app->get('/', function() use ($app) {
	if($app->bruno->data['user_id']){
		$user_info = Action::getUserInfo();
		foreach ($user_info as $key => $value) {
			$app->bruno->data['user_info_'.$key] = $value;
		}
		$app->render('/bundles/bruno/app/templates/app/application.twig');
	} else {
		if(Vanquish::get('remember')){
			//It feels better to keep track of last email login
			Vanquish::unsetAll(array('user_language', 'remember', 'user_email'));
		} else {
			Vanquish::unsetAll(array('user_language', 'remember'));
		}
		$app->render('/bundles/bruno/app/templates/login.twig');
	}
})
->name('_get');

$app->get(
	'/username',
	'\bundles\bruno\app\controllers\ControllerApp:username_get'
)
->name('app_username_get');

$app->post(
	'/refresh',
	'\bundles\bruno\app\controllers\ControllerApp:refresh_post'
)
->name('app_refresh_post');

$app->group('/sample', function() use ($app) {

	$app->get(
		'/pitch/:pitchid',
		'\bundles\bruno\app\controllers\ControllerApp:sample_pitch_get'
	)
	->conditions(array(
		'pitchid' => '[a-z0-9]+',
	))
	->name('app_sample_pitch_get');

});

/*
$app->get('/login', function() use ($app) {
	$app->render('/bundles/bruno/app/templates/login.twig');
})
->name('app_login_get');
*/
