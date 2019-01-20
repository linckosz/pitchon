<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;

class Paypal extends Model {

	protected $connection = 'data';

	protected $table = 'paypal';
	protected $morphClass = 'paypal';

	protected $primaryKey = 'id';

	public $timestamps = false;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'event',
	);

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

////////////////////////////////////////////

	public function save(array $options = array()){
		//We only allow creation
		if(!isset($this->id)){
			$this->c_at = ModelBruno::getMStime();
			$this->md5 = md5(uniqid('', true));
			return parent::save($options);
		}
		return false;
	}

}
