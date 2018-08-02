<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/info', function() use ($app) {

	$app->post(
		'/action',
		'\bundles\bruno\api\controllers\ControllerInfo:action_post'
	)
	->name('api_info_action_post');

	$app->get(
		'/wait/:seconds.xml',
		'\bundles\bruno\api\controllers\ControllerInfo:wait_get'
	)
	->conditions(array(
		'seconds' => '\d+',
	))
	->name('api_info_wait_get');

});
