<?php

namespace bundles\bruno\www\controllers;

use \libs\Controller;
use \libs\Folders;

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
		if($content == 'overview'){
			//Random picture
			$folder = new Folders($app->bruno->path.'/public/bruno/www/images/content/overview/question');
			$files = $folder->loopFolder(true);
			$app->bruno->data['question'] = pathinfo($files[array_rand($files)])['filename'];
			//https://quiz.lebonquiz.fr/p/9mq?preview=1&zoom=0.94
			$app->bruno->data['mobile_quiz'] = $base_url = 'https://quiz.lebonquiz.fr/p/'.$app->bruno->data['question'].'?preview=1&zoom=0.94'; //toto => Must adapt the domain name if we use another customed website like pitchenhancer.com
		}
		$app->render($twig);
	}

}
