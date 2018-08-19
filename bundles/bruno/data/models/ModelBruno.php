<?php

namespace bundles\bruno\data\models;

use \Exception;
use \libs\Json;
use \libs\STR;
use \bundles\bruno\data\models\ModelBruno;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder as Schema;


abstract class ModelBruno extends Model {

	protected static $app = null;

	use SoftDeletes;
	const DELETED_AT = 'd_at'; //overwrite deleted_at

	protected $with_trash = false;
	protected static $with_trash_global = false;

	protected $guarded = array('*');

	//We don't use anymore created_at / updated_at / deleted_at, but miliseconds timestamp with c_at / u_at / d_at
	public $timestamps = false;

////////////////////////////////////////////

	//Keep a record of the data sent
	protected static $data = null;

	protected static $schema_table = array();
	protected static $schema_default = array();

	//CRUD restriction per model class
	// 1:CRUD / 2: CRU / 3:CR
	protected $crud = 1;

	//Force to save user access for the user itself
	protected static $save_user_access = true;

	//Only calculate the pivot relation for few models
	protected static $pivot_include = false;

	//It force to save, even if dirty is empty
	protected $force_save = false;

	//If at true, we skip some operation, but CAUTION we need to make sure they are run later because it can generate permission access issues
	protected static $save_skipper = false;

	//Fix once the time in milliseconds for the whole process
	protected static $ms = false;

	//When call toJson, convert fields to timestamp format if the field exists only
	protected static $class_timestamp = array();
	protected $model_timestamp = array();

	//When call toJson, convert fields to integer format if the field exists only
	protected static $class_integer = array(
		'c_by',
		'u_by',
		'd_by',
		'c_at',
		'u_at',
		'd_at',
	);
	protected $model_integer = array();

	//When call toJson, convert fields to boolean format if the field exists only
	protected static $class_boolean = array(
		'access',
	);
	protected $model_boolean = array();

	//Tell which parent role to check if the model doesn't have one, for example Tasks will check Projects if Tasks doesn't have role permission.
	protected static $parent_list = null;

	//Is true when we are saving a new model
	protected $new_model = false;

	//turn it on for debug purpose only
	protected static $debugMode = false;

	//From pivot table, at true it accepts only access at 1, at false it rejects access at 0 but include parent at access at 1
	protected static $access_accept = true;

	//Vraiable used to pass some values through scopes
	protected $var = array();

	protected static $columns = array();

	//Note: In relation functions, cannot not use underscore "_", something like "tasks_users()" will not work.

	//List of relations we want to make available on client side
	protected static $dependencies_visible = array();

	//Pivot to update
	protected $pivots_var = null;

	//Force to receive some items (make sure the user has access to them of course)
	protected static $force_read = array();

	public function __construct(array $attributes = array()){
		parent::__construct($attributes);
		//$db = Capsule::connection($this->connection);
		//$db->enableQueryLog();
	}

////////////////////////////////////////////
	//VALIDATION METHODS

	//The value has to be previously converted (int)boolval(var) because of MySQL => 0|1
	public static function validBoolean($data){
		$return = is_numeric($data) && ($data==0 || $data==1);
		return $return;
	}

	//useless
	public static function validNumeric($data){
		$return = is_numeric($data);
		return $return;
	}

	public static function validRCUD($data){
		$return = is_numeric($data) && $data>=0 && $data<=3;
		return $return;
	}

	public static function validProgress($data){
		$return = is_numeric($data) && $data>=0 && $data<=100;
		return $return;
	}

	public static function validDate($data){
		$return = preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/u", $data);
		return $return;
	}

	public static function validType($data){
		if(is_null($data)){ //It can be at root level
			return true;
		} else {
			$parent_list = static::$parent_list;
			if(!is_array($parent_list) && is_string($parent_list)){
				$parent_list = array($parent_list);
			}
			$return = is_string($data) && !empty($parent_list) && in_array($data, $parent_list) && preg_match("/^[a-z]{0,104}$/u", $data);
		}
		return $return;
	}

	public static function validChar($data){
		$return = is_string($data) && strlen(trim($data))>=0 && preg_match("/^.{0,104}$/u", $data);
		return $return;
	}

	public static function validTitle($data){
		$return = is_string($data) && strlen(trim($data))>=0 && preg_match("/^.{0,200}$/u", $data);
		return $return;
	}

	public static function validText($data){
		$return = is_string($data);
		return $return;
	}

	//191 is limited by MySQL for Indexing
	public static function validDomain($data){
		$return = is_string($data) && preg_match("/^.{1,191}$/u", trim($data)) && preg_match("/^[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/ui", trim($data));
		return $return;
	}

	public static function validURL($data){
		$return = is_string($data) && preg_match("/^[a-zA-Z0-9]{3,104}$/u", trim($data));
		return $return;
	}

	//191 is limited by MySQL for Indexing
	public static function validEmail($data){
		$return = is_string($data) && preg_match("/^.{1,191}$/u", trim($data)) && filter_var(trim($data), FILTER_VALIDATE_EMAIL) && preg_match("/^.{1,100}@.*\..{2,4}$/ui", trim($data)) && preg_match("/^[_a-z0-9-%+]+(\.[_a-z0-9-%+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/ui", trim($data));
		return $return;
	}

	public static function validPassword($data){
		$return = is_string($data) && preg_match("/^[\S]{6,60}$/u", $data);
		return $return;
	}

	public static function validCode($data){
		$return = is_numeric($data) && preg_match("/^[\d]{4,6}$/u", $data);
		return $return;
	}

////////////////////////////////////////////

	public static function setData($data){
		return self::$data = $data;
	}

	public static function getData($key = false){
		if($key){
			if(isset(self::$data->$key)){
				return self::$data->$key;
			} else {
				return null;
			}
		}
		return self::$data;
	}

	//No need to abstract it, but need to redefined for the Models that use it
	public function users(){
		return false;
	}

	public static function setItem($form){
		return false;
	}

	public static function getApp(){
		if(is_null(self::$app)){
			self::$app = \Slim\Slim::getInstance();
		}
		return self::$app;
	}

	//Get IP
	public static function getIP(){
		if(isset($_COOKIE['ip'])){
			return $_COOKIE['ip'];
		} else if(isset($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		} else {
			$app = ModelBruno::getApp();
			return $app->request->getIp();
		}
	}

	//Help to fix the time in MS once for the whole process
	public static function getMStime(){
		if(!self::$ms){
			self::$ms = \ms_seconds();
		}
		return self::$ms;
	}

	public static function numToAplha($num){
		$num = intval($num);
		$arr = array(
			1 => 'a',
			2 => 'b',
			3 => 'c',
			4 => 'd',
			5 => 'e',
			6 => 'f',
		);
		if(isset($arr[$num])){
			return $arr[$num];
		}
		return false;
	}

	public static function getTableStatic(){
		return (new static())->getTable();
	}

	//$read at false we delete
	public function forceRead($read=true){
		self::$force_read[$this->table][$this->id] = $read;
	}

	public static function getForceRead(){
		return self::$force_read;
	}

	public function tableExists($table){
		$app = ModelBruno::getApp();
		$connection = $this->getConnectionName();
		if(!isset(self::$schema_table[$connection])){
			self::$schema_table[$connection] = array();
			if(isset($app->bruno->databases[$connection]) && isset($app->bruno->databases[$connection]['database'])){
				$sql = 'select `table_name` from `information_schema`.`tables` where `table_schema` = ?;';
				$db = Capsule::connection($connection);
				$database = Capsule::schema($connection)->getConnection()->getDatabaseName();
				$data = $db->select( $sql , [$database] );
				foreach ($data as $value) {
					if(isset($value->table_name)){
						self::$schema_table[$connection][$value->table_name] = true;
					}
				}
			}
		}
		if(isset(self::$schema_table[$connection][$table])){
			return true;
		}
		return false;
	}

	public function getDefaultValue($table){
		$connection = $this->getConnectionName();
			if(!isset(self::$schema_default[$connection])){
				if($this->tableExists($table)){
				self::$schema_table[$connection] = array();
				$database = Capsule::schema($connection)->getConnection()->getDatabaseName();
				$sql = 'select table_name, column_name, column_default from `information_schema`.`columns` where `table_schema` = ?;';
				$db = Capsule::connection($connection);
				$data = $db->select( $sql , [$database] );
				foreach ($data as $value) {
					if(!isset(self::$schema_default[$connection][$value->table_name])){
						self::$schema_default[$connection][$value->table_name] = array();
					}
					self::$schema_default[$connection][$value->table_name][$value->column_name] = $value->column_default;
				}
			}
		}
		if(isset(self::$schema_default[$connection][$table])){
			return self::$schema_default[$connection][$table];
		}
		return false;
	}

	public function scopegetItems($query, &$list=array(), $get=false){
		$query = $query->where('id', -1); //Force to return null
		if($get){
			return $query->get();
		} else {
			return $query;
		}
	}

	public static function getColumns(){
		$model = new static();		
		if(!isset(self::$columns[$model->getTable()])){
			self::$columns[$model->getTable()] = array();
			$schema = $model->getConnection()->getSchemaBuilder();
			self::$columns[$model->getTable()] = $schema->getColumnListing($model->getTable());
		}
		return self::$columns[$model->getTable()];
	}

	public static function findMD5(int $id, $md5, $with_trash=false){
		//Read is authorized at 8 characters
		if(strlen($md5)<8){
			return false;
		}
		if($with_trash){
			$model = static::withTrashed()->where('id', $id)->where('md5', 'like', $md5.'%')->first();
		} else {
			$model = static::Where('id', $id)->where('md5', 'like', $md5.'%')->first();
		}
		return $model;
	}
	
	public static function getModel($id, $with_trash=false){
		if($with_trash){
			$model = static::withTrashed()->find($id);
		} else {
			$model = static::find($id);
		}
		return $model;
	}

	public static function getParentList(){
		return static::$parent_list;
	}

	public static function setDebugMode($onoff=false){
		$onoff = (bool) $onoff;
		self::$debugMode = $onoff;
	}

	public static function getClass($class=false){
		if(!$class){
			$class = static::getTableStatic();
		}
		$fullClass = '\\bundles\\bruno\\data\\models\\data\\'.STR::textToFirstUC($class);
		if(class_exists($fullClass)){
			return $fullClass;
		}
		return false;
	}

	protected static function errorMsg($detail='', $msg=false){
		$app = ModelBruno::getApp();
		if(!$msg){
			$msg = $app->trans->getBRUT('api', 0, 0); //You are not allowed to access the server data.
		}
		\libs\Watch::php($detail, $msg, __FILE__, __LINE__, true);
		if(!self::$debugMode){
			$json = new Json($msg, true, 406, false);
			$json->render();
		}
		return false;
	}

	public function forceSaving($force=true){
		$this->force_save = (boolean) $force;
	}

	public static function saveSkipper($change=true){
		self::$save_skipper = (boolean) $change;
	}

	public function getDirty(){
		$dirty = parent::getDirty();
		$columns = self::getColumns();
		foreach ($dirty as $key => $value) {
			if(!in_array($key, $columns)){
				unset($dirty[$key]);
			}
		}
		return $dirty;
	}

	public function save(array $options = array()){
		$app = ModelBruno::getApp();

		$columns = self::getColumns();
		$dirty = $this->getDirty();

		$new = !isset($this->id);
		if($new && (!isset($this->md5) || strlen($this->md5)!=32)){
			$this->md5 = md5(uniqid('', true));
		}

		$time_ms = ModelBruno::getMStime();

		//Only check foreign keys for new items
		if($new){
			if(in_array('c_at', $columns) && !isset($this->c_at)){
				$this->c_at = $time_ms;
			}
			if(in_array('c_by', $columns) && !isset($this->c_by)){
				$this->c_by = $app->bruno->data['user_id'];
			}
			if(in_array('u_by', $columns) && !isset($this->u_by)){
				$this->u_by = $app->bruno->data['user_id'];
			}
		} else {
			if(in_array('u_by', $columns)){
				$this->u_by = $app->bruno->data['user_id'];
			}
		}
				
		if(in_array('u_at', $columns)){
			$this->u_at = $time_ms;
		}

		//For debug mode, do not record new model
		if($new && self::$debugMode){
			return true;
		}

		$attributes = array();
		//Insure to not send to mysql any field that does not exists (for example parent_type)
		//Store those values to reapply them after saving
		foreach ($this->attributes as $key => $value) {
			if(!in_array($key, $columns)){
				$attributes[$key] = $this->attributes[$key];
				unset($this->attributes[$key]);
			}
		}

		//do nothing if dirty is empty
		if(!$this->force_save && count($dirty)<=0){
			return true;
		}

		if(!$new){
			//do not check some attributes
			unset($dirty['id']);
			unset($dirty['md5']);
			unset($dirty['c_at']);
			unset($dirty['u_at']);
			unset($dirty['updated_json']);
			unset($dirty['nosql']);
			//Help the offline update
			if(count($dirty)>0){
				$updated_json = json_decode($this->updated_json);
				if(!$updated_json || !is_object($updated_json)){
					$updated_json = new \stdClass;
				}
				foreach ($dirty as $key => $value) {
					$updated_json->$key = $time_ms;
				}
				$this->updated_json = json_encode($updated_json, JSON_UNESCAPED_UNICODE);
			}
		}
		
		$return = false;
		try {
			if(parent::save($options)){
				$return = true;
			}
			usleep(rand(10000, 15000)); //10ms
			if($new){
				$this->new_model = true;
				if($this->getTable()=='users' && (!isset($app->bruno->data['user_id']) || !$app->bruno->data['user_id'])){
					$app->bruno->data['user_id'] = $this->id;
				}
			}
			//Reapply the fields that were not part of table columns
			foreach ($attributes as $key => $value) {
				$this->attributes[$key] = $value;
			}
			$this->pivots_save();
		} catch(\Exception $e){
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'Exception: '.$e->getLine().' / '.$e->getMessage(), __FILE__, __LINE__, true);
		}

		if($return){
			$this->getNoSQL(true); //Cache the data as NoSQL format
			$this->forceRead();
			return $this;
		}
		return false;
		
	}

	//This will update u_at, even if the user doesn't have write permission
	public function touchUpdateAt(){
		$app = ModelBruno::getApp();
		if (!isset($this->u_at) || !isset($this->id)) {
			return false;
		}
		$time_ms = ModelBruno::getMStime();
		$result = $this::withTrashed()->where('id', $this->id)->getQuery()->update(['u_at' => $time_ms]);
		$this->getNoSQL(true); //Cache the data as NoSQL format
		usleep(rand(10000, 15000)); //10ms
		$this->forceRead();
		return $this;
	}
	
	public function delete(){
		//We don't delete in debug mode
		if(self::$debugMode){
			return true;
		}
		if(!isset($this->d_at) && isset($this->attributes) && array_key_exists('d_at', $this->attributes)){
			$save = false;
			if(array_key_exists('d_by', $this->attributes)){
				$app = ModelBruno::getApp();
				$this->d_by = $app->bruno->data['user_id'];
				$save = true;
			}
			if($save){
				$this->save();
			}
			$time_ms = ModelBruno::getMStime();
			$this::withTrashed()->where('id', $this->id)->getQuery()->update(['d_at' => $time_ms]);
			usleep(rand(10000, 15000)); //10ms
			$this->touchUpdateAt(); //Update of NoSQL is inside this method
			$this->forceRead(false);
		}
		return true;
	}

	public function restore(){
		//We don't restore in debug mode
		if(self::$debugMode){
			return true;
		}
		if(isset($this->d_at) && isset($this->attributes) && array_key_exists('d_at', $this->attributes)){
			$save = false;
			if(array_key_exists('d_by', $this->attributes)){
				$this->d_by = null;
				$save = true;
			}
			if($save){
				$this->save();
			}
			$time_ms = ModelBruno::getMStime();
			$this::withTrashed()->where('id', $this->id)->getQuery()->update(['d_at' => null]);
			usleep(rand(10000, 15000)); //10ms
			$this->touchUpdateAt();
			$this->forceRead();
		}
		return true;
	}

	//True: will display with trashed
	//False (default): will display only not deleted
	public function enableTrash($trash=false){
		$trash = (boolean) $trash;
		$this->with_trash = $trash;
	}

	//True: will display with trashed
	//False (default): will display only not deleted
	public static function enableTrashGlobal($trash=false){
		$app = ModelBruno::getApp();
		$trash = (boolean) $trash;
		self::$with_trash_global = $trash;
	}

	public function getNoSQL($force=false){

		$result = false;

		//We use a NoSQL format to speedup the result displayed
		if($force || !isset($this->nosql) || is_null($this->nosql)){
			//Make sure we cache with all fields
			$model = static::find($this->id);
			$result = json_decode($model->toJson());
			foreach(static::$class_timestamp as $value) {
				if(isset($result->$value)){ $result->$value = (int) (new \DateTime($result->$value))->getTimestamp(); }
			}
			foreach($this->model_timestamp as $value) {
				if(isset($result->$value)){ $result->$value = (int) (new \DateTime($result->$value))->getTimestamp(); }
			}
			//Convert number to integer. NULL will stay NULL thanks to isset()
			foreach(static::$class_integer as $value) {
				if(isset($result->$value)){ $result->$value = (int) $result->$value; }
			}
			foreach($this->model_integer as $value) {
				if(isset($result->$value)){ $result->$value = (int) $result->$value; }
			}
			//Convert boolean
			foreach(static::$class_boolean as $value) {
				if(isset($result->$value)){ $result->$value = (boolean) $result->$value; }
			}
			foreach($this->model_boolean as $value) {
				if(isset($result->$value)){ $result->$value = (boolean) $result->$value; }
			}
			//Must always check updated_json
			if(!isset($result->updated_json) || !is_object($result->updated_json)){
				$updated_json = null;
				if(!is_null($this->updated_json)){
					$updated_json = json_decode($this->updated_json);
				}
				if(is_object($updated_json)){
					$result->updated_json = $updated_json;
				} else {
					unset($result->updated_json);
				}
			}
			//Must always check parent_type
			if(isset($result->parent_id) && !isset($result->parent_type) && is_string($this->parent_type)){ $result->parent_type = $this->parent_type; }
			//to avoid infinite loop
			unset($result->nosql);
			$this->nosql = json_encode($result, JSON_UNESCAPED_UNICODE);

			static::withTrashed()->where('id', $this->id)->getQuery()->update(['nosql' => $this->nosql]);
			usleep(rand(1000, 5000)); //1ms
		} else {
			$result = json_decode($this->nosql);
		}

		//Limit the scope for the user
		if(isset($result->md5)){
			if($this->crud == 2){ //CRU
				$result->md5 = substr($result->md5, 0, 16);
			} else if($this->crud == 3){ //CR
				$result->md5 = substr($result->md5, 0, 8);
			}
		}

		return $result;
	}

	public function pivots_format($form){
		$app = ModelBruno::getApp();
		if(!static::$pivot_include){
			return false;
		}
		$save = false;
		foreach ($form as $key => $list) {
			if( preg_match("/^([a-z0-9_]+)>([a-z0-9_]+)$/ui", $key, $match) && is_object($list) && count((array)$list)>0 ){
				$type = $match[1];
				$column = $match[2];
				foreach ($list as $type_id => $value) {
					//We cannot block or authorize itself
					if($column=='access' && $type_id==$app->bruno->data['user_id'] && !static::$save_user_access){
						continue;
					} else if(is_numeric($type_id) && (int)$type_id>=0){
						$save = true;
						if($this->pivots_var==null){ $this->pivots_var = new \stdClass; }
						if(!isset($this->pivots_var->$type)){ $this->pivots_var->$type = new \stdClass; }
						if(!isset($this->pivots_var->$type->$type_id)){ $this->pivots_var->$type->$type_id = new \stdClass; }
						if(!isset($this->pivots_var->$type->$type_id->$column)){ $this->pivots_var->$type->$type_id->$column = $value; }
						$this->forceSaving();
					}	
				}
			}
		}
		return $save;
	}

	protected function setPivotExtra($type, $column, $value){
		$pivot_array = array(
			$column => $value,
		);
		return $pivot_array;
	}

	public function pivots_get(){
		return $this->pivots_var;
	}

	public function pivots_save(array $parameters = array()){
		if(!static::$pivot_include){
			return true;
		}
		$success = true;
		$touch = false;
		if(is_object($this->pivots_var)){
			foreach ($this->pivots_var as $type => $type_id_list) {
				if(!$success){ break; }
				foreach ($type_id_list as $type_id => $column_list) {
					if(!$success){ break; }
					//Check if the user has access to the element to avoid unwanted assignements
					$class = $this::getClass($type);
					if($model = $class::getModel($type_id, true, true)){
						foreach ($column_list as $column => $result) {
							$loop = 10; //do 10 tries at the most
							retry:
							if(!$loop || $loop<=0){
								continue;
							}
							$loop--;
							$value = $result;
							//Convert the value to be compriable with database
							if(is_bool($value)){
								$value = (int) $value;
							}
							//Do not convert into string a NULL value (ifnot it will return a 0 timestamp or empty string instead of NULL value)
							if(!is_null($value)){
								$value = (string) $value;
							}
							if(!$success){ break; }
							$pivot = false;
							$pivot_array = $this->setPivotExtra($type, $column, $value);
							if(method_exists(get_called_class(), $type)){ //Check if the pivot call exists
								$pivot_relation = $this->$type();
								if($pivot_relation !== false && method_exists($pivot_relation, 'updateExistingPivot') && method_exists($pivot_relation, 'attach')){
									if($pivot = $pivot_relation->find($type_id)){ //Check if the pivot exists
										//We delete c_at since it already exists
										unset($pivot_array['c_at']);
										//Update an existing pivot
										if(is_null($pivot->pivot->$column)){ //Do not convert a NULL into a string
											$value_old = $pivot->pivot->$column;
										} else {
											$value_old = (string) $pivot->pivot->$column;
										}
										if($value_old != $value){
											if($pivot_relation->updateExistingPivot($type_id, $pivot_array)){
												$touch = true;
											} else {
												$success = false;
											}
										}
										continue;
									} else {
										//Create a new pivot line
										if($column!='access' && !isset($this->pivots_var->$type->$type_id->access)){
											//By default, if we affect a new pivot, we always authorized access if it's not specified (for instance a user assigned to a task will automaticaly have access to it)
											$pivot_array['access'] = true;
										}
										//For an unknown reason, sometime the pivot exist already, so attach will fail
										try {
											$pivot_relation->attach($type_id, $pivot_array); //attach() return nothing
										} catch (\Exception $e) {
											//\libs\Watch::php(true, 'pivots_save => this is only a warning, the system will retry it. It looks like sometime the pivot is not find but actually exists, so attach is launched', __FILE__, __LINE__, true);
											goto retry; //restart the operation
										}
										$this->pivot_extra_array = false;
										$touch = true;
										continue;
									}
								}
								$success = false;
								break;
							}
							$success = false;
							break;
						}
					}
				}
			}
		}
		
		if($touch){
			usleep(rand(30000, 35000));
			$this->touchUpdateAt();
		}
		
		return $success;
	}

	public static function getCSV($list, $filename='data.csv'){
		
		$app = ModelBruno::getApp();
		$app->response->headers->set('Content-Encoding', 'UTF-8');
		$app->response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
		$app->response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		
		$output = fopen('php://output', 'w');
		fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); //UTF-8 BOM
		$list = json_decode(json_encode($list), true); //Force to be an array
		$fields = array();
		if(count($list)>0){
			foreach ($list as $item) {
				if(is_array($item)){
					foreach ($item as $key => $value) {
						if(!in_array($key, $fields)){
							$fields[] = $key;
						}
					}
				}
			}
			fputcsv($output, $fields);

			$flip = array_flip($fields);
			$results = array();
			$i = 0;
			foreach ($list as $item) {
				if(is_array($item)){
					$results[$i] = array();
					foreach ($fields as $j => $key) {
						if(isset($item[$key])){
							$results[$i][$j] = $item[$key];
						} else {
							$results[$i][$j] = null;
						}
					}
					$i++;
				}
			}
			foreach ($results as $result) {
				fputcsv($output, $result);
			}
		}

		fclose($output);
		
		return exit(0);
	}

}
