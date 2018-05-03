<?php

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\wrapper\models\Action;

class ControllerInfo extends Controller {

	protected $app = NULL;
	protected $data = NULL;

	public function __construct(){
		$app = $this->app = \Slim\Slim::getInstance();
		$this->data = json_decode($app->request->getBody());
		return true;
	}

	public function action_post(){
		$app = ModelBruno::getApp();
		if(isset($this->data->action)){
			$action = $this->data->action;
			if(is_numeric($action)){
				//Always use negative for outside value, Positives value are used to follow history
				if($action>0){
					$action = -$action;
				}
				$info = null;
				if(isset($this->data->info)){
					$info = $this->data->info;
				}
				Action::record($action, $info);
			}
		}
		$msg = array('msg' => 'ok');
		(new Json($msg))->render();
		return exit(0);
	}

}
