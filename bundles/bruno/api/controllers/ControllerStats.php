<?php
// Category 11

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Session;
use \bundles\bruno\data\models\Statistics;
use \bundles\bruno\data\models\Answered;
use \bundles\bruno\data\models\data\Guest;

class ControllerStats extends Controller {

	public function session_post(){
		$data = ModelBruno::getData();
		$msg = 'no record';
		if(isset($data->id) && isset($data->md5)){

			$statistics = Statistics::getStats($data->id, $data->md5)->get();
			
			$statistics_list = array();
			$sessions_list = array();
			foreach ($statistics as $stat) {
				$statistics_list[$stat->id] = $stat->id;
				$sessions_list[$stat->session_id] = $stat->session_id;
			}

			$session = Session::WhereIn('id', $sessions_list)->get();
			$answered = Answered::WhereIn('statistics_id', $statistics_list)->get();

			$msg = array();
			$msg['msg'] = 'Sessions';
			$msg['data'] = array(
				'session' => $session->toArray(),
				'statistics' => $statistics->toArray(),
				'answered' => $answered->toArray(),
			);

		}
		(new Json($msg))->render();
		return exit(0);
	}

	public function session_post_old(){
		$data = ModelBruno::getData();
		$msg = 'no record';
		if(isset($data->id) && isset($data->md5)){
			if($statistics = Statistics::getStatistics($data->id, $data->md5)){
				$msg = array();
				$msg['msg'] = 'Sessions';
				$msg['data'] = array();



				$sessions_list = array();
				$statistics_list = array();
				$statistics_to_session = array();
				$participants = array();
				foreach ($statistics as $stat) {
					$sessions_list[$stat->session_id] = $stat->session_id;
					$statistics_list[$stat->id] = $stat->id;
					$statistics_to_session[$stat->id] = $stat->session_id;
					$participants[$stat->session_id] = array();
				}

				if(count($sessions_list)>0){
					
					$sessions = array();
					$sessions_items = Session::WhereIn('id', $sessions_list)->get(array('id', 'md5', 'c_at'));
					foreach ($sessions_items as $item) {
						$sessions[$item->id] = $item;
					}

					$answered_items = Answered::WhereIn('statistics_id', $statistics_list)->get(array('id', 'guest_id', 'statistics_id', 'ad_clicks'));
					foreach ($answered_items as $item) {
						if(isset($statistics_to_session[$item->statistics_id])){
							$session_id = $statistics_to_session[$item->statistics_id];
							if(isset($participants[$session_id])){
								if(isset($participants[$session_id][$item->guest_id])){
									$participants[$session_id][$item->guest_id]++;
								} else {
									$participants[$session_id][$item->guest_id] = 1;
								}
							}
						}
					}
					
					$items = array();
					foreach ($statistics as $stat) {
						if(isset($items[$stat->session_id])){
							$items[$stat->session_id]['ad_clicks'] += (int) $stat->ad_clicks;
						} else if(isset($sessions[$stat->session_id])){
							$session = $sessions[$stat->session_id];
							$items[$session->id] = array(
								'id' => (int) $session->id,
								'md5' => $session->md5,
								'c_at' => (int) $session->c_at,
								'participants' => count($participants[$stat->session_id]),
								'ad_clicks' => (int) $stat->ad_clicks,
							);
						}
					}
					$msg['data'] = $items;
					
				}

				krsort($msg['data']);
			}
		}
		(new Json($msg))->render();
		return exit(0);
	}

	public function statistics_post(){
		$data = ModelBruno::getData();
		$msg = 'no record';
		if(isset($data->id) && isset($data->md5)){
			if($statistics = Statistics::getStatistics($data->id, $data->md5)){
				$msg = array();
				$msg['msg'] = 'Statistics';
				$msg['data'] = array();
				foreach ($statistics as $stat) {
					$participants = $stat->a + $stat->b + $stat->c + $stat->d + $stat->e + $stat->f;
					$letter = ModelBruno::numToAplha($stat->number);
					$correct = $stat->{$letter};
					$msg['data'][$stat->c_at] = array(
						'id' => (int) $stat->id,
						'md5' => $stat->md5,
						'c_at' => (int) $stat->c_at,
						'participants' => (int) $participants,
						'correct' => (int) $correct,
						'ad_clicks' => (int) $stat->ad_clicks,
					);
				}
				krsort($msg['data']);
			}
		}
		(new Json($msg))->render();
		return exit(0);
	}

	public function adclick_post(){
		$data = ModelBruno::getData();
		if(isset($data->id) && isset($data->md5)){
			if($statistics = Statistics::Where('id', $data->id)->where('md5', $data->md5)->first(array('id', 'question_id', 'ad_clicks'))){
				$statistics->ad_clicks++;
				$statistics->save();
				//We must get guest_id here, because bundle is recognized as "api", not "quiz"
				\bundles\bruno\wrapper\hooks\SetGuest();
				if($guest = Guest::getUser()){
					if($answered = Answered::Where('guest_id', $guest->id)->where('statistics_id', $statistics->id)->where('question_id', $statistics->question_id)->first()){
						$answered->ad_clicks++;
						$answered->save();
					}
				}
			}
		}
				
		(new Json('Thank you'))->render();
		return exit(0);
	}
	
}
