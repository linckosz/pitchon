<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;

class Subscription extends Model {

	protected $connection = 'data';

	protected $table = 'subscription';
	protected $morphClass = 'subscription';

	protected $primaryKey = 'id';

	public $timestamps = false;

	protected $visible = array();

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }
	public function save(array $options = array()){ return false; }

////////////////////////////////////////////
	
	public static function getLatest(){
		return Subscription::orderBy('id', 'DESC')->first();
	}

}
