<?php

namespace bundles\bruno\app\routes;

$app = \Slim\Slim::getInstance();

$app->get(
	'/',
	'\bundles\bruno\app\controllers\ControllerApp:_get'
)
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
