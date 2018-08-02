<?php

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\wrapper\models\Action;

class ControllerInfo extends Controller {

	public function action_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		if(isset($data->action)){
			$action = $data->action;
			if(is_numeric($action)){
				//Always use negative for outside value, Positives value are used to follow history
				if($action>0){
					$action = -$action;
				}
				$info = null;
				if(isset($data->info)){
					$info = $data->info;
				}
				Action::record($action, $info);
			}
		}
		$msg = array('msg' => 'ok');
		(new Json($msg))->render();
		return exit(0);
	}

	public function wait_get($seconds = 5){
		$app = ModelBruno::getApp();
		if($seconds<0 || $seconds > 60){
			$seconds = 10;
		}
		sleep($seconds);
		$app->response->headers->set('Content-Type', 'text/xml');
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
  		echo '<?xml version="1.0" encoding="UTF-8"?><ppt>ok</ppt>';
  		return true;
	}

}
