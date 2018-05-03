<?php
// Category 11

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\Answer;
use \bundles\bruno\data\models\data\File;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\Pitch;
use \bundles\bruno\data\models\data\User;

class ControllerData extends Controller {

	protected static $priority_tables = array(
		'user',
		'pitch',
		'question',
		'answer',
		'file',
	);

	public function latest_post(){
		$data = ModelBruno::getData();
		$result = new \stdClass;
		$result->read = new \stdClass;
		$lastvisit_ms = false;
		
		//Note: There is a very low probability that one object has been created exactly at the same milliseconds, but should never happen with the same user.
		if(isset($data->lastvisit_ms) && is_integer($data->lastvisit_ms)){
			$lastvisit_ms = (int) $data->lastvisit_ms;
		}

		$refresh = User::getUser()->refresh;
		if($refresh>$lastvisit_ms){
			$lastvisit_ms = false;
		} else {
			$refresh = false;
		}

		$list = array();
		foreach (self::$priority_tables as $table) {
			$result->read->$table = new \stdClass;
			$list[$table] = array();
			$class = ModelBruno::getClass($table);
			if($class){
				if($items = $class::getItems($list)->get(array('id', 'md5', 'nosql'))){
					foreach ($items as $item) {
						$list[$table][] = $item->id;
						$result->read->$table->{$item->id} = $item->getNoSQL();
					}
				}
			}
		}

		if($lastvisit_ms && isset($result->read)){
			foreach ($result->read as $table => $items) {
				foreach ($items as $id => $item) {
					if(isset($item->u_at) && $item->u_at <= $lastvisit_ms){
						unset($result->read->$table->$id);
					}
				}
			}
		}

		self::additional_data($result);

		$msg = array();
		$msg['data'] = $result;
		if(!$lastvisit_ms){
			$msg['info'] = 'all';
		}
		if($refresh){
			$msg['refresh'] = true;
		}
		(new Json($msg))->render();
		return exit(0);
	}

	public function set_post(){
		$data = ModelBruno::getData();
		$result = new \stdClass;
		
		if(is_object($data)){

			//Read
			if(isset($data->read) && is_object($data->read)){
				foreach ($data->read as $table => $list) {
					$class = ModelBruno::getClass($table);
					if($class){
						foreach ($list as $item) {
							if(isset($item->id) && isset($item->md5) && is_string($item->md5) && strlen($item->md5)==32){
								if($model = $class::where('id', $item->id)->where('md5', $item->md5)->first(array('id', 'md5', 'nosql'))){
									if(!isset($result->read)){ $result->read = new \stdClass; }
									if(!isset($result->read->$table)){ $result->read->$table = new \stdClass; }
									$result->read->$table->{$item->id} = $model->getNoSQL();
								} else {
									if(!isset($result->delete)){ $result->delete = new \stdClass; }
									if(!isset($result->delete->$table)){ $result->delete->$table = new \stdClass; }
									$result->delete->$table->{$item->id} = true;
								}
							}
						}
					}
				}
			}

			//Insert or Update
			if(isset($data->set) && is_object($data->set)){
				if(!isset($result->read)){ $result->read = new \stdClass; }
				foreach ($data->set as $table => $list) {
					$class = ModelBruno::getClass($table);
					if($class){
						if(!isset($result->read->$table)){ $result->read->$table = new \stdClass; }
						foreach ($list as $item) {
							$model = $class::setItem($item);
							if(is_object($model)){
								$dirty = $model->getDirty();
								$pivots = $model->pivots_format($item);
								if(count($dirty)>0 || $pivots){
									if($model->save()){
										$result->read->$table->{$model->id} = $model->getNoSQL();
									}
								}
							}
						}
					}
				}
			}

			//Restore
			if(isset($data->restore) && is_object($data->restore)){
				if(!isset($result->read)){ $result->read = new \stdClass; }
				foreach ($data->restore as $table => $list) {
					$class = ModelBruno::getClass($table);
					if($class){
						if(!isset($result->read->$table)){ $result->read->$table = new \stdClass; }
						foreach ($list as $item) {
							if(isset($item->id) && isset($item->md5) && is_string($item->md5) && strlen($item->md5)==32){
								if($model = $class::withTrashed()->where('id', $item->id)->where('md5', $item->md5)->first()){
									if($model->restore()){
										$result->read->$table->{$model->id} = $model->getNoSQL();
									}
								}
							}
						}
					}
				}
			}

			//Delete
			if(isset($data->delete) && is_object($data->delete)){
				if(!isset($result->delete)){ $result->delete = new \stdClass; }
				foreach ($data->delete as $table => $list) {
					$class = ModelBruno::getClass($table);
					if($class){
						if(!isset($result->delete->$table)){ $result->delete->$table = new \stdClass; }
						foreach ($list as $item) {
							if(isset($item->id) && isset($item->md5) && is_string($item->md5) && strlen($item->md5)==32){
								if($model = $class::Where('id', $item->id)->where('md5', $item->md5)->first()){
									$id = $model->id;
									if($model->delete()){
										$result->delete->$table->$id = true;
									}
								}
							}
						}
					}
				}
			}

		}

		self::additional_data($result);

		$msg = array('data' => $result);
		(new Json($msg))->render();
		return exit(0);

	}

	protected function additional_data(&$result){
		$force_read = ModelBruno::getForceRead();
		foreach ($force_read as $table => $list) {
			$class = ModelBruno::getClass($table);
			if($class){
				$list_read = array();
				foreach ($list as $id => $read) {
					if(
						   ( isset($result->read->$table) && isset($result->read->$table->$id) )
						|| ( isset($result->delete->$table) && isset($result->delete->$table) && isset($result->delete->$table->$id) )
					){
						continue;
					}
					if($read){
						$list_read[$id] = $id;
					} else {
						if(!isset($result->delete)){ $result->delete = new \stdClass; }
						if(!isset($result->delete->$table)){ $result->delete->$table = new \stdClass; }
						$result->delete->$table->$id = true;
					}
				}
				if(count($list_read)>0){
					if($items = $class::WhereIn('id', $list_read)->get(array('id', 'md5', 'nosql'))){
						if(!isset($result->read->$table)){ $result->read->$table = new \stdClass; }
						foreach ($items as $item) {
							$result->read->$table->{$item->id} = $item->getNoSQL();
						}
					}
				}
			}
		}

		if(isset($result->read)){
			foreach ($result->read as $table => $items) {
				if(empty((array)$result->read->$table)){
					unset($result->read->$table);
				}
			}
		}

		if(isset($result->delete)){
			foreach ($result->delete as $table => $items) {
				if(empty((array)$result->delete->$table)){
					unset($result->delete->$table);
				}
			}
		}
	}

}
