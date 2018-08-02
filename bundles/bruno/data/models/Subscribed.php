<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;

class Subscribed extends Model {

	protected $connection = 'data';

	protected $table = 'subscribed';
	protected $morphClass = 'subscribed';

	protected $primaryKey = 'id';

	public $timestamps = false;

	protected $visible = array();

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
			return parent::save($options);
		}
		return false; //Disable update
	}

}
