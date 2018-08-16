<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;

class Answered extends Model {

	protected $connection = 'data';

	protected $table = 'answered';
	protected $morphClass = 'answered';

	protected $primaryKey = 'id';

	protected $enable_overwrite = false;

	const FC_TIME = 28800; //For dynamic code we block 8H
	const DYN_TIME = 60; //For fixcode we block 60s

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
		if(!isset($this->id) || $this->enable_overwrite){
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

	public function reset(){
		$this->enable_overwrite = true;
		$this->style = 1;
		$this->answer_id = null;
		$this->number = null;
		$this->correct = null;
		$this->s_a = null;
		$this->s_b = null;
		$this->s_c = null;
		$this->s_d = null;
		$this->s_e = null;
		$this->s_f = null;
		$this->info_0 = null;
		$this->info_1 = null;
		$this->info_2 = null;
		$this->info_3 = null;
	}

	public static function isAuthorized($guest_id, $statistics_id, $question_id, $fixcode=false){
		//Check if the guest already answered on this session
		if($answered = Answered::Where('guest_id', $guest_id)->where('statistics_id', $statistics_id)->where('question_id', $question_id)->first(array('id', 'c_at'))){
			if($fixcode && $answered->c_at <= (ModelBruno::getMStime() - (self::FC_TIME)*1000)){ //Gap of 1min only for fixcode
				return true;
			}
			return false;
		}
		return true;
	}

}
