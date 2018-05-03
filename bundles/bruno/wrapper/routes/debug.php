<?php

namespace bundles\bruno\wrapper\routes;

$app = \Slim\Slim::getInstance();

$app->group('/wrapper/debug', function() use ($app) {
	
	if($app->getMode()=='development'){
		
		$app->map('/', function() use ($app) {
			$data = NULL; //Just in order to avoid a bug if we call it in debug.php
			include($app->bruno->path.'/error/debug.php');
		})
		->via('GET', 'POST')
		->name('debug_all');
		
		$app->get('/md5', function() use ($app) {
			include($app->bruno->path.'/error/md5.php');
		})
		->name('debug_md5_get');
		
		$app->get('/twig', function() use ($app) {
			$app->render('/bundles/bruno/wrapper/templates/debug.twig', array(
				'data' => 'a data',
			));
		});
	}

	//Catch JS message error
	$app->post('/js', function() use ($app) {
		\libs\Watch::js();
	});

});
