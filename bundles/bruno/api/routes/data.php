<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/data', function() use ($app) {

	$app->post(
		'/latest',
		'\bundles\bruno\api\controllers\ControllerData:latest_post'
	)
	->name('api_data_latest_post');

	$app->post(
		'/set',
		'\bundles\bruno\api\controllers\ControllerData:set_post'
	)
	->name('api_data_set_post');

});
