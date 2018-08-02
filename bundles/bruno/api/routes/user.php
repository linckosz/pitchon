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

	$app->post(
		'/search',
		'\bundles\bruno\api\controllers\ControllerUser:search_post'
	)
	->name('api_user_search_post');

	$app->post(
		'/promocode',
		'\bundles\bruno\api\controllers\ControllerUser:promocode_post'
	)
	->name('api_user_promocode_post');

});
