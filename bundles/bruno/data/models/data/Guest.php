<?php

namespace bundles\bruno\data\models\data;

use \libs\Json;
use \bundles\bruno\data\models\ModelBruno;

class Guest extends ModelBruno {

	protected $connection = 'data';

	protected $table = 'guest';
	protected $morphClass = 'guest';

	protected $primaryKey = 'id';

	protected static $pivot_include = true;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'u_at',
		'updated_json',
		'username',
	);

	protected static $me = false;

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

	public function scopegetItems($query, &$list=array(), $get=false){
		$app = ModelBruno::getApp();
		$query = $query->where('id', $app->bruno->data['guest_id']);
		if($get){
			return $query->get();
		} else {
			return $query;
		}
	}

////////////////////////////////////////////

	public static function getUser($force=false){
		$app = ModelBruno::getApp();
		if($force){
			static::$me = false;
		}
		if(!static::$me && $app->bruno->data['guest_id']){
			if($me = self::where('id', $app->bruno->data['guest_id'])->first()){
				static::$me = $me;
			}
		} 
		return static::$me;
	}

	public function setLanguage($save=true){
		$app = ModelBruno::getApp();
		$language = $app->trans->getClientLanguage();
		if(!empty($language) && $language!=$this->language){
			$this->language = strtolower($language);
			if($save){
				$this->save();
			}
		}
	}

}
