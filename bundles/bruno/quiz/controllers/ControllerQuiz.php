<?php

namespace bundles\bruno\quiz\controllers;

use \libs\STR;
use \libs\Email;
use \libs\Controller;
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
		$this->prepared = true;
		return true;
	}

	protected function question_display($question_id){
		$app = ModelBruno::getApp();
		$app->bruno->data['data_questionid_enc'] = '';
		if($question = Question::Where('id', $question_id)->first(array('id', 'u_at', 'parent_id', 'number', 'file_id', 'title', 'style'))){

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
		if(isset($_COOKIE) && isset($_COOKIE['code']) && is_numeric($_COOKIE['code']) && $_COOKIE['code'] > 0){
			$app->bruno->data['data_scan_code'] = intval($_COOKIE['code']);
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
				setcookie('code', $session->code, time()+1800, '/', '.'.$app->bruno->http_host); //Only 30min (because a pitch should not exceed 30 min)
			} else {
				setcookie('code', null, time()-3600, '/', '.'.$app->bruno->http_host);
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
		if($session = Session::Where('code', $code)->first(array('id', 'question_id', 'code'))){
			if($session->code){
				setcookie('code', $session->code, time()+1800, '/', '.'.$app->bruno->http_host); //Only 30min (because a pitch should not exceed 30 min)
			} else {
				setcookie('code', null, time()-3600, '/', '.'.$app->bruno->http_host);
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
		$answer_id = STR::integer_map($answerid_enc, true);
		$guest_id = $app->bruno->data['guest_id'];
		$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
		$app->bruno->data['data_ad_pic'] = $base_url.'/bruno/screen/images/logo.png';
		$app->bruno->data['data_ad'] = $app->bruno->data['domain'];
		if($statisticsid_enc=='preview'){ //Mode demo
			$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 16); //Result
			if($answer = Answer::Where('id', $answer_id)->first(array('id', 'number', 'parent_id'))){
				$letter = $answer->letter();
				if($question = Question::Where('id', $answer->parent_id)->first(array('id', 'style', 'number', 'parent_id'))){
					$app->bruno->data['data_question_id'] = $question->id;
					if($pitch = Pitch::find($question->parent_id)){
						if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
							$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
						}
						if($pitch->ad && strlen($pitch->ad)>0){
							$app->bruno->data['data_ad'] = $pitch->ad;
						}
						$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
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
				$question = Question::Where('id', $statistics->question_id)->first(array('id', 'style', 'number', 'parent_id'));
				$app->bruno->data['data_question_id'] = $question->id;
				if($question && $pitch = Pitch::find($question->parent_id)){
					$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
					if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
						$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
					}
					if($pitch->ad && strlen($pitch->ad)>0){
						$app->bruno->data['data_ad'] = $pitch->ad;
					}
					$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
				}
				//Check if already answered
				if(!Answered::isAuthorized($guest_id, $statistics->id, $statistics->question_id)){
					$app->bruno->data['data_answered'] = true;
					$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
					return true;
				} else if($answer = Answer::Where('id', $answer_id)->first(array('id', 'number', 'parent_id'))){
					$letter = $answer->letter();
					if(isset($statistics->$letter)){
						if($question){
							$value = intval($statistics->$letter);
							$value++;
							$statistics->$letter = $value;
							$statistics->save();
							if($answer->number == $question->number){
								$app->bruno->data['data_correct'] = true;
							}
							$answered = new Answered;
							$answered->guest_id = $guest_id;
							$answered->statistics_id = $statistics->id;
							$answered->question_id = $question->id;
							$answered->answer_id = $answer->id;
							$answered->style = $question->style;
							$answered->correct = null;
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
		$guest_id = $app->bruno->data['guest_id'];
		$base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
		$app->bruno->data['data_ad_pic'] = $base_url.'/bruno/screen/images/logo.png';
		$app->bruno->data['data_ad'] = $app->bruno->data['domain'];
		if($statisticsid_enc=='preview'){ //Mode demo
			if($questionid_enc){
				$question_id = STR::integer_map($questionid_enc, true);
				if($question = Question::Where('id', $question_id)->first(array('id', 'style', 'number', 'parent_id'))){
					if($pitch = Pitch::find($question->parent_id)){
						if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
							$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
						}
						if($pitch->ad && strlen($pitch->ad)>0){
							$app->bruno->data['data_ad'] = $pitch->ad;
						}
						$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
					}
				}
			}
			$app->bruno->data['title'] = $app->trans->getBRUT('quiz', 0, 16); //Result
			$app->render('/bundles/bruno/quiz/templates/quiz/result/statistics.twig');
			return true;
		} else {
			$statistics_id = STR::integer_map($statisticsid_enc, true);
			if($statistics = Statistics::Where('id', $statistics_id)->first()){
				$question = Question::Where('id', $statistics->question_id)->first(array('id', 'style', 'parent_id'));
				if($question && $pitch = Pitch::find($question->parent_id)){
					if($pitch->ad_pic && $file = File::Where('id', $pitch->ad_pic)->first(array('id', 'uploaded_by', 'link', 'ori_ext', 'u_at'))){
						$app->bruno->data['data_ad_pic'] = $base_url.'/files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext.'?'.$file->u_at;
					}
					if($pitch->ad && strlen($pitch->ad)>0){
						$app->bruno->data['data_ad'] = $pitch->ad;
					}
					$app->bruno->data['data_ad_link'] = Email::buildUrl($app->bruno->data['data_ad']);
				}
				//Check if already answered
				if(!Answered::isAuthorized($guest_id, $statistics->id, $statistics->question_id)){
					$app->bruno->data['data_answered'] = true;
					$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
					return true;
				} else if($question){
					$data = ModelBruno::getData();					
					$answered = new Answered;
					$answered->guest_id = $guest_id;
					$answered->statistics_id = $statistics->id;
					$answered->question_id = $question->id;
					$answered->style = 4;
					$answered->correct = null;
					if(isset($data->score) && $data->score && $score = json_decode($data->score)){
						foreach ($score as $key => $value) {
							if($letter = ModelBruno::numToAplha($key)){
								$answered->{'s_'.$letter} = $value;
								$statistics->$letter = intval($statistics->$letter)+1;
								$statistics->{'s_'.$letter} = intval($statistics->{'s_'.$letter}) + $value;
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
					$app->render('/bundles/bruno/quiz/templates/quiz/result/statistics.twig');
					return true;
				}
			}

		}
		$app->render('/bundles/bruno/quiz/templates/quiz/result/wait.twig');
		return true;
	}

}
