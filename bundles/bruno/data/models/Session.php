<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;

class Session extends Model {

	protected $connection = 'data';

	protected $table = 'session';
	protected $morphClass = 'session';

	protected $primaryKey = 'id';

	public $timestamps = false;

	protected $visible = array(
		'id',
		'c_at',
	);

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

////////////////////////////////////////////

	public function save(array $options = array()){
		$time_ms = ModelBruno::getMStime();
		if(!isset($this->id)){
			$this->c_at = $time_ms;
			if(!isset($this->md5)){
				$this->md5 = md5(uniqid('', true));
			}
		}
		$this->u_at = $time_ms;
		return parent::save($options);
	}

	public static function get_session_md5(){
		$app = ModelBruno::getApp();
		if(isset($_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_md5'])){
			$md5 = $_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_md5'];
		} else {
			$md5 = md5(uniqid('', true));
			//Find a unique md5
			while(Session::Where('id', $md5)->first(array('id'))){
				$md5 = md5(uniqid('', true));
			}
		}
		$timelimit = time()+(8*3600); //8H maximum without consulting it
		setcookie($app->bruno->data['bruno_dev'].'_screen_session_md5', $md5, $timelimit, '/');
		$_COOKIE[$app->bruno->data['bruno_dev'].'_screen_session_md5'] = $md5;
		return $md5;
	}

	public static function get_session_code(){
		//Clean unused codes (older than 24H)
		$limit = ModelBruno::getMStime() - (24*3600*1000); //Cut 24H
		//For session where user_id exists, it means it's a fix session (no time limit!)
		Session::WhereNotNull('code')->where('u_at', '<', $limit)->whereNull('question_hashid')->getQuery()->update(['code' => null]);

		//Get a unique code number
		$length = 4;
		$tries = 5000;
		$code = null;
		$loop = true;
		while($loop){
			$loop = false;
			$code = rand( pow(10, $length-1), pow(10, $length)-1 ); //for length 4, min is 1000, max is 9999
			//Find a unique md5
			if(Session::Where('code', $code)->first(array('id'))){
				$loop = true;
				$tries--;
				if($tries<=0){
					$tries = 5000;
					$length = $length + 2; //It's easier for user to insert a even number length, 4 6 8
				}
				if($length>8){
					$code = null;
					$loop = false;
					break;
				}
			}
		}
		return $code;
	}

}
