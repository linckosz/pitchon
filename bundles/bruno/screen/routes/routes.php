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

//NOTE: Must be before screen_picture_get to not be ignored
$app->get(
	'/fixcode.jpg',
	'\bundles\bruno\screen\controllers\ControllerScreen:fixcode_get'
)
->name('screen_qrcode_fixcode_get');

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
	'page' => '\d+[ab]?',
))
->name('screen_pitch_get');

//Generate a picture based on fixcode
$app->get(
	'/:pitchid(/:page).:ext',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_picture_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+[ab]?',
	'ext' => 'jpg|png',
))
->name('screen_picture_get');

//Generate a picture based on fixcode
$app->get(
	'/:pitchid(/:page).zip',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_zip_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+?',
))
->name('screen_picture_get');

//Unique session with no JS
$app->get(
	'/fc/:pitchid(/:page)',
	'\bundles\bruno\screen\controllers\ControllerScreen:pitch_fixcode_get'
)
->conditions(array(
	'pitchid' => '[a-z0-9]+',
	'page' => '\d+[ab]?',
))
->name('screen_fixcode_get');

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
	'/statshash/:step/:hashid',
	'\bundles\bruno\screen\controllers\ControllerScreen:statshash_get'
)
->conditions(array(
	'step' => 'question|answer',
	'hashid' => '[a-z0-9]+',
))
->name('screen_statshash_get_pic');

$app->get(
	'/statspic/:step/:hashid.png',
	'\bundles\bruno\screen\controllers\ControllerScreen:statspic_get'
)
->conditions(array(
	'step' => 'question|answer',
	'hashid' => '[a-z0-9]+',
))
->name('screen_statspic_get');

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
