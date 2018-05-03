<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/test', function() use ($app) {

	$app->map(
		'/',
		'\bundles\bruno\api\controllers\ControllerTest:test'
	)
	->via('GET', 'POST')
	->name('api_test_post');

});
