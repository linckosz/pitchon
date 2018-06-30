<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\User;

class Bank extends Model {

	protected $connection = 'data';

	protected $table = 'bank';
	protected $morphClass = 'bank';

	protected $primaryKey = 'id';

	public $timestamps = false;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'used_at',
		'eur',
	);

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

////////////////////////////////////////////

	public function save(array $options = array()){
		if(!isset($this->id)){
			$this->c_at = ModelBruno::getMStime();
			$this->md5 = md5(uniqid('', true));
			return parent::save($options);
		} else { //Only if we never used it before
			$dirty = (array) $this->getDirty();
			//We only allow creation and modification of ad_clicks
			if(isset($dirty['used_at']) && count($dirty) == 1){
				//Make sure that used_at was at null before
				$original = (array) $this->getOriginal();
				if(isset($original['used_at']) && $original['used_at'] == null){
					//Make sure used_at is set as now
					$this->used_at = ModelBruno::getMStime();
					return parent::save($options);
				}
			}
		}
		return false;
	}

	
	public static function subscription(float $amount_eur, $subscription_rate = 0.5){
		$app = ModelBruno::getApp();
		//The user get 50% of the "one time" subscription amount, the rate must be between 0% and 60%.
		if(!is_float($subscription_rate) || $subscription_rate > 0.6 || $subscription_rate < 0){
			return false;
		}
		$amount_eur = floor($subscription_rate * $amount_eur);
		if($amount_eur<=0){
			return false;
		}
		//We the host_id is available, we attach the first time of any subscription
		$user = User::getUser();
		if($user->host_id){
			$bank = Bank::Where('host_user_id', $user->host_id)->where('guest_user_id', $user->id)->first();
			//Do nothing if it already exists
			if(!$bank){
				$bank = new Bank;
				$bank->host_user_id = $user->host_id;
				$bank->guest_user_id = $user->id;
				$bank->eur = $amount_eur;
				if($bank->save()){
					return true;
				}
			}
		}
		return false;
	}

	public static function getRecords(){
		$app = ModelBruno::getApp();
		return Bank::Where('host_user_id', $app->bruno->data['user_id'])->where('eur', '>', 0)->get();
	}

}
