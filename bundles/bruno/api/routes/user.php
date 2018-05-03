<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/user', function() use ($app) {

	$app->post(
		'/signin',
		'\bundles\bruno\api\controllers\ControllerUser:signin_post'
	)
	->name('api_user_signin_post');

	$app->post(
		'/signout',
		'\bundles\bruno\api\controllers\ControllerUser:signout_post'
	)
	->name('api_user_signout_post');

});
