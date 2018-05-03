<?php

namespace bundles\bruno\wrapper\routes;

$app = \Slim\Slim::getInstance();

$app->group('/wrapper/translation', function() use ($app) {

	$app->get(
		'/list.js',
		'\bundles\bruno\wrapper\controllers\ControllerTranslation:list_get'
	)
	->name('wrapper_translation_list');

	$app->get(
		'/date.js',
		'\bundles\bruno\wrapper\controllers\ControllerDate:date_get'
	)
	->name('wrapper_translation_date');

	$app->post(
		'/language',
		'\bundles\bruno\wrapper\controllers\ControllerTranslation:language_set'
	)
	->name('wrapper_translation_language');

	$app->post(
		'/auto',
		'\bundles\bruno\api\controllers\ControllerTranslation:auto_post'
	)
	->name('wrapper_translation_auto');

});

$app->post(
	'/wrapper/language',
	'\bundles\bruno\wrapper\controllers\ControllerTranslation:language_set'
)
->name('wrapper_language_post');
