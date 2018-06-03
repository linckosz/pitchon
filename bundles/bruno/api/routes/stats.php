<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/stats', function() use ($app) {

	$app->post(
		'/session',
		'\bundles\bruno\api\controllers\ControllerStats:session_post'
	)
	->name('api_data_session_post');

});
