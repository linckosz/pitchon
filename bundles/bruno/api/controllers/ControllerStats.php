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
		$data = ModelBruno::getData();
		$msg = 'no record';
		if(isset($data->id) && isset($data->md5)){
			if($statistics = Statistics::getStats($data->id, $data->md5)){
				$msg = array();
				$msg['msg'] = 'Sessions';
				$msg['data'] = array();
				foreach ($statistics as $item) {
					$participants = $item->a + $item->b + $item->c + $item->d + $item->e + $item->f;
					$letter = ModelBruno::numToAplha($item->number);
					$correct = $item->{$letter};
					$msg['data'][$item->c_at] = array(
						'id' => (int) $item->id,
						'md5' => $item->md5,
						'c_at' => (int) $item->c_at,
						'participants' => (int) $participants,
						'correct' => (int) $correct,
						'ad_clicks' => (int) $item->ad_clicks,
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
			if($statistics = Statistics::Where('id', $data->id)->where('md5', $data->md5)->first(array('id', 'ad_clicks'))){
				$statistics->ad_clicks++;
				$statistics->save();
			}
		}
		(new Json('Thank you'))->render();
		return exit(0);
	}
	
}
