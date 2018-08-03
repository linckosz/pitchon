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

}
