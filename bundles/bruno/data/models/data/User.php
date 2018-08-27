<?php

namespace bundles\bruno\data\models\data;

use \libs\Json;
use \libs\STR;
use \libs\Vanquish;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Bank;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\Answer;
use \bundles\bruno\data\models\data\File;
use Illuminate\Database\Capsule\Manager as Capsule;

class User extends ModelBruno {

	const DELETED_AT = 'd_at'; //overwrite deleted_at

	protected $connection = 'data';

	protected $table = 'user';
	protected $morphClass = 'user';

	protected $primaryKey = 'id';

	protected static $pivot_include = true;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'u_at',
		'updated_json',
		'email',
		'username',
		'tuto',
		'plan',
		'plan_duration',
		'plan_at',
		'_bank',
		'_host',
	);

	protected $model_integer = array(
		'plan',
	);

	protected static $me = false;

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	public static function setItem($form){
		$app = ModelBruno::getApp();

		$model = false;
		$errfield = 'undefined';
		$error = false;
		$new = true;

		//Convert to object
		$form = json_decode(json_encode($form, JSON_FORCE_OBJECT));
		foreach ($form as $key => $value) {
			if(!is_numeric($value) && empty($value)){ //Exclude 0 to become an empty string
				$form->$key = '';
			}
		}

		$md5 = false;
		if(isset($form->md5) && is_string($form->md5) && strlen($form->md5)==32){
			$md5 = $form->md5;
		}
		if(isset($form->id)){
			$new = false;
			$error = true;
			if($md5 && is_numeric($form->id)){
				$id = (int) $form->id;
				if($model = static::find($id)){
					if($model->md5 == $md5){
						$error = false;
					}
				}
			}
			if($error){
				$errfield = 'id';
				goto failed;
			}
		} else {
			goto failed;
		}

		if(isset($form->username)){
			$error = true;
			if(is_string($form->username)){
				$error = false;
				$model->username = STR::break_line_conv($form->username, '');
			}
			if($error){
				$errfield = 'username';
				goto failed;
			}
		}

		if(isset($form->tuto)){
			$error = true;
			if(is_object($form->tuto)){
				$form->tuto = json_encode($form->tuto);
			}
			if(is_string($form->tuto) && json_decode($form->tuto)){
				$error = false;
				$model->tuto = $form->tuto;
			}
			if($error){
				$errfield = 'tuto';
				goto failed;
			}
		}

		return $model;

		failed:
		if($new){
			$errmsg = $app->trans->getBRUT('api', 12, 1)."\n"; //Account creation failed.
		} else {
			$errmsg = $app->trans->getBRUT('api', 12, 5)."\n"; //Account update failed.
		}
		$errmsg .= $app->trans->getBRUT('api', 1, 0); //We could not validate the format.
		\libs\Watch::php(array($errmsg, $form), 'failed', __FILE__, __LINE__, true);
		$msg = array('msg' => $errmsg, 'field' => $errfield);

		if(!$new){
			//Return the original element for overwriting on front
			$nosql = $model->getNoSQL();
			if($nosql && isset($nosql->$errfield)){
				$result = new \stdClass;
				$result->reset = new \stdClass;
				$result->reset->{$model->getTable()} = new \stdClass;
				$result->reset->{$model->getTable()}->{$model->id} = new \stdClass;
				$result->reset->{$model->getTable()}->{$model->id}->$errfield = $nosql->$errfield;
				$msg['data'] = $result;
			}
		}
		
		(new Json($msg, true, 401, true))->render();
		return exit(0);
	}

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

	public function scopegetItems($query, &$list=array(), $get=false){
		$app = ModelBruno::getApp();
		$query = $query->where('id', $app->bruno->data['user_id']);
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
		if(!static::$me && $app->bruno->data['user_id']){
			if($me = self::where('id', $app->bruno->data['user_id'])->first()){
				static::$me = $me;
			}
		} 
		return static::$me;
	}

	public static function findUser($md5, $user_id){
		$app = ModelBruno::getApp();
		if(is_string($md5) && is_numeric($user_id)){
			return self::where('md5', $md5)->where('id', $user_id)->first();
		} 
		return false;
	}

	public static function isAdmin(){
		if(User::getUser()->admin){
			return true;
		}
		return false;
	}

	public static function refreshAll(){
		if(User::isAdmin()){
			User::withTrashed()->getQuery()->update(['refresh' => ModelBruno::getMStime()]);
			return true;
		}
		return false;
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

	public static function getHosts(){
		$app = ModelBruno::getApp();
		return User::Where('host_id', $app->bruno->data['user_id'])->count();
	}

	public function toJson($detail=true, $options = 256){ //256: JSON_UNESCAPED_UNICODE
		$this->_bank = Bank::getRecords(); //Get the whole list of subscriptions attached
		$this->_host = User::getHosts(); //Get the number of attached hosts that created an account
		$temp = parent::toJson($detail, $options);
		return $temp;
	}

	public function save(array $options = array()){
		$new = false;
		if(!isset($this->id)){
			$this->setLanguage(false);
			// Attach a new user to the latest pitch host.
			// If this user upgrate to a paid account, a % of the 1st order will be given to the host
			// It only works at the first payment, not renewal
			// If later this user upgrate to a paid account with a promotional code, the host_id will be changed to the person owning the promotional code (our sales)
			if($host_id = Vanquish::get('host_id')){
				$this->host_id = $host_id;
			}
			$this->plan = 2; //Open Standard mode for trial
			$this->plan_at = ModelBruno::getMStime() + (30*24*3600*100); //30 days free trial
			//Set tuto list
			$this->tuto = json_encode((object) array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1));
			$new = true;
		}
		$result = parent::save($options);
		if($new){
			$app = ModelBruno::getApp();
			$app->bruno->data['user_id'] = $this->id;
			//Prepare onboarding
			$this->onboarding();
		}
		return $result;
	}

	public function onboarding(){
		$app = ModelBruno::getApp();
		$db = Capsule::connection('data');
		$db->beginTransaction();
		$committed = false;
		try {
			$language = $app->trans->getClientLanguage();

			$pitch = new Pitch;
			$pitch->title = $app->trans->getBRUT('api', 4, 0); //My Pitch
			$pivot = new \stdClass;
			$pivot->{'user>access'} = new \stdClass;
			$pivot->{'user>access'}->{$this->id} = true;
			$pitch->pivots_format($pivot);
			$pitch->save();

			$question = new Question;
			$question->style = 1;
			$question->parent_id = $pitch->id;
			$question->title = $app->trans->getBRUT('api', 4, 1); //The Dutch windmill is mainly used for:
			$question->save();
			$file = File::find(10000)->replicate();
			$file->updated_json = null;
			$file->nosql = null;
			$file->parent_type = $question->getTable();
			$file->parent_id = $question->id;
			$file->saveParent();
			$question->file_id = $file->id;
			$question->save();
			//Additional answers
			$answer = new Answer;
			$answer->number = 3;
			$answer->parent_id = $question->id;
			$answer->save();
			//Edit all answers
			$answers = $question->answer;
			foreach ($answers as $answer) {
				if($answer->number==1){
					$answer->title = $app->trans->getBRUT('api', 4, 2); //Electricity generation
				} else if($answer->number==2){ //correct
					$answer->title = $app->trans->getBRUT('api', 4, 3); //Water drainage
					$question->number = $answer->number;
					$question->save();
				} else if($answer->number==3){
					$answer->title = $app->trans->getBRUT('api', 4, 4); //Tourism
				}
				$answer->save();
			}

			$question = new Question;
			$question->style = 2; //Pictures
			$question->parent_id = $pitch->id;
			$question->title = $app->trans->getBRUT('api', 4, 10); //(Fan Bingbing question in chinese)Which supercar is made in Lebanon?
			$question->save();
			//Additional answers
			$answer = new Answer;
			$answer->number = 3;
			$answer->parent_id = $question->id;
			$answer->save();
			$answer = new Answer;
			$answer->number = 4;
			$answer->parent_id = $question->id;
			$answer->save();
			//Edit all answers
			$answers = $question->answer;
			foreach ($answers as $answer) {
				if($answer->number==1){
					$file_id = 10005;
					if($language=='zh-chs' || $language=='zh-cht'){ $file_id = 10001; }
				} else if($answer->number==2){ //correct
					$file_id = 10006;
					if($language=='zh-chs' || $language=='zh-cht'){ $file_id = 10002; }
					$question->number = $answer->number;
					$question->save();
				} else if($answer->number==3){
					$file_id = 10007;
					if($language=='zh-chs' || $language=='zh-cht'){ $file_id = 10003; }
				} else if($answer->number==4){
					$file_id = 10008;
					if($language=='zh-chs' || $language=='zh-cht'){ $file_id = 10004; }
				}
				$file = File::find($file_id)->replicate();
				$file->updated_json = null;
				$file->nosql = null;
				$file->parent_type = $answer->getTable();
				$file->parent_id = $answer->id;
				$file->saveParent();
				$answer->file_id = $file->id;
				$answer->title = '';
				$answer->save();
			}

			$question = new Question;
			$question->style = 1;
			$question->parent_id = $pitch->id;
			$question->title = $app->trans->getBRUT('api', 4, 5); //(Mosquito question in chinese) According to a well-known piece of advice, "If you want something done right, you have to" what?
			$question->save();
			//Additional answers
			$answer = new Answer;
			$answer->number = 3;
			$answer->parent_id = $question->id;
			$answer->save();
			$answer = new Answer;
			$answer->number = 4;
			$answer->parent_id = $question->id;
			$answer->save();
			//Edit all answers
			$answers = $question->answer;
			foreach ($answers as $answer) {
				if($answer->number==1){
					$answer->title = $app->trans->getBRUT('api', 4, 6); //Use a calculator
				} else if($answer->number==2){
					$answer->title = $app->trans->getBRUT('api', 4, 7); //Call MacGyver
				} else if($answer->number==3){ //correct
					$answer->title = $app->trans->getBRUT('api', 4, 8); //Do it yourself
					$question->number = $answer->number;
					$question->save();
				} else if($answer->number==4){
					$answer->title = $app->trans->getBRUT('api', 4, 9); //Double-check everything
				}
				$answer->save();
			}

			$db->commit();
			$committed = true;
		} catch (\Exception $e){
			$committed = false;
			$db->rollback();
		}
		return $committed;
	}

}
