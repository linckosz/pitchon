<?php

namespace bundles\bruno\wrapper\routes;

$app = \Slim\Slim::getInstance();

$app->group('/wrapper/export', function() use ($app) {

	$app->get(
		'/data.csv',
		'\bundles\bruno\wrapper\controllers\ControllerExport:csv_get'
	)
	->name('export_csv');

});
