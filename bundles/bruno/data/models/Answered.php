<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;

class Answered extends Model {

	protected $connection = 'data';

	protected $table = 'answered';
	protected $morphClass = 'answered';

	protected $primaryKey = 'id';

	public $timestamps = false;

////////////////////////////////////////////
/*
	//Many(Statistics) to One(Session)
	public function session(){
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\Session', 'session_id');
	}
*/
////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

////////////////////////////////////////////

	public function save(array $options = array()){
		if(!isset($this->id)){
			$this->c_at = ModelBruno::getMStime();
			return parent::save($options);
		} else {
			//We only allow creation
			return false;
		}
	}

	public static function isAuthorized($guest_id, $statistics_id, $question_id){
		//Check if the guest already answered on this session
		if(Answered::Where('guest_id', $guest_id)->where('statistics_id', $statistics_id)->where('question_id', $question_id)->first(array('id'))){
			return false;
		}
		//Check if the guest already answered on another session within 8H
		/*
		$timems_limit = ModelBruno::getMStime() - 8*3600*1000; //Gap of 8H
		if(Answered::Where('guest_id', $guest_id)->where('c_at', '>', $timems_limit)->where('question_id', $question_id)->first(array('id'))){
			return false;
		}
		*/
		return true;
	}

}
