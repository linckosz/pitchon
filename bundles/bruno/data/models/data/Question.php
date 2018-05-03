<?php

namespace bundles\bruno\data\models\data;

use \libs\Json;
use \libs\STR;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\Answer;
use \bundles\bruno\data\models\data\Pitch;
use Illuminate\Database\Capsule\Manager as Capsule;

class Question extends ModelBruno {

	protected $connection = 'data';

	protected $table = 'question';
	protected $morphClass = 'question';

	protected $primaryKey = 'id';

	protected static $pivot_include = true;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'u_at',
		'updated_json',
		'title',
		'style',
		'parent_type',
		'parent_id',
		'number',
		'file_id',
		'sort',
	);

	public $parent_type = 'pitch';

	protected $model_integer = array(
		'parent_id',
		'number',
		'file_id',
		'sort',
		'style',
	);

////////////////////////////////////////////

	//Many(Question) to One(Pitch)
	public function pitch(){
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Pitch', 'parent_id'); //parent_id => pitch_id
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
		}

		if($new){
			$error = true;
			if(isset($form->parent_id) && isset($form->parent_md5)){
				if(is_numeric($form->parent_id) && is_string($form->parent_md5)){
					if(Pitch::Where('id', $form->parent_id)->where('md5', $form->parent_md5)->first()){
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

		//Check if number if between 1 and 6, and it can only be one per question
		if(isset($form->number)){
			$error = true;
			if(is_integer($form->number) && in_array($form->number, [1, 2, 3, 4, 5, 6])){
				$error = false;
				$model->number = (int) $form->number;
			}
			if($error){
				$errfield = 'number';
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

		if(isset($form->style)){
			$error = true;
			if(is_numeric($form->style)){
				$style = (int) $form->style;
				if(in_array($style, array(1, 2, 3, 4))){
					$error = false;
					$model->style = $style;
				}
			}
			if($error){
				$errfield = 'style';
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
			$errmsg = $app->trans->getBRUT('api', 10, 1)."\n"; //Question creation failed.
		} else {
			$errmsg = $app->trans->getBRUT('api', 10, 5)."\n"; //Question update failed.
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
		if(!isset($list['pitch'])){ $list['pitch']=array(); }
		$query = $query
		->whereHas('pitch', function($query) use ($list) {
			$query->whereIn('question.parent_id', $list['pitch']);
		});
		if($get){
			return $query->get();
		} else {
			return $query;
		}
	}

////////////////////////////////////////////

	public function save(array $options = array()){
		if(!isset($this->id)){ //Only for new, we add 4 answers
			$app = ModelBruno::getApp();
			//Start transaction because we must create 4 answers along with the question
			$db = Capsule::connection('data');
			$db->beginTransaction();
			$committed = false;
			try {
				$result = parent::save($options);
				if(!$result){
					throw new \Exception;
				}

				$answer_1 = new Answer;
				$answer_1->number = 1;
				$answer_1->parent_id = $this->id;
				$answer_1->title = $app->trans->getBRUT('api', 0, 12); //Yes

				$answer_2 = new Answer;
				$answer_2->number = 2;
				$answer_2->parent_id = $this->id;
				$answer_2->title = $app->trans->getBRUT('api', 0, 13); //No

				$result = $answer_1->save() && $answer_2->save();

				if(!$result){
					throw new \Exception;
				}

				$answer_1->forceRead();
				$answer_2->forceRead();

				$this->number = 1;
				$result = parent::save($options);
				if(!$result){
					throw new \Exception;
				}

				$db->commit();
				$committed = true;
			} catch (\Exception $e){
				$committed = false;
				$db->rollback();
				$errmsg = $app->trans->getBRUT('api', 10, 1)."\n"; //Question creation failed.
				$errmsg .= $app->trans->getBRUT('api', 0, 7); //Please try again.
				\libs\Watch::php($errmsg, 'failed', __FILE__, __LINE__, true);
				$msg = array('msg' => $errmsg);
				(new Json($msg, true, 401, true))->render();
				return exit(0);
			}
			return $committed;
		} else {
			return parent::save($options);
		}
	}

}
