<?php

namespace bundles\bruno\wrapper\controllers;

use \libs\Controller;
use \libs\Json;
use \bundles\bruno\data\models\ModelBruno;

class ControllerInfo extends Controller {

	public function online_post(){
		(new Json('online'))->render();
		return exit(0);
	}

	public function timems_post(){
		$ms = ModelBruno::getMStime();
		(new Json(array('timems' => $ms)))->render();
		return exit(0);
	}

	public function verify_email_post(){
		$app = ModelBruno::getApp();
		$error = true;
		$email = ModelBruno::getData('email');
		if($email){
			$ve = new \hbattat\VerifyEmail($email, 'noreply@'.$app->bruno->domain);
			if($ve->verify()){
				$error = false;
			}
		}
		(new Json('Email verified', $error))->render();
		return exit(0);
	}

}
