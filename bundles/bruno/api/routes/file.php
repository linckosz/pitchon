<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/file', function() use ($app) {

	$app->map(
		'/upload',
		'\bundles\bruno\api\controllers\ControllerFile:upload'.$app->bruno->method_suffix
	)
	->via('POST', 'OPTIONS')
	->name('api_file_upload_map');

	$app->map(
		'/result',
		'\bundles\bruno\api\controllers\ControllerFile:result'
	)
	->via('GET', 'POST', 'OPTIONS', 'HEAD')
	->name('api_file_result_map');

	$app->get(
		'/:type/:md5/:id/:name',
		'\bundles\bruno\api\controllers\ControllerFile:open_get'
	)
	->conditions(array(
		'type' => 'link|thumbnail|download',
		'md5' => '[\d\w]+',
		'id' => '\d+',
		'name' => '.+',
	))
	->name('api_file_open_get');

	$app->get(
		'/myqrcode/:md5/:id/:name',
		'\bundles\bruno\api\controllers\ControllerFile:qrcode_get'
	)
	->conditions(array(
		'md5' => '[\d\w]+',
		'id' => '\d+',
		'name' => '.+',
	))
	->name('api_file_myqrcode_get');

	$app->post(
		'/progress/:id',
		'\bundles\bruno\api\controllers\ControllerFile:progress_post'
	)
	->conditions(array(
		'id' => '\d+',
	))
	->name('api_file_progress_post');

});
