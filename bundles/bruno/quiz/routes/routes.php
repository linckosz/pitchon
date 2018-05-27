<?php

namespace bundles\bruno\quiz\routes;

use \libs\Vanquish;

$app = \Slim\Slim::getInstance();

$app->get(
	'/',
	'\bundles\bruno\quiz\controllers\ControllerQuiz:scan_get'
)
->conditions(array(
	'sessionid' => '[a-z0-9]+',
))
->name('quiz_session_get');

$app->get(
	'/:sessionid',
	'\bundles\bruno\quiz\controllers\ControllerQuiz:session_get'
)
->conditions(array(
	'sessionid' => '[a-z0-9]+',
))
->name('quiz_session_get');

$app->get(
	'/p/:questionid',
	'\bundles\bruno\quiz\controllers\ControllerQuiz:question_get'
)
->conditions(array(
	'questionid' => '[a-z0-9]+',
))
->name('quiz_preview_get');

$app->get(
	'/a/:statisticsid/:answerid',
	'\bundles\bruno\quiz\controllers\ControllerQuiz:answer_get'
)
->conditions(array(
	'statisticsid' => '[a-z0-9]+',
	'answerid' => '[a-z0-9]+',
))
->name('quiz_answer_get');

$app->get(
	'/s/:statisticsid(/:questionid)',
	'\bundles\bruno\quiz\controllers\ControllerQuiz:survey_get'
)
->conditions(array(
	'statisticsid' => '[a-z0-9]+',
))
->name('quiz_survey_get');

$app->get(
	'/w/:statisticsid',
	'\bundles\bruno\quiz\controllers\ControllerQuiz:wait_get'
)
->conditions(array(
	'statisticsid' => '[a-z0-9]+',
))
->name('quiz_wait_get');
