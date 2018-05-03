<?php

namespace bundles\bruno\data\models\data;

use \libs\Json;
use \libs\STR;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\Question;

class Answer extends ModelBruno {

	protected $connection = 'data';

	protected $table = 'answer';
	protected $morphClass = 'answer';

	protected $primaryKey = 'id';

	protected static $pivot_include = true;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'u_at',
		'updated_json',
		'title',
		'number',
		'parent_type',
		'parent_id',
		'file_id',
	);

	public $parent_type = 'question';

	protected $model_integer = array(
		'number',
	);

////////////////////////////////////////////

	//Many(Answer) to One(Question)
	public function question(){
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Question', 'parent_id'); //parent_id => question_id
	}

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

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
		}
		
		if($new){
			$error = true;
			if(isset($form->parent_id) && isset($form->parent_md5)){
				if(is_numeric($form->parent_id) && is_string($form->parent_md5)){
					if(Question::Where('id', $form->parent_id)->where('md5', $form->parent_md5)->first()){
						$error = false;
						$model->parent_id = (int) $form->parent_id;
					}
				}
			}
			if($error){
				$errfield = 'parent_id';
				goto failed;
			}
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

		if($new){
			//Check if number if between 1 and 6, and it can only be one per question
			if(isset($form->number)){
				$error = true;
				if(is_integer($form->number) && in_array($form->number, [1, 2, 3, 4, 5, 6])){
					if(!Answer::Where('parent_id', $model->parent_id)->where('number', $form->number)->first()){
						$error = false;
						$model->number = (int) $form->number;
					}
				}
				if($error){
					$errfield = 'number';
					goto failed;
				}
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

		return $model;

		failed:
		if($new){
			$errmsg = $app->trans->getBRUT('api', 13, 1)."\n"; //Answer creation failed.
		} else {
			$errmsg = $app->trans->getBRUT('api', 13, 5)."\n"; //Answer update failed.
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

	public function scopegetItems($query, &$list=array(), $get=false){
		if(!isset($list['question'])){ $list['question']=array(); }
		$query = $query
		->WhereHas('question', function($query) use ($list) {
			$query->whereIn('answer.parent_id', $list['question']);
		});
		if($get){
			return $query->get();
		} else {
			return $query;
		}
	}

////////////////////////////////////////////

	public function letter(){
		return ModelBruno::numToAplha($this->number);
	}

}
