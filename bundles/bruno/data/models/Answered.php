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
			$dirty = (array) $this->getDirty();
			//We only allow creation and modification of ad_clicks
			if(isset($dirty['ad_clicks']) && count($dirty) == 1){
				return parent::save($options);
			} else {
				return false;
			}
		}
	}

	public static function isAuthorized($guest_id, $statistics_id, $question_id, $fixcode=false){
		//Check if the guest already answered on this session
		if($answered = Answered::Where('guest_id', $guest_id)->where('statistics_id', $statistics_id)->where('question_id', $question_id)->first(array('id', 'c_at'))){
			if($fixcode && $answered->c_at <= (ModelBruno::getMStime() - 60*1000)){ //Gap of 1min only for fixcode
				return true;
			}
			return false;
		}
		return true;
	}

}
