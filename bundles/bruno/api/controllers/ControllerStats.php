<?php
// Category 11

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Session;
use \bundles\bruno\data\models\Statistics;
use \bundles\bruno\data\models\Answered;

class ControllerStats extends Controller {

	public function session_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();

		//Check $pitch_id and $pitch_md5
		
		$msg = array();
		$msg['msg'] = 'Sessions';
		$msg['data'] = array();

		$msg['data'][micro_seconds()] = array(
			'participants' => rand(4, 5800)
		);
		$msg['data'][micro_seconds()-258457745] = array(
			'participants' => rand(400, 2700)
		);

		ksort($msg['data']);

		(new Json($msg))->render();
		return exit(0);
	}
	
}
