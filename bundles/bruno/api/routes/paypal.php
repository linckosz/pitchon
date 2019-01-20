<?php

namespace bundles\bruno\api\routes;

$app = \Slim\Slim::getInstance();

$app->group('/api/paypal', function() use ($app) {

	$app->post(
		'/billing',
		'\bundles\bruno\api\controllers\ControllerPaypal:billing_post'
	)
	->name('api_paypal_billing_post');

	$app->post(
		'/pay',
		'\bundles\bruno\api\controllers\ControllerPaypal:pay_post'
	)
	->name('api_paypal_pay_post');

	$app->post(
		'/fail',
		'\bundles\bruno\api\controllers\ControllerPaypal:fail_post'
	)
	->name('api_paypal_fail_post');

	$app->post(
		'/listener',
		'\bundles\bruno\api\controllers\ControllerPaypal:listener_post'
	)
	->name('api_paypal_listener_post');

});
