<?php

namespace bundles\bruno\screen\routes;

use \libs\Vanquish;

$app = \Slim\Slim::getInstance();

$app->get(
	'/wait/:seconds.xml',
	'\bundles\bruno\api\controllers\ControllerInfo:wait_get'
)
->conditions(array(
	'seconds' => '\d+',
))
->name('screen_wait_get');

$app->get(
	'/:pitchid(/:page)',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+',
))
->name('screen_pitch_get');

$app->get(
	'/wb/:pitchid(/:page)',
	'\bundles\bruno\screen\controllers\ControllerScreen:webviewer_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+',
))
->name('screen_webviewer_get');

$app->get(
	'/stats/:questionid(/:step)',
	'\bundles\bruno\screen\controllers\ControllerScreen:stats_get'
)
->conditions(array(
	'questionid' => '[a-z0-9]+',
	'step' => 'question|answer',
))
->name('screen_stats_get');

$app->get(
	'/statsjson/:questionid',
	'\bundles\bruno\screen\controllers\ControllerScreen:statsjson_get'
)
->conditions(array(
	'questionid' => '[a-z0-9]+',
))
->name('screen_statsjson_get');

$app->get(
	'/statsjs/:questionid.js',
	'\bundles\bruno\screen\controllers\ControllerScreen:statsjs_get'
)
->conditions(array(
	'questionid' => '[a-z0-9]+',
))
->name('screen_statsjs_get');

//This does redirect the QRcode picture with the current open question
$app->get(
	'/session(/:questionid).jpg',
	'\bundles\bruno\screen\controllers\ControllerScreen:session_get'
)
->conditions(array(
	'questionid' => '[a-z0-9]+',
))
->name('screen_qrcode_session_get');
