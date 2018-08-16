<?php

namespace bundles\bruno\quiz\controllers;

use \libs\STR;
use \libs\Email;
use \libs\Controller;
use \libs\Vanquish;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Session;
use \bundles\bruno\data\models\Statistics;
use \bundles\bruno\data\models\Answered;
use \bundles\bruno\wrapper\models\Action;
use \bundles\bruno\data\models\data\Answer;
use \bundles\bruno\data\models\data\File;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\Guest;


class ControllerQuiz extends Controller {

	protected $prepared = false;

	protected $fixcode = false;

	//In Controllers, __construct does not work, so we use prepare()
	protected function prepare(){
		if($this->prepared){
			return true;
		}
		$data = ModelBruno::getData();
		$app = ModelBruno::getApp();

		$app->bruno->data['data_statisticsid_enc'] = false; //If false, we are in preview mode
		$app->bruno->data['html_zoom'] = false;
		if(isset($data->zoom)){
			$app->bruno->data['html_zoom'] = (float) $data->zoom;
		}
		$app->bruno->data['data_preview'] = false;
		if(isset($data->preview) && $data->preview){
			$app->bruno->data['data_preview'] = true;
		}
		$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
		$app->bruno->data['data_ad_pic'] = $base_url.'/bruno/screen/images/logo.png';
		$app->bruno->data['data_ad'] = $app->bruno->data['domain'];
		$this->prepared = true;
		return true;
	}

	protected function question_display($question_id){
		$app = ModelBruno::getApp();
		$app->bruno->data['data_questionid_enc'] = '';
		if($question = Question::Where('id', $question_id)->first(array('id', 'u_at', 'parent_id', 'number', 'file_id', 'title', 'style'))){

			//Set host_id
			if(!Vanquish::get('host_id')){
				if($pitch = Pitch::find($question->parent_id)){
					//This will hold a guest to the latest pitch creator
					//A second pitch creator won't overwrite the first one (he has the priority)
					Vanquish::set(array('host_id' => $pitch->c_by,));
				}
			}

			$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];

			$app->bruno->data['data_question'] = true;
			$app->bruno->data['data_questionid_enc'] = STR::integer_map($question->id);
			$app->bruno->data['data_question_title'] = $question->title; //Twig will do a HTML encode
			$app->bruno->data['data_question_picture'] = false;
			$app->bruno->data['data_question_style'] = $question->style;
			if($question->file_id && $file = File::Where('id', $question->file_id)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
				$app->bruno->data['data_question_picture'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
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
			foreach ($answers as $key => $answer) {
				$prefix = 'data_answer_'.$answer->number;
				$data_answers[$answer->number] = $prefix;
				$app->bruno->data[$prefix] = true;
				$app->bruno->data[$prefix.'_id'] = STR::integer_map($answer->id);
				$app->bruno->data[$prefix.'_title'] = $answer->title; //Twig will do a HTML encode
				$app->bruno->data[$prefix.'_picture'] = false;
				$app->bruno->data[$prefix.'_number'] = $answer->number;
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
			if($question->style==4){
				$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 18); //Give a score
				$app->render('/bundles/bruno/quiz/templates/quiz/qanda/survey.twig');
			} else {
				$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 14); //Single choice
				$app->render('/bundles/bruno/quiz/templates/quiz/qanda/questions.twig');
			}
			return true;
		}
		$app->render('/bundles/bruno/quiz/templates/generic/sorry.twig');
		return true;
	}

	public function scan_get(){
		$app = ModelBruno::getApp();
		$app->bruno->data['data_scan_code'] = false;
		$quiz_code = $app->bruno->data['bruno_dev'].'_quiz_code';
		if(isset($_COOKIE) && isset($_COOKIE[$quiz_code]) && strlen($_COOKIE[$quiz_code]) > 0){
			$app->bruno->data['data_scan_code'] = mb_strtolower($_COOKIE[$quiz_code]);
		}
		$app->render('/bundles/bruno/quiz/templates/quiz/scan.twig');
		return true;
	}


	public function session_get($sessionid_enc){
		$app = ModelBruno::getApp();
		$this->prepare();
		$session_id = STR::integer_map($sessionid_enc, true);
		if($session = Session::Where('id', $session_id)->first(array('id', 'question_id', 'code'))){
			if($session->code){
				setcookie($app->bruno->data['bruno_dev'].'_quiz_code', $session->code, time()+1800, '/'); //Only 30min (because a pitch should not exceed 30 min)
			} else {
				setcookie($app->bruno->data['bruno_dev'].'_quiz_code', null, time()-3600, '/');
			}
			if($session->question_id){
				if($statistics = Statistics::unlock($session->id, $session->question_id)){
					$app->bruno->data['data_statisticsid_enc'] = STR::integer_map($statistics->id);
				}
				return $this->question_display($session->question_id);
			}
		}
		$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
		return true;
	}

	public function code_get($code){
		$app = ModelBruno::getApp();
		$this->prepare();
		//Check if it's a fixcode (has alphabet)
		if($question_id = Question::decrypt($code)){
			//We don't save cookies
			setcookie($app->bruno->data['bruno_dev'].'_quiz_code', null, time()-3600, '/');
			$session = Session::Where('question_hashid', $code)->first(array('id'));
			if(!$session){
				//Create the session, but must be in a try because hundred of users can do this operation at the same time
				$session = new Session;
				$session->md5 = Session::get_session_md5();
				$session->question_id = $question_id;
				$session->question_hashid = $code;
				try {
					$session->save(); //It may fail if somebody else is faster
				} catch (\Exception $e){
					//Maybe someone else did it slightly faster
					$session = Session::Where('question_hashid', $code)->first(array('id'));
				}
			}
			if($session){
				//We use $question_id instead of $session->question_id to insure the session is fixed to the question
				if($statistics = Statistics::unlock($session->id, $question_id)){
					$app->bruno->data['data_statisticsid_enc'] = STR::integer_map($statistics->id);
				}
				//We block only 1min for fixcode
				if(isset($_SESSION['block_'.$statistics->id]) && $_SESSION['block_'.$statistics->id] > time()){
					$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
					return true;
				}
				return $this->question_display($question_id);
			}
		} else if($session = Session::Where('code', $code)->first(array('id', 'question_id', 'code'))){
			if($session->code){
				setcookie($app->bruno->data['bruno_dev'].'_quiz_code', $session->code, time()+1800, '/'); //Only 30min (because a pitch should not exceed 30 min)
			} else {
				setcookie($app->bruno->data['bruno_dev'].'_quiz_code', null, time()-3600, '/');
			}
			if($session->question_id){
				if($statistics = Statistics::unlock($session->id, $session->question_id)){
					$app->bruno->data['data_statisticsid_enc'] = STR::integer_map($statistics->id);
				}
				//We block 8H for dynamic code (since a code a renew frequently, this should not affect the user)
				if(isset($_SESSION['block_'.$statistics->id]) && $_SESSION['block_'.$statistics->id] > time()){
					$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
					return true;
				}
				return $this->question_display($session->question_id);
			}
		}
		$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
		return true;
	}

	protected function inform_screen($statistics){
		if($session = Session::Where('id', $statistics->session_id)->whereNotNull('code')->first(array('id', 'code'))){
			if(is_numeric($session->code) && $session->code > 0){
				$entryData = array(
					'topicid'	=> 'screen_'.$session->code,
					'data'		=> true,
					'when'		=> time(),
				);
				$context = new \ZMQContext();
				$socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'api_websocket_session'); //$persistent_id is the same as the route in config/websocket.php
				$socket->connect("tcp://127.0.0.1:5555");
				$socket->send(json_encode($entryData));
			}
		}
	}

	public function question_get($questionid_enc){
		$this->prepare();
		$question_id = STR::integer_map($questionid_enc, true);
		$this->question_display($question_id);
		return true;
	}

	public function answer_get($statisticsid_enc, $answerid_enc){
		$app = ModelBruno::getApp();
		$this->prepare();
		$app->bruno->data['data_answered'] = false;
		$app->bruno->data['data_correct'] = false;
		$app->bruno->data['data_question_id'] = false;
		$app->bruno->data['data_statistics_id'] = false;
		$app->bruno->data['data_statistics_md5'] = false;
		$app->bruno->data['data_ad_visit'] = '';
		$answer_id = STR::integer_map($answerid_enc, true);
		$guest_id = $app->bruno->data['guest_id'];
		if($statisticsid_enc=='preview'){ //Mode demo
			$app->bruno->data['data_preview'] = true;
			$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 16); //Result
			if($answer = Answer::Where('id', $answer_id)->first(array('id', 'number', 'parent_id'))){
				$letter = $answer->letter();
				if($question = Question::Where('id', $answer->parent_id)->first(array('id', 'style', 'number', 'parent_id'))){
					$app->bruno->data['data_question_id'] = $question->id;
					if($pitch = Pitch::find($question->parent_id)){
						Vanquish::set(array('host_id' => $pitch->c_by,)); //This will hold a guest to the latest pitch creator
						$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
						if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
							$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
						}
						if($pitch->ad && strlen($pitch->ad)>0){
							$app->bruno->data['data_ad'] = $pitch->ad;
						}
						$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
						if($app->bruno->data['data_ad_link']){
							if(strpos($app->bruno->data['data_ad_link'], 'mailto:') === 0){
								//$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 27); //Contact us
							} else {
								$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 26); //Visit us at
							}
						}
					}
					if($answer->number == $question->number){
						$app->bruno->data['data_correct'] = true;
					}
					if($question->style==1 || $question->style==2){ //Questions, Pictures
						$app->render('/bundles/bruno/quiz/templates/quiz/result/answer.twig');
					} else {
						$app->render('/bundles/bruno/quiz/templates/quiz/result/statistics.twig');
					}
					return true;
				}
			}
		} else {
			$statistics_id = STR::integer_map($statisticsid_enc, true);
			if($statistics = Statistics::Where('id', $statistics_id)->first()){
				if(Session::Where('id', $statistics->session_id)->whereNotNull('question_hashid')->first(array('id'))){
					$this->fixcode = true;
				}
				$app->bruno->data['data_statistics_id'] = $statistics->id;
				$app->bruno->data['data_statistics_md5'] = $statistics->md5;
				$question = Question::Where('id', $statistics->question_id)->first(array('id', 'style', 'number', 'parent_id'));
				$app->bruno->data['data_question_id'] = $question->id;
				if($question && $pitch = Pitch::find($question->parent_id)){
					Vanquish::set(array('host_id' => $pitch->c_by,)); //This will hold a guest to the latest pitch creator
					$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
					if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
						$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
					}
					if($pitch->ad && strlen($pitch->ad)>0){
						$app->bruno->data['data_ad'] = $pitch->ad;
					}
					$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
					if($app->bruno->data['data_ad_link']){
						if(strpos($app->bruno->data['data_ad_link'], 'mailto:') === 0){
							//$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 27); //Contact us
						} else {
							$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 26); //Visit us at
						}
					}
				}

				//Check if already answered
				if(
					   (isset($_SESSION['block_'.$statistics->id]) && $_SESSION['block_'.$statistics->id] > time())
					|| !Answered::isAuthorized($guest_id, $statistics->id, $statistics->question_id, $this->fixcode)
				){
					$app->bruno->data['data_answered'] = true;
					$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
					return true;
				}
				//Setup a blocking timer
				if($this->fixcode){
					//This stay true while the website is alive (browser not closed because of the PHP session)
					$_SESSION['block_'.$statistics->id] = time() + Answered::FC_TIME; //+60s
				} else {
					$_SESSION['block_'.$statistics->id] = time() + Answered::DYN_TIME; //+8H (but in all the case we don't allow overwring for dynamic code)
				}

				if($answer = Answer::Where('id', $answer_id)->first(array('id', 'number', 'parent_id'))){
					$letter = $answer->letter();
					if(isset($statistics->$letter)){
						if($question){
							$statistics->$letter = intval($statistics->$letter)+1;
							$statistics->answers = intval($statistics->answers)+1;
							$statistics->save();
							if($answer->number == $question->number){
								$app->bruno->data['data_correct'] = true;
							}
							$answered = false;
							if($this->fixcode){
								$answered = Answered::Where('guest_id', $guest_id)->where('statistics_id', $statistics_id)->where('question_id', $question->id)->first();
							}
							if($$answered){
								$answered->reset();
							} else {
								$answered = new Answered;
							}
							$answered->guest_id = $guest_id;
							$answered->statistics_id = $statistics->id;
							$answered->question_id = $question->id;
							$answered->answer_id = $answer->id;
							$answered->style = $question->style;
							$answered->correct = null;
							$answered->number = $answer->number;
							if($question->style==1 || $question->style==2){ //Answers or Pictures
								$answered->correct = $app->bruno->data['data_correct'];
							}
							$user_info = Action::getUserInfo();
							$answered->info_0 = $user_info[0];
							$answered->info_1 = $user_info[1];
							$answered->info_2 = $user_info[2];
							$answered->info_3 = $user_info[3];
							try { //In case there is a doublon
								$answered->save();
							} catch (\Exception $e){
								//Do nothing
							}
							$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 16); //Result

							//Inform the screen
							$this->inform_screen($statistics);

							if($question->style==1 || $question->style==2){
								$app->render('/bundles/bruno/quiz/templates/quiz/result/answer.twig');
							} else {
								$app->render('/bundles/bruno/quiz/templates/quiz/result/statistics.twig');
							}
							return true;
						}
					}
				}
			}

		}
		$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
		return true;
	}

	public function survey_get($statisticsid_enc, $questionid_enc=false){
		$app = ModelBruno::getApp();
		$this->prepare();
		$app->bruno->data['data_answered'] = false;
		$app->bruno->data['data_ad_visit'] = '';
		$guest_id = $app->bruno->data['guest_id'];
		if($statisticsid_enc=='preview'){ //Mode demo
			$app->bruno->data['data_preview'] = true;
			if($questionid_enc){
				$question_id = STR::integer_map($questionid_enc, true);
				if($question = Question::Where('id', $question_id)->first(array('id', 'style', 'number', 'parent_id'))){
					if($pitch = Pitch::find($question->parent_id)){
						Vanquish::set(array('host_id' => $pitch->c_by,)); //This will hold a guest to the latest pitch creator
						$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
						if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
							$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
						}
						if($pitch->ad && strlen($pitch->ad)>0){
							$app->bruno->data['data_ad'] = $pitch->ad;
						}
						$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
						if($app->bruno->data['data_ad_link']){
							if(strpos($app->bruno->data['data_ad_link'], 'mailto:') === 0){
								//$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 27); //Contact us
							} else {
								$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 26); //Visit us at
							}
						}
					}
				}
			}
			$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 16); //Result
			$app->render('/bundles/bruno/quiz/templates/quiz/result/statistics.twig');
			return true;
		} else {
			$statistics_id = STR::integer_map($statisticsid_enc, true);
			if($statistics = Statistics::Where('id', $statistics_id)->first()){
				if(Session::Where('id', $statistics->session_id)->whereNotNull('question_hashid')->first(array('id'))){
					$this->fixcode = true;
				}
				$question = Question::Where('id', $statistics->question_id)->first(array('id', 'style', 'parent_id'));
				if($question && $pitch = Pitch::find($question->parent_id)){
					Vanquish::set(array('host_id' => $pitch->c_by,)); //This will hold a guest to the latest pitch creator
					$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
					if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
						$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
					}
					if($pitch->ad && strlen($pitch->ad)>0){
						$app->bruno->data['data_ad'] = $pitch->ad;
					}
					$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
					if($app->bruno->data['data_ad_link']){
						if(strpos($app->bruno->data['data_ad_link'], 'mailto:') === 0){
							//$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 27); //Contact us
						} else {
							$app->bruno->data['data_ad_visit'] = $app->trans->getBRUT('quiz', 0, 26); //Visit us at
						}
					}
				}

				//Check if already answered
				if(
					   (isset($_SESSION['block_'.$statistics->id]) && $_SESSION['block_'.$statistics->id] > time())
					|| !Answered::isAuthorized($guest_id, $statistics->id, $statistics->question_id, $this->fixcode)
				){
					$app->bruno->data['data_answered'] = true;
					$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
					return true;
				}
				//Setup a blocking timer
				if($this->fixcode){
					//This stay true while the website is alive (browser not closed because of the PHP session)
					$_SESSION['block_'.$statistics->id] = time() + Answered::FC_TIME; //+60s
				} else {
					$_SESSION['block_'.$statistics->id] = time() + Answered::DYN_TIME; //+8H (but in all the case we don't allow overwring for dynamic code)
				}

				if($question){
					$data = ModelBruno::getData();					
					$answered = false;
					if($this->fixcode){
						$answered = Answered::Where('guest_id', $guest_id)->where('statistics_id', $statistics_id)->where('question_id', $question->id)->first();
					}
					if($$answered){
						$answered->reset();
					} else {
						$answered = new Answered;
					}
					$answered->guest_id = $guest_id;
					$answered->statistics_id = $statistics->id;
					$answered->question_id = $question->id;
					$answered->style = 4;
					$answered->correct = null;
					$statistics->answers = intval($statistics->answers)+1;
					if(isset($data->score) && $data->score && $score = json_decode($data->score)){
						foreach ($score as $key => $value) {
							if($letter = ModelBruno::numToAplha($key)){
								$answered->{'s_'.$letter} = $value; //s_ stands for Score
								$statistics->$letter = intval($statistics->$letter)+1;
								$statistics->{'t_'.$letter} = intval($statistics->{'t_'.$letter}) + $value; //t_ stands for Total (cumulated scores)
							}
						}
					}
					$user_info = Action::getUserInfo();
					$answered->info_0 = $user_info[0];
					$answered->info_1 = $user_info[1];
					$answered->info_2 = $user_info[2];
					$answered->info_3 = $user_info[3];
					try { //In case there is a doublon
						$statistics->save();
						$answered->save();
					} catch (\Exception $e){
						//Do nothing
					}
					$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 16); //Result

					//Inform the screen
					$this->inform_screen($statistics);

					$app->render('/bundles/bruno/quiz/templates/quiz/result/statistics.twig');
					return true;
				}
			}

		}
		$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
		return true;
	}

}
