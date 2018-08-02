<?php

namespace bundles\bruno\api\controllers;

use \libs\Controller;
use \libs\Email;
use \libs\Folders;
use \libs\Json;
use \libs\STR;
use \libs\Network;
use \libs\Datassl;
use \libs\Translation;
use \bundles\bruno\data\models\Data;
use \bundles\bruno\data\models\Inform;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\wrapper\models\Action;
use \bundles\bruno\data\models\data\Answer;
use \bundles\bruno\data\models\data\File;
use \bundles\bruno\data\models\data\Login;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\User;
use \bundles\bruno\data\models\data\Guest;
use GeoIp2\Database\Reader;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Carbon\Carbon;
use WideImage\WideImage;

use Screen\Capture;

class ControllerTest extends Controller {

	public function test(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		if($app->config('mode') != 'development'){
			$msg = array('msg' => 'Unauthorized access');
			(new Json($msg))->render();
			return exit(0);
		}
		$msg = 'The application is reading';
		$db = Capsule::connection('data');
		$db->enableQueryLog();
		$app->bruno->time_record = true; //Display timing
		$tp = null;

		$url = 'https://screen.pitchon.net/6qfsb/1';
		$url = 'https://screen.pitchon.net/acpga/12';

		$screenCapture = new Capture();
		$screenCapture->setUrl($url);
		/*
		$screenCapture->setWidth(1280);
		$screenCapture->setHeight(720);
		$screenCapture->setClipWidth(1280);
		$screenCapture->setClipHeight(720);
		$screenCapture->setImageType('png');
		*/
		$screenCapture->setWidth(1920);
		$screenCapture->setHeight(1080);
		$screenCapture->setClipWidth(1920);
		$screenCapture->setClipHeight(1080);
		$screenCapture->setImageType('jpg');
		$screenCapture->setOptions([
		    'ignore-ssl-errors' => 'yes',
		]);
		$folder = new Folders;
		$folder->createPath($app->bruno->filePath.'/microweber/jobs/');
		$folder->createPath($app->bruno->filePath.'/microweber/output/');
		$screenCapture->jobs->setLocation($app->bruno->filePath.'/microweber/jobs/');
		$screenCapture->output->setLocation($app->bruno->filePath.'/microweber/output/');
		$screenCapture->binPath = '/usr/local/bin/';
		$screenCapture->save('test');

		//wrapper_sendAction('', 'post', 'api/test');
		//\libs\Watch::php( $db->getQueryLog() , 'QueryLog', __FILE__, __LINE__, false, false, true);
		\libs\Watch::php( $tp, time(), __FILE__, __LINE__, false, false, true);

		
		$msg = array('msg' => $msg);
		(new Json($msg))->render();
		return exit(0);
	}

}
