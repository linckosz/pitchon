<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Subscribed;

class Promocode extends Model {

	protected $connection = 'data';

	protected $table = 'promocode';
	protected $morphClass = 'promocode';

	protected $primaryKey = 'id';

	public $timestamps = false;

	protected $visible = array();

////////////////////////////////////////////

//No need relation

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }
	public function save(array $options = array()){ return false; }

////////////////////////////////////////////
	
	public static function getCurrent(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		$promocode = '';
		if(isset($data->promocode) && is_string($data->promocode) ){
			$promocode = $data->promocode;
			$_SESSION['promocode'] = $promocode;
		} else if(isset($_SESSION['promocode']) && $_SESSION['promocode']){
			$promocode = $_SESSION['promocode'];
		}

		$result = array('', 0);
		if($item = self::getItem($promocode)){
			$result = array($item->title, $item->discount);
		}
		
		return $result;
	}

	public static function getItem($title){
		$app = ModelBruno::getApp();
		if(empty($title)){
			return false;
		}
		$used = Subscribed::Where('user_id', $app->bruno->data['user_id'])->where('promocode', $title)->first(array('id'));
		if($used){
			return -1; //-1 = code already usedd
		}

		return Promocode::Where('title', $title)->first();
	}

}
