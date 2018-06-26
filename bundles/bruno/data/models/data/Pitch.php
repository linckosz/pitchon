<?php

namespace bundles\bruno\data\models\data;

use \libs\Json;
use \libs\STR;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\Question;

class Pitch extends ModelBruno {

	protected $connection = 'data';

	protected $table = 'pitch';
	protected $morphClass = 'pitch';

	protected $primaryKey = 'id';

	protected static $pivot_include = true;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'u_at',
		'c_by',
		'updated_json',
		'title',
		'file_id',
		'brand',
		'brand_pic',
		'ad',
		'ad_pic',
		'sort',
		'_user',
	);

	protected $model_integer = array(
		'file_id',
		'brand_pic',
		'ad_pic',
		'sort',
	);

////////////////////////////////////////////

	//Many(Pitch) to Many(User)
	public function user(){
		return $this->belongsToMany('\\bundles\\bruno\\data\\models\\data\\User', 'user_x_pitch', 'pitch_id', 'user_id')->withPivot('access');
	}

	//One(Pitch) to Many(Question)
	public function question(){
		return $this->hasMany('\\bundles\\bruno\\data\\models\\data\\Question', 'parent_id'); //parent_id => pitch_id
	}

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
			if(!$md5){
				$md5 = md5(uniqid('', true));
			}
			$model = new self;
			$model->md5 = $md5;
			//Give access to the user itself
			$pivot = new \stdClass;
			$pivot->{'user>access'} = new \stdClass;
			$pivot->{'user>access'}->{$app->bruno->data['user_id']} = true;
			$model->pivots_format($pivot);
		}

		if(isset($form->title)){
			$error = true;
			if(is_string($form->title)){
				$error = false;
				$model->title = STR::break_line_conv($form->title, '');
			}
			if($error){
				$errfield = 'title';
				goto failed;
			}
		}

		if(isset($form->file_id)){
			$error = true;
			if(is_object($form->file_id)){
				$form->file_id = (array) $form->file_id;
			}
			//Only valid the detachment
			if(empty($form->file_id)){
				$error = false;
				$model->file_id = null;
			}
			if($error){
				$errfield = 'file_id';
				goto failed;
			}
		}

		if(isset($form->brand)){
			$error = true;
			if(is_string($form->brand)){
				$error = false;
				$model->brand = STR::break_line_conv($form->brand, '');
			}
			if($error){
				$errfield = 'brand';
				goto failed;
			}
		}

		if(isset($form->brand_pic)){
			$error = true;
			if(is_object($form->brand_pic)){
				$form->brand_pic = (array) $form->brand_pic;
			}
			//Only valid the detachment
			if(empty($form->brand_pic)){
				$error = false;
				$model->brand_pic = null;
			}
			if($error){
				$errfield = 'brand_pic';
				goto failed;
			}
		}

		if(isset($form->ad)){
			$error = true;
			if(is_string($form->ad)){
				$error = false;
				$model->ad = STR::break_line_conv($form->ad, '');
			}
			if($error){
				$errfield = 'ad';
				goto failed;
			}
		}

		if(isset($form->ad_pic)){
			$error = true;
			if(is_object($form->ad_pic)){
				$form->ad_pic = (array) $form->ad_pic;
			}
			//Only valid the detachment
			if(empty($form->ad_pic)){
				$error = false;
				$model->ad_pic = null;
			}
			if($error){
				$errfield = 'ad_pic';
				goto failed;
			}
		}

		if(isset($form->sort)){
			$error = true;
			if(is_numeric($form->sort)){
				$error = false;
				$model->sort = (int) $form->sort;
			}
			if($error){
				$errfield = 'sort';
				goto failed;
			}
		}

		return $model;

		failed:
		if($new){
			$errmsg = $app->trans->getBRUT('api', 9, 1)."\n"; //Pitch creation failed.
		} else {
			$errmsg = $app->trans->getBRUT('api', 9, 5)."\n"; //Pitch update failed.
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

	public function toJson($detail=true, $options = 256){ //256: JSON_UNESCAPED_UNICODE
		$users = $this->user;
		$this->_user = new \stdClass;
		foreach ($users as $user) {
			$this->_user->{$user->id} = new \stdClass;
			$this->_user->{$user->id}->id = $user->id;
			$this->_user->{$user->id}->username = $user->username;
			$this->_user->{$user->id}->email = $user->email;
			$this->_user->{$user->id}->lock = false;
			//Lock the creator for being exclude of the picth
			if($this->c_by == $user->id){
				$this->_user->{$user->id}->lock = true;
			}
		}
		//\libs\Watch::php($user, '$user', __FILE__, __LINE__, false, false, true);
		//$this->_user = ;
		$temp = parent::toJson($detail, $options);
		return $temp;
	}

	public function scopegetItems($query, &$list=array(), $get=false){
		if(!isset($list['user'])){ $list['user']=array(); }
		$query = $query
		->whereHas('user', function($query) use ($list) {
			$query
			->whereIn('user_x_pitch.user_id', $list['user'])
			->where('user_x_pitch.access', 1);
		});
		if($get){
			return $query->get();
		} else {
			return $query;
		}
	}

////////////////////////////////////////////

	//Get all questions in correct order
	public function questions($arr=array('*')){
		return $this->question()->orderBy('sort', 'DESC')->orderBy('id', 'ASC')->get($arr);
	}

	//Get first question
	public function question_first($arr=array('*')){
		return $this->question()->orderBy('sort', 'DESC')->orderBy('id', 'ASC')->first($arr);
	}

	//Get last question
	public function question_last($arr=array('*')){
		return $this->question()->orderBy('sort', 'ASC')->orderBy('id', 'DESC')->first($arr);
	}

	//Get offset question
	public function question_offset($offset, $arr=array('*')){
		return $this->question()->orderBy('sort', 'DESC')->orderBy('id', 'ASC')->offset($offset)->first($arr);
	}

}
