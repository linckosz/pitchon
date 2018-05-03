<?php

namespace bundles\bruno\wrapper\controllers;

use \bundles\bruno\wrapper\models\TranslationListJS;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\User;
use \libs\Vanquish;
use \libs\Controller;
use \libs\Json;
use \libs\STR;

class ControllerTranslation extends Controller {

	public function list_get(){
		$app = ModelBruno::getApp();
		$app->response->headers->set('Content-Type', 'application/javascript');
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
		$this->setList();
	}

	public function language_set(){
		$data = ModelBruno::getData();
		if(isset($data->translation_language) && is_string($data->translation_language)){
			$data = strtolower($data->translation_language);
			if(preg_match("/[\w-]{2,}/ui", $data)){
				Vanquish::set(array('user_language' => $data));
				if($user = User::getUser()){
					$user->setLanguage();
				}
			}
		}
		(new Json())->render();
		return exit(0);
	}

	public function setList(){
		echo TranslationListJS::setList();
	}

	public function auto_post(){
		$app = ModelBruno::getApp();
		$data = json_decode($app->request->getBody());
		if(isset($data->data) && !is_object($data->data)){
			$data->data = (object) $data->data;
		}
		if(!isset($data->data)){
			$msg = $app->trans->getBRUT('api', 0, 4); //No data form received.
			(new Json(array('msg' => $msg), true, 400, true))->render();
			return exit(0);
		}
		$form = $data->data;
		if(isset($form->text)){
			$translator = new \libs\OnlineTranslator();
			$msg = $translator->translate($form->text);
			(new Json(array('msg' => $msg)))->render();
			return exit(0);
		} else {
			$msg = $app->trans->getBRUT('api', 2, 6); //No text found to be translated
			(new Json(array('msg' => $msg), true, 400, true))->render();
			return exit(0);
		}
	}

}
