<?php

namespace bundles\bruno\www\routes;

$app = \Slim\Slim::getInstance();

$app->get('/', function () use($app) {
	$app->bruno->data['page_redirect'] = '';
	$app->bruno->data['link_reset'] = true;
	$app->render('/bundles/bruno/www/templates/launch/home.twig');
})
->name('www_root');

//Return at the home page
$app->get('/home', function () use($app) {
	$app->bruno->data['page_redirect'] = '';
	$app->bruno->data['link_reset'] = true;
	$app->render('/bundles/bruno/www/templates/launch/home.twig');
})
->name('www_home');



//Used for direct linked "forgot password", "sigin", etc
$app->get('/user/:user_action', function ($user_action='') use ($app) {
	$app->bruno->data['user_action'] = $user_action;
	$app->bruno->data['link_reset'] = true;
	$app->render('/bundles/bruno/www/templates/launch/home.twig');
})
->conditions(array(
	'user_action' => '\S+',
))
->name('www_user');
