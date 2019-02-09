<?php

namespace bundles\bruno\www\routes;

$app = \Slim\Slim::getInstance();

$app->get(
	'/:content',
	'\bundles\bruno\www\controllers\ControllerWeb:_get'
)
->conditions(array(
	'content' => '[a-zA-Z0-9]*',
))
->name('www_content');



//Used for direct linked "forgot password", "sigin", etc
$app->get('/user/:user_action', function ($user_action='') use ($app) {
	$app->bruno->data['user_action'] = $user_action;
	$app->render('/bundles/bruno/www/templates/content/overview.twig');
})
->conditions(array(
	'user_action' => '\S+',
))
->name('www_user');
