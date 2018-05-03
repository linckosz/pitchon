<?php

namespace bundles\bruno\wrapper\routes;

$app = \Slim\Slim::getInstance();

$app->get(
	'/wrapper/captcha(/:total_num(/:width(/:height)))',
	'\bundles\bruno\wrapper\controllers\ControllerCaptcha:get_captcha'
)
->conditions(array(
	'total_num' => '\d+',
	'width' => '\d+',
	'height' => '\d+',
))
->name('wrapper_captcha');

$app->group('/wrapper/info', function() use ($app) {
	
	$app->get('/nonetwork', function() use ($app) {
		$app->render('/bundles/bruno/wrapper/templates/nonetwork.twig');
	})
	->name('wrapper_info_nonetwork_get');

	$app->post(
		'/online',
		'\bundles\bruno\wrapper\controllers\ControllerInfo:online_post'
	)
	->name('wrapper_info_online_post');

	$app->post(
		'/timems',
		'\bundles\bruno\wrapper\controllers\ControllerInfo:timems_post'
	)
	->name('wrapper_info_timems_post');

	$app->post(
		'/verifyemail',
		'\bundles\bruno\wrapper\controllers\ControllerInfo:verify_email_post'
	)
	->name('wrapper_info_verify_email_post');

});
