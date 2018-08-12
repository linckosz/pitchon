<?php

namespace bundles\bruno\screen\controllers;

use \libs\STR;
use \libs\Json;
use \libs\Folders;
use \libs\Controller;
use \libs\Vanquish;
use \bundles\bruno\wrapper\models\Action;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Session;
use \bundles\bruno\data\models\Statistics;
use \bundles\bruno\data\models\data\Answer;
use \bundles\bruno\data\models\data\File;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\Guest;
use \bundles\bruno\data\models\data\User;
use WideImage\WideImage;
use Endroid\QrCode\QrCode;
use Screen\Capture;

class ControllerScreen extends Controller {

	protected static $fixcode = false;

	protected static $fixpicture = false;

	protected static $show_hand = true;

	protected static $session = false;

	protected function get_session(){
		if(self::$session){
			return self::$session;
		}
		$app = ModelBruno::getApp();
		$md5 = Session::get_session_md5();
		$session = false;
		if(isset($_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_id'])){
			$session = Session::Where('id', $_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_id'])->where('md5', $md5)->first();
		}
		if(!$session){
			$session = Session::Where('md5', $md5)->orderBy('c_at', 'desc')->first();
		}
		if(!$session){
			$session = new Session;
			$session->md5 = $md5;
			$session->code = Session::get_session_code();
			if($session->save()){
				$info = new \stdClass;
				$info->env = Action::getUserInfo();
				$info->session_id = $session->id;
				Action::record(1, $info);
			}
		}
		$timelimit = time()+(8*3600); //8H maximum without consulting it
		setcookie($app->bruno->data['bruno_dev'].'_screen_session_id', $session->id, $timelimit, '/');
		$_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_id'] = $session->id;
		self::$session = $session;
		return self::$session;
	}

	protected function set_session($question_id, $status=0){
		if($session = $this->get_session()){
			$session->question_id = $question_id;
			$session->status = $status;
			$session->save();
		}
		return $session;
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
		echo '<?xml version="1.0" encoding="UTF-8"?><wait>'.$seconds.'</wait>';
		return true;
	}

	public function pitch_picture_get($pitch_enc, $page=true, $ext=false){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();

		$path = $app->bruno->path.'/bundles/bruno/wrapper/public/images/generic/unavailable.png';
		if($pitch_id = STR::integer_map($pitch_enc, true)){

			$fixpicture = 1; //Default we fix the picture
			if(isset($data->fixpicture) && !$data->fixpicture){
				$fixpicture = 0;
			}
			//Use fixcode to not display JS button and unique session
			$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/fc/'.$pitch_enc.'/'.$page.'?fixpicture='.$fixpicture;

			$width = 1280;
			$height = 720;
			//We don't adapt the resultion, we only fit the ratio
			if(isset($data->width) && is_numeric($data->width) && $data->width>0 && isset($data->height) && is_numeric($data->height) && $data->height>0){
				$width = round($height*$data->width/$data->height);
			}
			
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
			$folder->createPath($app->bruno->filePath.'/microweber/jobs/'.$pitch_id.'/');
			$folder->createPath($app->bruno->filePath.'/microweber/output/'.$pitch_id.'/');
			$screenCapture->jobs->setLocation($app->bruno->filePath.'/microweber/jobs/'.$pitch_id.'/');
			$screenCapture->output->setLocation($app->bruno->filePath.'/microweber/output/'.$pitch_id.'/');
			$screenCapture->binPath = '/usr/local/bin/';
			$screenCapture->save('tp0_'.$page.'.'.$ext); //JPEG compress is 75% (good ratio)
			//$screenCapture->jobs->clean();
			$path = $app->bruno->filePath.'/microweber/output/'.$pitch_id.'/'.$page.'.'.$ext;
			@unlink($path);
			rename($app->bruno->filePath.'/microweber/output/'.$pitch_id.'/tp0_'.$page.'.'.$ext, $path);

			$image = WideImage::load($path);
		} else {
			$image = WideImage::load($path);
		}

		$image->output($ext);
		session_write_close();
		return exit(0);
	}

	protected function set_page_array($page){
		$result = array(
			'nbr' => 0,
			'style' => 'question',
			'prev' => false,
			'next' => '1a',
		);
		if($page===true){
			if(!isset($_SESSION['screen_page_next'])){
				$page = 0; //Start from introduction page
			} else {
				$page = $_SESSION['screen_page_next'];
			}
		}

		if(preg_match("/(\d+)([ab])/ui", $page, $matches)){
			$result['nbr'] = intval($matches[1]);
			if($matches[2] == 'b'){
				$result['style'] = 'answer';
			}
			//Next
			if($matches[2] == 'b'){
				$result['next'] = ($result['nbr']+1) . 'a';
			} else {
				$result['next'] = $result['nbr'] . 'b';
			}
			//Previous
			if($matches[2] == 'b'){
				$result['prev'] = $result['nbr'] . 'a';
			} else if($result['nbr']==1){
				$result['prev'] = 0;
			} else {
				$result['prev'] = ($result['nbr']-1) . 'b';
			}
		}
		$_SESSION['screen_page_next'] = $result['next'];
		return $result;
	}

	public function pitch_fixcode_get($pitch_enc, $page=true){
		$data = ModelBruno::getData();
		//We default at fixing the picture (not PPT)
		self::$fixpicture = true;
		self::$show_hand = true;
		if(isset($data->fixpicture) && !$data->fixpicture){
			self::$fixpicture = false; //PPT with hand animation
			self::$show_hand = false;
		}

		self::$fixcode = true;
		return $this->pitch_get($pitch_enc, $page);
	}

	public function pitch_get($pitch_enc, $page=true, $ext=false){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();

		$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];

		$app->bruno->data['show_hand'] = false;

		$app->bruno->data['fixpicture'] = self::$fixpicture;

		$list = $this->set_page_array($page);

		$app->bruno->data['get_style'] = $list['style'];

		$app->bruno->data['listenevent'] = true;
		$app->bruno->data['fixcode'] = self::$fixcode;
		$fcuri = '';
		$hashid = false;
		if(self::$fixcode){
			$app->bruno->data['listenevent'] = false;
			$fcuri = '/fc';
		}
		$app->bruno->data['body_preview'] = false;

		//Minimum require to make sure we are in preview mode
		$preview = false;
		$check = array(
			'preview' => true,
			'html_zoom' => true,
			'html_width' => true,
			'html_height' => true,
		);
		foreach ($data as $key => $value) {
			if(isset($check[$key])){
				$app->bruno->data[$key] = $value;
				unset($check[$key]);
			}
		}
		if(count($check)==0){
			$preview = true;
			self::$fixcode = false;
			$fcuri = '';
			$app->bruno->data['listenevent'] = false; //Disable events for preview mode
			$app->bruno->data['body_preview'] = true;
			$app->bruno->data['fixcode'] = false;
		}

		if($preview){
			$app->bruno->data['fixpicture'] = false;
			$app->bruno->data['show_hand'] = false;
		}

		$app->bruno->data['slide_prev'] = false;
		$app->bruno->data['slide_next'] = false;
		$app->bruno->data['get_refresh'] = false;
		$app->bruno->data['data_pitch_url_hide'] = false;
		$pitch_id = STR::integer_map($pitch_enc, true);
		if($pitch = Pitch::find($pitch_id)){
			$app->bruno->data['data_brand_pic'] = $base_url.'/bruno/screen/images/logo.png';
			if($pitch->brand_pic && $file = File::Where('id', $pitch->brand_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
				$app->bruno->data['data_brand_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
			}
			$app->bruno->data['data_brand'] = $app->bruno->data['domain'];
			if($pitch->brand && strlen($pitch->brand)>0){
				$app->bruno->data['data_brand'] = $pitch->brand;
			}
			if($list['nbr']>=1){
				if(isset($app->bruno->data['listenevent']) && $app->bruno->data['listenevent']){
					if($list['prev']!==false){
						$app->bruno->data['slide_prev'] = 'https://'.$app->bruno->http_host.$fcuri.'/'.$pitch_enc.'/'.$list['prev'];
					}
				}
				if($question = $pitch->question_offset($list['nbr']-1, array('id', 'u_at', 'parent_id', 'number', 'file_id', 'title', 'style'))){
					$session = false;
					$app->bruno->data['data_pitch_code'] = false;
					$app->bruno->data['data_pitch_code_length'] = 4;
					if(!$preview && !self::$fixcode){
						if($list['style']=='question'){
							$session = $this->set_session($question->id, 2); //Prepare session
						} else {
							$session = $this->set_session(null, 2); //Change to null so we can force mobiles to switch into the waiting screen
						}
						if($session && $session->code){
							$length = mb_strlen($session->code);
							$app->bruno->data['data_pitch_code_length'] = $length;
							$app->bruno->data['data_pitch_code'] = $session->code;
							if($length==6){
								$app->bruno->data['data_pitch_code'] = substr($session->code, 0, 3).' '.substr($session->code, 3, 3);
							} else if($length==8){
								$app->bruno->data['data_pitch_code'] = substr($session->code, 0, 4).' '.substr($session->code, 3, 4);
							}
						}
					} else if(!$preview && $hashid = Question::encrypt($question->id)){
						$length = mb_strlen($hashid);
						$app->bruno->data['data_pitch_code_length'] = $length;
						$app->bruno->data['data_pitch_code'] = $hashid;
						if($length==6){
							$app->bruno->data['data_pitch_code'] = substr($hashid, 0, 3).' '.substr($hashid, 3, 3);
						} else if($length==8){
							$app->bruno->data['data_pitch_code'] = substr($hashid, 0, 4).' '.substr($hashid, 3, 4);
						}
					}

					//Start WAMP room
					if(!$preview && !self::$fixcode && $list['style']=='question'){
						if($app->bruno->data['data_pitch_code'] && $app->bruno->data['data_pitch_code'] > 0){
							$entryData = array(
								'topicid'	=> 'quiz_'.$app->bruno->data['data_pitch_code'],
								'data'		=> $question->id,
								'when'		=> time(),
							);
							$context = new \ZMQContext();
							$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'api_websocket_session'); //$persistent_id is the same as the route in config/websocket.php
							$socket->connect("tcp://127.0.0.1:5555");
							$socket->send(json_encode($entryData));
						}
					}

					$app->bruno->data['slide_next'] = 'https://'.$app->bruno->http_host.$fcuri.'/'.$pitch_enc.'/'.$list['next'];

					$questionid_enc = STR::integer_map($question->id);

					$app->bruno->data['data_question'] = true;
					$app->bruno->data['data_question_title'] = $question->title; //Twig will do HTML encoding
					$app->bruno->data['data_question_picture'] = false;
					$app->bruno->data['data_question_style'] = $question->style;
					if($question->file_id && $file = File::Where('id', $question->file_id)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
						$app->bruno->data['data_question_picture'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
					}

					$app->bruno->data['data_question_style_picture'] = '/bruno/screen/images/icons/answers.png';
					if($question->style==2){
						$app->bruno->data['data_question_style_picture'] = '/bruno/screen/images/icons/pictures.png';
					} else if($question->style==3){
						$app->bruno->data['data_question_style_picture'] = '/bruno/screen/images/icons/statistics.png';
					} else if($question->style==4){
						$app->bruno->data['data_question_style_picture'] = '/bruno/screen/images/icons/survey.png';
					}

					$app->bruno->data['data_stats_iframe'] = $base_url.'/stats/'.$questionid_enc;

					if($list['style']=='answer'){
						//Do not refresh for answer
						$app->bruno->data['data_stats_iframe'] .= '/answer';
						$app->bruno->data['data_pitch_url_hide'] = true;
						$app->bruno->data['data_pitch_url'] = $base_url.'/bruno/wrapper/images/generic/neutral.png?0';
					} else {
						if(!$preview && $hashid){
							$app->bruno->data['data_pitch_url'] = $base_url.'/fixcode.jpg?hashid='.$hashid; //hashid is used to recover the fix session
						} else if(!$preview && $session){
							$app->bruno->data['data_pitch_url'] = $base_url.'/session.jpg?'.$session->id; //Is used only for cache
						} else {
							$app->bruno->data['data_pitch_url'] = $base_url.'/session/'.$questionid_enc.'.jpg?'.$question->id; //Suffix only used for cache
						}
					}

					if($preview){
						//Just simulate some data for preview purpose
						$app->bruno->data['data_stats_iframe'] .= '?preview=1';
						if(strpos($app->bruno->data['data_pitch_url'], '?')>=1){
							$app->bruno->data['data_pitch_url'] .= '&preview=1';
						} else {
							$app->bruno->data['data_pitch_url'] .= '?preview=1';
						}
					}

					if($question->style==2){
						$answers = Answer::Where('parent_id', $question->id)
							->whereNotNull('file_id')
							->take(6)
							->orderBy('number')
							->get(array('id', 'file_id', 'title', 'number'));
					} else {
						$answers = Answer::Where('parent_id', $question->id)
							->where(function($query) {
								$query
									->whereNotNull('file_id')
									->orWhere('title', '!=', '');
							})
							->take(6)
							->orderBy('number')
							->get(array('id', 'file_id', 'title', 'number'));
					}
					
					$data_answers = array();
					$correct = false;
					foreach ($answers as $key => $answer) {
						$prefix = 'data_answer_'.$answer->number;
						$data_answers[$answer->number] = $prefix;
						$app->bruno->data[$prefix] = true;
						$app->bruno->data[$prefix.'_title'] = $answer->title; //Twig will do HTML encoding
						$app->bruno->data[$prefix.'_picture'] = false;
						$app->bruno->data[$prefix.'_correct'] = false;
						if($answer->file_id && $file = File::Where('id', $answer->file_id)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
							$app->bruno->data[$prefix.'_picture'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
						}
						if($question->style!=2 && !$app->bruno->data[$prefix.'_picture'] && empty($answer->title)){
							$data_answers[$answer->number] = false;
							unset($data_answers[$answer->number]);
							$app->bruno->data[$prefix] = false;
							unset($answers[$key]);
							continue;
						}
						if($list['style']=='answer' && !$correct && $question->number == $answer->number){
							$app->bruno->data[$prefix.'_correct'] = true;
							$correct = true;
						}
					}
					if($list['style']=='answer' && !$correct){
						foreach ($answers as $key => $answer) {
							//Use the first one by default as correct if any error
							$prefix = 'data_answer_'.$answer->number;
							$app->bruno->data[$prefix.'_correct'] = true;
							break;
						}
					}

					ksort($data_answers);
					$app->bruno->data['data_answers'] = array();
					$i = 1;
					foreach ($data_answers as $prefix) {
						$key = $i;
						if($question->style==3 || $question->style==4){
							//Convert number to alphabet
							if($key==1){ $key = 'A'; }
							else if($key==2){ $key = 'B'; }
							else if($key==3){ $key = 'C'; }
							else if($key==4){ $key = 'D'; }
							else if($key==5){ $key = 'E'; }
							else if($key==6){ $key = 'F'; }
						}
						$app->bruno->data['data_answers'][$key] = $prefix;
						$i++;
					}
					
					if($list['style']=='question' && self::$show_hand && !$preview){
						$app->bruno->data['show_hand'] = true;
					}

					if($question->style==1){
						$app->render('/bundles/bruno/screen/templates/screen/qanda/questions.twig');
						return true;
					} else if($question->style==2){
						$app->render('/bundles/bruno/screen/templates/screen/qanda/pictures.twig');
						return true;
					} else if($question->style==3){
						$app->render('/bundles/bruno/screen/templates/screen/qanda/questions.twig');
						return true;
					} else if($question->style==4){
						$app->render('/bundles/bruno/screen/templates/screen/qanda/questions.twig');
						return true;
					}
				}
			}

			//We display START or END slide
			$app->bruno->data['get_style'] = 'answer';
			if($list['nbr']<=0){ //Start
				$session = $this->set_session(null, 1);
				$app->bruno->data['show_hand'] = false;
				$app->bruno->data['data_pitch_title'] = $pitch->title;
				if($user = User::find($pitch->c_by)){
					$app->bruno->data['data_pitch_by'] = $app->trans->getBRUT('screen', 0, 7).$user->username; //By Bruno Martin
				}
				$app->bruno->data['slide_prev'] = false;
				$app->bruno->data['slide_next'] = 'https://'.$app->bruno->http_host.$fcuri.'/'.$pitch_enc.'/1a';
				$app->render('/bundles/bruno/screen/templates/screen/info/pitch.twig');
				return true;
			} else { // END
				$session = $this->set_session(null, 0);
				$app->bruno->data['show_hand'] = false;
				$app->bruno->data['data_pitch_title'] = strtoupper($app->trans->getBRUT('screen', 0, 6)); //Thank you
				$app->bruno->data['data_pitch_by'] = '';
				$last_answer = $pitch->question->count() . 'b';
				$app->bruno->data['slide_prev'] = 'https://'.$app->bruno->http_host.$fcuri.'/'.$pitch_enc.'/'.$last_answer;
				$app->bruno->data['slide_next'] = false;
				$app->render('/bundles/bruno/screen/templates/screen/info/pitch.twig');
				return true;
			}
		}
		$session = $this->set_session(null, 0);
		$app->render('/bundles/bruno/screen/templates/generic/sorry.twig');
		return true;
	}

	public function stats_get($questionid_enc, $step='question'){
		$app = ModelBruno::getApp();
		$data = $this->stats_data($questionid_enc);
		$app->bruno->data['data_step'] = 'question';
		$app->bruno->data['get_refresh'] = true;
		if($step=='answer'){
			$app->bruno->data['get_refresh'] = false;
			$app->bruno->data['data_step'] = 'answer';
		}
		if(isset($data['data_question_style'])){
			if($data['data_question_style']==1){
				$app->render('/bundles/bruno/screen/templates/screen/stats/questions.twig');
				return true;
			} else if($data['data_question_style']==2){
				$app->render('/bundles/bruno/screen/templates/screen/stats/questions.twig');
				return true;
			} else if($data['data_question_style']==3){
				$app->render('/bundles/bruno/screen/templates/screen/stats/statistics.twig');
				return true;
			} else if($data['data_question_style']==4){
				$app->render('/bundles/bruno/screen/templates/screen/stats/statistics.twig');
				return true;
			}
		}
		$app->render('/bundles/bruno/screen/templates/generic/blank.twig');
	}

	public function statsjson_get($questionid_enc){
		$msg = array('data' => $this->stats_data($questionid_enc),);
		(new Json($msg))->render();
		return exit(0);
	}

	public function statsjs_get($questionid_enc){
		$app = ModelBruno::getApp();
		$app->response->headers->set('Content-Type', 'application/javascript');
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
		$data = $this->stats_data($questionid_enc);
		foreach ($data as $key => $value) {
			echo 'var statsjs_'.$key.'="'.$value.'";';
		}
	}

	public function stats_data($questionid_enc){
		$app = ModelBruno::getApp();
		$get = ModelBruno::getData();
		$question_id = STR::integer_map($questionid_enc, true);
		$question = Question::Where('id', $question_id)->first(array('id', 'style', 'number'));
		$statistics = false;
		$preview = false;
		$app->bruno->data['data_pitch_code'] = false;
		if(isset($get->preview) && $get->preview){
			$preview = true;
		} else {
			if(isset($_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_id'])){
				if($session = Session::find($_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_id'])){
					$statistics = Statistics::Where('session_id', $session->id)->where('question_id', $question_id)->first();
					if(!is_null($session->code)){
						$app->bruno->data['data_pitch_code'] = $session->code;
					}
				}
			}
		}

		$data = array();
		$data['data_preview'] = $app->bruno->data['data_preview'] = $preview;

		$data['data_questionid_enc'] = $app->bruno->data['data_questionid_enc'] = $questionid_enc;
		
		$data['get_style'] = $app->bruno->data['get_style'] = 'stats';
		$data['data_question_style'] = $app->bruno->data['data_question_style'] = false;

		if($question){

			$data['data_question_style'] = $app->bruno->data['data_question_style'] = $question->style;

			$data['data_participants'] = $app->bruno->data['data_participants'] = 0;
			if($preview){
				$data['data_participants'] = $app->bruno->data['data_participants'] = rand(200, 299);
			} else if($statistics){
				$data['data_participants'] = $app->bruno->data['data_participants'] = $total = intval($statistics->a) + intval($statistics->b) + intval($statistics->c) + intval($statistics->d) + intval($statistics->e) + intval($statistics->f);
			}

			if($question->style==1 || $question->style==2){
				if($preview){
					$random = rand(30, 70);
					$data['data_correct'] = $app->bruno->data['data_correct'] = $random;
					$data['data_not_correct'] = $app->bruno->data['data_not_correct'] = 100-$random;
				} else {
					$data['data_correct'] = $app->bruno->data['data_correct'] = 0;
					$data['data_not_correct'] = $app->bruno->data['data_not_correct'] = 0;
					if($statistics && $total>0){
						$letter = ModelBruno::numToAplha($question->number);
						if($letter && isset($statistics->$letter)){
							$data['data_correct'] = $app->bruno->data['data_correct'] = round(100 * $statistics->$letter / $total);
							$data['data_not_correct'] = $app->bruno->data['data_not_correct'] = round(100 * ($total - $statistics->$letter) / $total);
						}
					}
				}
			} else if($question->style==3){
				$column_width = 20;
				$column_between = 5;
				$column_side = 40;
				$answers_count = 0;
				$data['data_number_1'] = $app->bruno->data['data_number_1'] = 0;
				$data['data_number_2'] = $app->bruno->data['data_number_2'] = 0;
				$data['data_number_3'] = $app->bruno->data['data_number_3'] = 0;
				$data['data_number_4'] = $app->bruno->data['data_number_4'] = 0;
				$data['data_number_5'] = $app->bruno->data['data_number_5'] = 0;
				$data['data_number_6'] = $app->bruno->data['data_number_6'] = 0;
				if($preview){
					//We need a SQL call here because we don't store information into statistics
					$answers_count = Answer::Where('parent_id', $question->id)
						->where(function($query) {
							$query
								->whereNotNull('file_id')
								->orWhere('title', '!=', '');
						})
						->take(6)
						->count();

					$random_array = array(0, 0, 0, 0, 0, 0);
					for($i=0; $i<100; $i++){
						$random_array[rand(0, $answers_count-1)]++;
					}
					$data['data_number_1'] = $app->bruno->data['data_number_1'] = $random_array[0];
					$data['data_number_2'] = $app->bruno->data['data_number_2'] = $random_array[1];
					$data['data_number_3'] = $app->bruno->data['data_number_3'] = $random_array[2];
					$data['data_number_4'] = $app->bruno->data['data_number_4'] = $random_array[3];
					$data['data_number_5'] = $app->bruno->data['data_number_5'] = $random_array[4];
					$data['data_number_6'] = $app->bruno->data['data_number_6'] = $random_array[5];
				} else if(!$statistics){
					//Just display nothing
				} else {
					if($total>0){
						$array_letters = ['a', 'b', 'c', 'd', 'e', 'f'];
						foreach ($array_letters as $letter) {
							if(!is_null($statistics->$letter)){
								$answers_count++;
								$data['data_number_'.$answers_count] = $app->bruno->data['data_number_'.$answers_count] = round(100 * $statistics->$letter / $total);
							}
						}
					}
				}

				if($answers_count >= 5){
					$column_width = 15.5;
				}
				if($answers_count >= 6){
					$column_between = 1;
				}
				$column_side = (100 - ( ($answers_count*$column_width) + (($answers_count-1)*$column_between) )) / 2;

				$data['data_average'] = $app->bruno->data['data_average'] = 0;
				$data['data_column_width'] = $app->bruno->data['data_column_width'] = $column_width;
				$data['data_answers_count'] = $app->bruno->data['data_answers_count'] = $answers_count;
				$data['data_column_left_1'] = $app->bruno->data['data_column_left_1'] = $column_side + ((1-1) * ($column_between + $column_width));
				$data['data_column_left_2'] = $app->bruno->data['data_column_left_2'] = $column_side + ((2-1) * ($column_between + $column_width));
				$data['data_column_left_3'] = $app->bruno->data['data_column_left_3'] = $column_side + ((3-1) * ($column_between + $column_width));
				$data['data_column_left_4'] = $app->bruno->data['data_column_left_4'] = $column_side + ((4-1) * ($column_between + $column_width));
				$data['data_column_left_5'] = $app->bruno->data['data_column_left_5'] = $column_side + ((5-1) * ($column_between + $column_width));
				$data['data_column_left_6'] = $app->bruno->data['data_column_left_6'] = $column_side + ((6-1) * ($column_between + $column_width));
			
			} else if($question->style==4){
				$column_width = 20;
				$column_between = 5;
				$column_side = 40;
				$answers_count = 0;
				$data['data_number_1'] = $app->bruno->data['data_number_1'] = 0;
				$data['data_number_2'] = $app->bruno->data['data_number_2'] = 0;
				$data['data_number_3'] = $app->bruno->data['data_number_3'] = 0;
				$data['data_number_4'] = $app->bruno->data['data_number_4'] = 0;
				$data['data_number_5'] = $app->bruno->data['data_number_5'] = 0;
				$data['data_number_6'] = $app->bruno->data['data_number_6'] = 0;
				if($preview){
					//We need a SQL call here because we don't store information into statistics
					$answers_count = Answer::Where('parent_id', $question->id)
						->where(function($query) {
							$query
								->whereNotNull('file_id')
								->orWhere('title', '!=', '');
						})
						->take(6)
						->count();
					$data['data_number_1'] = $app->bruno->data['data_number_1'] = rand(0, 100);
					$data['data_number_2'] = $app->bruno->data['data_number_2'] = rand(0, 100);
					$data['data_number_3'] = $app->bruno->data['data_number_3'] = rand(0, 100);
					$data['data_number_4'] = $app->bruno->data['data_number_4'] = rand(0, 100);
					$data['data_number_5'] = $app->bruno->data['data_number_5'] = rand(0, 100);
					$data['data_number_6'] = $app->bruno->data['data_number_6'] = rand(0, 100);
				} else if(!$statistics){
					//Just display nothing
				} else {
					$data['data_participants'] = $app->bruno->data['data_participants'] = $statistics->answers;
					if($total>0){
						$array_letters = ['a', 'b', 'c', 'd', 'e', 'f'];
						foreach ($array_letters as $letter) {
							if(!is_null($statistics->$letter)){
								$answers_count++;
								if($statistics->$letter > 0 && !is_null($statistics->{'t_'.$letter})){
									$data['data_number_'.$answers_count] = $app->bruno->data['data_number_'.$answers_count] = round($statistics->{'t_'.$letter} / $statistics->$letter, 1);
								} else {
									$data['data_number_'.$answers_count] = $app->bruno->data['data_number_'.$answers_count] = 0;
								}
							}
						}
					}
				}

				if($answers_count >= 5){
					$column_width = 15.5;
				}
				if($answers_count >= 6){
					$column_between = 1;
				}
				$column_side = (100 - ( ($answers_count*$column_width) + (($answers_count-1)*$column_between) )) / 2;

				$data['data_column_width'] = $app->bruno->data['data_column_width'] = $column_width;
				$data['data_answers_count'] = $app->bruno->data['data_answers_count'] = $answers_count;
				$data['data_column_left_1'] = $app->bruno->data['data_column_left_1'] = $column_side + ((1-1) * ($column_between + $column_width));
				$data['data_column_left_2'] = $app->bruno->data['data_column_left_2'] = $column_side + ((2-1) * ($column_between + $column_width));
				$data['data_column_left_3'] = $app->bruno->data['data_column_left_3'] = $column_side + ((3-1) * ($column_between + $column_width));
				$data['data_column_left_4'] = $app->bruno->data['data_column_left_4'] = $column_side + ((4-1) * ($column_between + $column_width));
				$data['data_column_left_5'] = $app->bruno->data['data_column_left_5'] = $column_side + ((5-1) * ($column_between + $column_width));
				$data['data_column_left_6'] = $app->bruno->data['data_column_left_6'] = $column_side + ((6-1) * ($column_between + $column_width));
				//Averaga
				$data_average = 0;
				if($answers_count>0){
					for($i=1; $i<=$answers_count; $i++){
						$data_average += $data['data_number_'.$i];
					}
					$data_average = floor(10 * $data_average / $answers_count) / 10;
				}
				$data['data_average'] = $app->bruno->data['data_average'] = $data_average;
			}
		}
		return $data;
	}

	public function fixcode_get(){
		$fixcode = false;
		$get = ModelBruno::getData();
		if(isset($get->hashid) && $get->hashid){
			$fixcode = $get->hashid;
		}
		return $this->session_get(false, $fixcode);
	}

	public function session_get($questionid_enc=false, $fixcode=false){
		$app = ModelBruno::getApp();
		$get = ModelBruno::getData();
		$base_url = 'http://'.$app->bruno->shortlink;
		$url = false;
		
		$header_id = 0;
		$header_time = ModelBruno::getMStime();

		if($fixcode){
			$url = $base_url.'/c/'.$fixcode;
		} else if(isset($get->preview) && $get->preview){
			//In preview mode, we must have at least $questionid_enc definied since the session does not exists
			if($questionid_enc){
				$url = $base_url.'/p/'.$questionid_enc;
			}
		} else if($session = $this->get_session()){
			$header_id = $session->id;
			$header_time = $session->c_at;
			$url = $base_url.'/'.STR::integer_map($session->id);
		}
		
		if($url){
			ob_clean();
			flush();
			$gmt_mtime = gmdate('r', round($header_time/1000));
			header('Last-Modified: '.$gmt_mtime);
			header('Expires: '.gmdate(DATE_RFC1123, time()+16000000)); //About 6 months cached
			header('ETag: "'.md5($header_id.'-'.$header_time).'"');
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
				if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($header_id.'-'.$header_time)) {
					header('HTTP/1.1 304 Not Modified');
					session_write_close();
					return exit(0);
				}
			}
			//https://packagist.org/packages/endroid/qr-code
			$qrCode = new QrCode();
			$qrCode
				->setText($url)
				->setSize(640)
				->setPadding(20)
				->setErrorCorrection('medium')
				->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
				->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
				->setImageType(QrCode::IMAGE_TYPE_PNG)
			;
			header('Content-Type: '.$qrCode->getContentType());
			$qrCode->render();
			session_write_close();
			return exit(0);
		}

		$path = $app->bruno->path.'/bundles/bruno/wrapper/public/images/generic/neutral.png';
		if(is_file($path) && filesize($path)!==false){
			WideImage::load($path)->output('png');
			session_write_close();
			return exit(0);
		}
		$app->render('/bundles/bruno/screen/templates/generic/blank.twig');
	}

}
