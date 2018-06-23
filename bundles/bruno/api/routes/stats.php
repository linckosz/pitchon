<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/stats', function() use ($app) {

	$app->post(
		'/session',
		'\bundles\bruno\api\controllers\ControllerStats:session_post'
	)
	->name('api_stats_session_post');

	$app->get(
		'/session/:md5/:id/:category',
		'\bundles\bruno\api\controllers\ControllerStats:session_get'
	)
	->conditions(array(
		'md5' => '[\d\w]+',
		'id' => '\d+',
		'category' => 'session|statistics|answered',
	))
	->name('api_stats_session_get');

	$app->post(
		'/statistics',
		'\bundles\bruno\api\controllers\ControllerStats:statistics_post'
	)
	->name('api_stats_statistics_post');

	$app->post(
		'/adclick',
		'\bundles\bruno\api\controllers\ControllerStats:adclick_post'
	)
	->name('api_stats_adclick_post');

});
