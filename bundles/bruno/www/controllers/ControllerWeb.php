<?php

namespace bundles\bruno\www\controllers;

use \libs\Controller;

class ControllerWeb extends Controller {

	public function _get($content){
		$app = \Slim\Slim::getInstance();
		if(!$content){
			//We redirect to the Application if the user is logged
			if($app->bruno->data['user_id']){
				$app->response->redirect($_SERVER['REQUEST_SCHEME'].'://app.'.$app->bruno->domain, 303);
			}
			$content = 'overview';
		}
		$twig = '/bundles/bruno/www/templates/content/'.$content.'.twig';
		if(!is_file($app->bruno->path.$twig)){
			$content = 'anotherworld';
			$twig = '/bundles/bruno/www/templates/content/'.$content.'.twig';
		}

		$app->bruno->data['content'] = $content;
		$app->render($twig);
	}

}
