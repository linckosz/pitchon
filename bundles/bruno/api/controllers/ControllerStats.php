<?php
// Category 11

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Session;

class ControllerStats extends Controller {

	public function session_post(){
		$app = ModelBruno::getApp();


		$msg = 'ok';
		(new Json($msg))->render();
		return exit(0);
	}

}
