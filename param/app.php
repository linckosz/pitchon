<?php

$path = $_SERVER['DOCUMENT_WWW'];

require_once $path.'/vendor/autoload.php';

$app = new \Slim\Slim();

require_once $path.'/config/global.php';
require_once $path.'/config/language.php';
require_once $path.'/param/config.php';
require_once $path.'/param/unique/env.php';

// Only invoked if mode is "production"
$app->configureMode('production', function() use ($app) {
	$app->config(array(
		'log.enable' => true,
	));
	ini_set('display_errors', '0');
});

// Only invoked if mode is "development"
$app->configureMode('development', function() use ($app) {
	//usleep(500000); //To simulate a slow connection, use 500ms delay
	$app->config(array(
		'log.enable' => false,
		'cookies.secure' => false, //Allow non-SSL record
	));
	//ini_set('display_errors', '0');
	ini_set('opcache.enable', '0');
	$app->bruno->showError = true; //Force to see Error message
	$app->bruno->data['bruno_show_dev'] = 'true'; //Show some errors for Front end developpers (NOTE: it has to be a string because of Twig conversion to JS)
	//Only useful in rendering mode, useless in JSON mode
	//$debugbar = new \Slim\Middleware\DebugBar();
	//$app->add($debugbar);
});

require_once $path.'/config/autoload.php';
require_once $path.'/error/errorPHP.php';
require_once $path.'/config/eloquent.php';
require_once $path.'/config/session.php';

//echo 'in maintenance';
$app->run();
