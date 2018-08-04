<?php

namespace bundles\bruno\screen\routes;

use \libs\Vanquish;

$app = \Slim\Slim::getInstance();

//This does redirect the QRcode picture with the current open question
//NOTE: Must be before screen_picture_get to not be ignored
$app->get(
	'/session(/:questionid).jpg',
	'\bundles\bruno\screen\controllers\ControllerScreen:session_get'
)
->conditions(array(
	'questionid' => '[a-z0-9]+',
))
->name('screen_qrcode_session_get');

$app->get(
	'/wait/:seconds.xml',
	'\bundles\bruno\screen\controllers\ControllerScreen:wait_get'
)
->conditions(array(
	'seconds' => '\d+',
))
->name('screen_wait_get');

//Dynamic session with JS action
$app->get(
	'/:pitchid(/:page)',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+',
))
->name('screen_pitch_get');

//Genrate a picture base on webviewer
$app->get(
	'/:pitchid(/:page).:ext',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_picture_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+',
	'ext' => 'jpg|png',
))
->name('screen_picture_get');

//Unique session with no JS
$app->get(
	'/wb/:pitchid(/:page)',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_webviewer_get'
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
