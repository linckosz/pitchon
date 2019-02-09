<?php

namespace bundles\bruno\app\controllers;

use \libs\Controller;
use \libs\STR;
use \libs\Folders;
use \libs\Json;
use \libs\Vanquish;
use \bundles\bruno\app\models\Terms;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Subscription;
use \bundles\bruno\data\models\Promocode;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\User;
use \bundles\bruno\wrapper\models\Action;
use Screen\Capture;

class ControllerApp extends Controller {

	public function _get(){
		$app = ModelBruno::getApp();
		if($app->bruno->data['user_id']){
			$user_info = Action::getUserInfo();
			foreach ($user_info as $key => $value) {
				$app->bruno->data['user_info_'.$key] = $value;
			}
			//List of laest Subscription available
			$subscription = Subscription::getLatest();
			$app->bruno->data['subscription_id'] = $subscription->id;
			$app->bruno->data['subscription_md5'] = $subscription->md5;
			$app->bruno->data['subscription_starter'] = $subscription->starter;
			$app->bruno->data['subscription_standard'] = $subscription->standard;
			$app->bruno->data['subscription_premium'] = $subscription->premium;
			$app->bruno->data['subscription_duration'] = 2; //Default to 3 months
			$app->bruno->data['subscription_plan'] = 1; //Default to Starter
			if($user = User::getUser()){
				$app->bruno->data['subscription_plan'] = $user->plan;
				$app->bruno->data['subscription_duration'] = $user->plan_duration;
			}
			$app->bruno->data['subscription_promocode'] = false;
			$app->bruno->data['subscription_promocode_discount'] = 0;
			if($promocode = Promocode::getCurrent()){
				$app->bruno->data['subscription_promocode'] = $promocode[0];
				$app->bruno->data['subscription_promocode_discount'] = $promocode[1];
			}
			$app->render('/bundles/bruno/app/templates/app/application.twig');
		} else {
			if(Vanquish::get('remember')){
				//It feels better to keep track of last email login
				Vanquish::unsetAll(array('user_language', 'remember', 'host_id', 'user_email'));
			} else {
				Vanquish::unsetAll(array('user_language', 'remember', 'host_id'));
			}
			$app->bruno->data['terms'] = Terms::getTerm();
			$app->render('/bundles/bruno/app/templates/login.twig');
		}
	}

	public function username_get(){
		$msg = false;
		if($user = User::getUser()){
			if($user->username){
				$msg = $user->username;
			}
		}
		$msg = array('msg' => $msg);
		(new Json($msg))->render();
		return exit(0);
	}

	//wrapper_sendAction('', 'post', 'refresh');
	public function refresh_post(){
		$msg = 'error';
		if(User::isAdmin()){
			if(User::refreshAll()){
				$msg = 'OK';
			}
		}
		$msg = array('msg' => $msg);
		(new Json($msg))->render();
		return exit(0);
	}

	protected function generate_zip_picture($pitch_enc, $page){
		$app = ModelBruno::getApp();
		if($pitch_id = STR::integer_map($pitch_enc, true)){
			$ext = 'jpg';
			$url = $_SERVER['REQUEST_SCHEME'].'://screen.'.$app->bruno->domain.'/fc/'.$pitch_enc.'/'.$page.'?fixpicture=0';
			$width = 1280;
			$height = 720;
			$screenCapture = new Capture();
			$screenCapture->setUrl($url);
			$screenCapture->setWidth($width);
			$screenCapture->setHeight($height);
			$screenCapture->setClipWidth($width);
			$screenCapture->setClipHeight($height);
			$screenCapture->setImageType($ext);
			$screenCapture->setOptions([
			    'ignore-ssl-errors' => 'yes',
			]);
			$folder = new Folders;
			$folder->createPath($app->bruno->filePath.'/microweber/jobs/pitch/'.$pitch_id.'/');
			$folder->createPath($app->bruno->filePath.'/microweber/output/pitch/'.$pitch_id.'/');
			$screenCapture->jobs->setLocation($app->bruno->filePath.'/microweber/jobs/pitch/'.$pitch_id.'/');
			$screenCapture->output->setLocation($app->bruno->filePath.'/microweber/output/pitch/'.$pitch_id.'/');
			$screenCapture->binPath = '/usr/local/bin/';
			$screenCapture->save('tp1_'.$page.'.'.$ext); //JPEG compress is 75% (good ratio)
			//$screenCapture->jobs->clean();
			$path = $app->bruno->filePath.'/microweber/output/pitch/'.$pitch_id.'/'.$page.'.'.$ext;
			@unlink($path);
			rename($app->bruno->filePath.'/microweber/output/pitch/'.$pitch_id.'/tp1_'.$page.'.'.$ext, $path);
			return $path;
		}
		return $app->bruno->path.'/bundles/bruno/wrapper/public/images/generic/unavailable.png';
	}

	public function sample_pitch_get($pitch_enc){
		if(function_exists('proc_nice')){proc_nice(10);} //Higer the number is, lower the script priority is
		set_time_limit(300); //It may take few minutes (max 5min)
		$app = ModelBruno::getApp();
		include_once($app->bruno->path.'/libs/TinyButStrong.php'); //Note: Composer is using a too old version of opentbs, and cannot use autoloader because of namespace issue, must be manual
		$pitch_id = STR::integer_map($pitch_enc, true);
		ob_clean();
		flush();
		
		if($pitch = Pitch::find($pitch_id)){

			//Clean sample directory
			$folder = new Folders;
			$folder->createPath($app->bruno->filePath.'/sample/');
			$files = $folder->loopFolder(true);
			foreach ($files as $file) {
				if(filemtime($file) < time()-(24*3600)){
					@unlink($file);
				}
			}

			$ppt = $app->bruno->path.'/bundles/bruno/app/models/sample/lbqz.pptm';
			$ppt_temp = $app->bruno->filePath.'/sample/lbqz_'.$pitch_enc.'_'.time().'.pptm';

			copy($ppt, $ppt_temp);
			usleep(50000);

			$zip = new \ZipArchive();
			//Make sure the copy function if completed
			$open = 0;
			$i = 0;
			while($open!=1 && $i<1000){ //Wait 10s max
				$open = $zip->open($ppt_temp);
				usleep(10000);
				$i++;
			}

			$questions = $pitch->questions(array('id', 'style'));

			$tbs = new \clsTinyButStrong;
			$tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$tbs->LoadTemplate($ppt_temp);
			$page_max = $tbs->Plugin(OPENTBS_COUNT_SLIDES);
			$tbs->LoadTemplate(false);

			//NOTE => The name may change once the new PPT is created (or if PPT sample is update)
			//image1.jpg => White background
			//image2.png => Transparent Score
			//image3.png => Hand

			$page = 1;
			$nbr = 1;

			//Introduction page
			$path_screen = $this->generate_zip_picture($pitch_enc, '0');
			$zip->addFile($path_screen, 'ppt/media/0.jpg');
			$zip->addFile($app->bruno->path.'/bundles/bruno/wrapper/public/images/generic/neutral.png', 'ppt/media/neutral.png');
			$rels = 'ppt/slides/_rels/slide'.$page.'.xml.rels';
			$rels_xml = $zip->getFromName($rels);
			if(!empty($rels_xml)){
				$rels_xml = preg_replace("/image1.jpg/i", '0.jpg', $rels_xml);
				$rels_xml = preg_replace("/image3.png/i", 'neutral.png', $rels_xml); //Do not display the hand
				$zip->addFromString($rels, $rels_xml);
			}
			//Faster animation to enable click next slide
			$rels = 'ppt/slides/slide'.$page.'.xml';
			$rels_xml = $zip->getFromName($rels);
			if(!empty($rels_xml)){
				$rels_xml = preg_replace("/dur=\"\d+\"/i", 'dur="1"', $rels_xml);
				$rels_xml = preg_replace("/delay=\"\d+\"/i", 'delay="0"', $rels_xml);
				$rels_xml = preg_replace("/<p:childTnLst>.*<\/p:childTnLst>/i", '', $rels_xml);
				$zip->addFromString($rels, $rels_xml);
			}
			$page++;

			$nbr = 1;
			foreach ($questions as $question) {
				$arr = array('a', 'b');
				foreach ($arr as $suffix) {
					$path_screen = $this->generate_zip_picture($pitch_enc, $nbr.$suffix);
					$zip->addFile($path_screen, 'ppt/media/'.$nbr.$suffix.'.jpg');
					$rels = 'ppt/slides/_rels/slide'.$page.'.xml.rels';
					$rels_xml = $zip->getFromName($rels);
					if(!empty($rels_xml)){
						$rels_xml = preg_replace("/image1.jpg/i", $nbr.$suffix.'.jpg', $rels_xml);
						if($suffix!='a'){
							$rels_xml = preg_replace("/image3.png/i", 'neutral.png', $rels_xml); //Do not display the hand
						}
						$zip->addFromString($rels, $rels_xml);
					}
					$rels = 'ppt/slides/slide'.$page.'.xml';
					$rels_xml = $zip->getFromName($rels);
					if(!empty($rels_xml)){
						$hashid = Question::encrypt($question->id);
						if($suffix=='a'){
							$rels_xml = preg_replace("/lbqz@Type/i", 'question', $rels_xml);
							$rels_xml = preg_replace("/lbqz@Score/i", $_SERVER['REQUEST_SCHEME'].'://screen.'.$app->bruno->domain.'/statspic/question/'.$hashid.'.png', $rels_xml);
						} else if($suffix=='b'){
							$rels_xml = preg_replace("/lbqz@Type/i", 'answer', $rels_xml);
							$rels_xml = preg_replace("/lbqz@Score/i", $_SERVER['REQUEST_SCHEME'].'://screen.'.$app->bruno->domain.'/statspic/answer/'.$hashid.'.png', $rels_xml);
						}
						$rels_xml = preg_replace("/lbqz@Question/i", $_SERVER['REQUEST_SCHEME'].'://screen.'.$app->bruno->domain.'/'.$pitch_enc.'/'.$nbr.$suffix.'.jpg', $rels_xml);
						if($suffix!='a'){
							//Faster animation to enable click next slide
							$rels_xml = preg_replace("/dur=\"\d+\"/i", 'dur="1"', $rels_xml);
							$rels_xml = preg_replace("/delay=\"\d+\"/i", 'delay="0"', $rels_xml);
							$rels_xml = preg_replace("/<p:childTnLst>.*<\/p:childTnLst>/i", '', $rels_xml);
						}
						$zip->addFromString($rels, $rels_xml);
					}
					$page++;
					if($page >= $page_max){
						break;
					}
				}
				$nbr++;
			}

			//[$page_max] Thank you page
			$suffix = 'a';
			$path_screen = $this->generate_zip_picture($pitch_enc, $nbr.$suffix);
			$zip->addFile($path_screen, 'ppt/media/'.$nbr.$suffix.'.jpg');
			$rels = 'ppt/slides/_rels/slide'.$page_max.'.xml.rels';
			$rels_xml = $zip->getFromName($rels);
			if(!empty($rels_xml)){
				$rels_xml = preg_replace("/image1.jpg/i", $nbr.$suffix.'.jpg', $rels_xml);
				$rels_xml = preg_replace("/image3.png/i", 'neutral.png', $rels_xml); //Do not display the hand
				$zip->addFromString($rels, $rels_xml);
			}
			//Faster animation to enable click next slide
			$rels = 'ppt/slides/slide'.$page_max.'.xml';
			$rels_xml = $zip->getFromName($rels);
			if(!empty($rels_xml)){
				$rels_xml = preg_replace("/dur=\"\d+\"/i", 'dur="1"', $rels_xml);
				$rels_xml = preg_replace("/delay=\"\d+\"/i", 'delay="0"', $rels_xml);
				$rels_xml = preg_replace("/<p:childTnLst>.*<\/p:childTnLst>/i", '', $rels_xml);
				$zip->addFromString($rels, $rels_xml);
			}
			
			$zip->close();

			$tbs = new \clsTinyButStrong;
			$tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$tbs->LoadTemplate($ppt_temp);

			//We keep the last page which is the Thank you age
			while($page <= $page_max-1 ){
				$tbs->PlugIn(OPENTBS_DELETE_SHEETS, $page);
				$page++;
			}

			if(function_exists('proc_nice')){proc_nice(0);} //Reset the script priority to normal

			//@unlink($ppt_temp);
			header('Content-Description: File Transfer');
			header('Content-Transfer-Encoding: binary');
			header('Content-Type: application/force-download;');
			header('Content-Disposition: attachment; filename="lbqz_'.$pitch_enc.'.pptm"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			$tbs->Show(true, 'lbqz_'.$pitch_enc.'.pptm');

			return true;
		}
		return false;
	}

}
