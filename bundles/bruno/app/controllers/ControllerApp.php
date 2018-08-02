<?php

namespace bundles\bruno\app\controllers;

use \libs\Controller;
use \libs\STR;
use \libs\Folders;
use \libs\Json;
use \libs\Vanquish;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Subscription;
use \bundles\bruno\data\models\Promocode;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\User;
use \bundles\bruno\wrapper\models\Action;

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
			$app->bruno->data['subscription_pricing'] = 1; //Default to Monthly
			$app->bruno->data['subscription_plan'] = 1; //Default to Starter
			if($user = User::getUser()){
				$app->bruno->data['subscription_pricing'] = $user->pricing;
				$app->bruno->data['subscription_plan'] = $user->plan;
			}
			$promocode = Promocode::getCurrent();
			$app->bruno->data['subscription_promocode'] = $promocode[0];
			$app->bruno->data['subscription_promocode_discount'] = $promocode[1];
			$app->render('/bundles/bruno/app/templates/app/application.twig');
		} else {
			if(Vanquish::get('remember')){
				//It feels better to keep track of last email login
				Vanquish::unsetAll(array('user_language', 'remember', 'host_id', 'user_email'));
			} else {
				Vanquish::unsetAll(array('user_language', 'remember', 'host_id'));
			}
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

	public function sample_pitch_get($pitchid_enc){
		$app = ModelBruno::getApp();
		include_once($app->bruno->path.'/libs/TinyButStrong.php'); //Note: Composer is using a too old version of opentbs, and cannot use autoloader because of namespace issue, must be manual
		$pitch_id = STR::integer_map($pitchid_enc, true);
		ob_clean();
		flush();
		
		if($pitch = Pitch::find($pitch_id)){

			$bruno_info = $app->trans->getBRUT('app', 6, 1); //1) Please use PowerPoint 2013 or later, with its Add-in Web Viewer installed. And click on "Enable Editing" if you see the notification. 2) Or simply use any browser.

			$folder = new Folders;
			$folder->createPath($app->bruno->filePath.'/sample/');
			$files = $folder->loopFolder(true);
			foreach ($files as $file) {
				if(filemtime($file) < time()-(24*3600)){
					@unlink($file);
				}
			}
			$files = $folder->loopFolder(true);

			$ppt = $app->bruno->path.'/bundles/bruno/app/models/sample/pitch.pptx';
			$ppt_temp = $app->bruno->filePath.'/sample/pitch_'.$pitchid_enc.'_'.time().'.pptx';

			copy($ppt, $ppt_temp);
			usleep(50000);

			$zip = new \ZipArchive();
			//Make sure the copy function if completed
			$open = 0;
			$i = 0;
			while($open!=1 && $i<1000){
				$open = $zip->open($ppt_temp);
				usleep(10000);
				$i++;
			}

			$questions = $pitch->questions(array('id', 'style', 'title'));

			$bruno_pitch_title = $pitch->title; //title

			$bruno_by = '';
			if($user = User::Where('id', $pitch->c_by)->first(array('id', 'username'))){
				$bruno_by = $app->trans->getBRUT('app', 6, 8).$user->username; //By Bruno Martin 马丁
			}

			$content = 'ppt/slides/slide1.xml';
			$xml = $zip->getFromName($content);
			if(!empty($xml)){
				$xml = preg_replace("/bruno_pitch_title/i", $bruno_pitch_title, $xml);
				$xml = preg_replace("/bruno_by/i", $bruno_by, $xml);
				$zip->addFromString($content, $xml);
			}

			$bruno_url = false;
			foreach ($questions as $question) {
				$questionid_enc = STR::integer_map($question->id);
				$bruno_url = 'screen.'.$app->bruno->domain.'/'.$pitchid_enc.'/0';
				break;
			}

			$bruno_pitch = $app->trans->getBRUT('app', 6, 2).$pitch->title; //Title: title
			$content = 'ppt/notesSlides/notesSlide1.xml';
			$xml = $zip->getFromName($content);
			if(!empty($xml)){
				if($bruno_url){
					$xml = preg_replace("/bruno_url/i", $bruno_url, $xml);
				} else {
					$xml = preg_replace("/https://bruno_url/i", '', $xml);
				}
				$xml = preg_replace("/bruno_pitch/i", $bruno_pitch, $xml);
				$xml = preg_replace("/bruno_info/i", $bruno_info, $xml);
				$zip->addFromString($content, $xml);
			}

			$tbs = new \clsTinyButStrong;
			$tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$tbs->LoadTemplate($ppt);
			$page_max = $tbs->Plugin(OPENTBS_COUNT_SLIDES)-1;
			$tbs->LoadTemplate(false);

			$page = 2;
			
			$nbr = 1;
			foreach ($questions as $question) {
				$questionid_enc = STR::integer_map($question->id);
				$bruno_question = $app->trans->getBRUT('app', 6, 3); //Question: 
				if($question->style==2){
					$bruno_question .= ' ['.$nbr.' - '.$app->trans->getBRUT('app', 6, 5).'] '; //pictures
				} else if($question->style==3){
					$bruno_question .= ' ['.$nbr.' - '.$app->trans->getBRUT('app', 6, 6).'] '; //statistics
				} else if($question->style==4){
					$bruno_question .= ' ['.$nbr.' - '.$app->trans->getBRUT('app', 6, 7).'] '; //survey
				} else {
					$bruno_question .= ' ['.$nbr.' - '.$app->trans->getBRUT('app', 6, 4).'] '; //answers
				}
				$bruno_question .= $question->title; //Pitch: [2 - pictures] title

				$slides = 2;
				if($question->style == 3){
					//Statistics only have 1 slide
					$slides = 1;
				}
				$limit = $page_max - $slides;
				$type = 'question';
				while ($slides>0 && $page<=$limit) {
					$bruno_url = 'screen.'.$app->bruno->domain.'/'.$pitchid_enc.'/'.$nbr;
					$slide = 'ppt/slides/slide'.$page.'.xml';
					$rels = 'ppt/slides/_rels/slide'.$page.'.xml.rels';
					$slide_xml = $zip->getFromName($slide);
					$rels_xml = $zip->getFromName($rels);
					if(!empty($slide_xml) && !empty($rels_xml)){
						if(preg_match("/(webextension\d+.xml)/ui", $rels_xml, $match)){
							if($match && isset($match[1])){
								$content = 'ppt/webextensions/'.$match[1];
								$xml = $zip->getFromName($content);
								if(!empty($xml)){
									$xml = preg_replace("/bruno_start_url.*?bruno_end_url/i", $bruno_url.'/webviewer', $xml);
									$zip->addFromString($content, $xml);
								}
							}
						}
						if(preg_match("/(notesSlide\d+.xml)/ui", $rels_xml, $match)){
							if($match && isset($match[1])){
								$content = 'ppt/notesSlides/'.$match[1];
								$xml = $zip->getFromName($content);
								if(!empty($xml)){
									$xml = preg_replace("/bruno_question/i", $bruno_question, $xml);
									$xml = preg_replace("/bruno_url/i", $bruno_url, $xml);
									$xml = preg_replace("/bruno_info/i", $bruno_info, $xml);
									$zip->addFromString($content, $xml);
								}
							}
						}
					}
					$type = 'answer';
					$slides--;
					$page++;
				}
				$nbr++;
				if($page > $page_max){
					break;
				}
			}

			$zip->close();

			$tbs = new \clsTinyButStrong;
			$tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$tbs->LoadTemplate($ppt_temp);

			while($page <= $page_max ){
				$tbs->PlugIn(OPENTBS_DELETE_SHEETS, $page);
				$page++;
			}
			
			header('Content-Description: File Transfer');
			header('Content-Type: attachment/force-download;');
			header('Content-Transfer-Encoding: binary');
			header('Content-Type: application/force-download;');
			header('Content-Disposition: attachment; filename="Pitch_'.$pitchid_enc.'_[PowerPoint 2013+].pptx"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			$tbs->Show(true, 'Pitch_'.$pitchid_enc.'_[PowerPoint 2013+].pptx');
			@unlink($ppt_temp);

			return true;
		}
		return false;
	}

}
