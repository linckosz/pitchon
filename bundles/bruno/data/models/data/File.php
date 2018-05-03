<?php

namespace bundles\bruno\data\models\data;

use WideImage\WideImage;
use \libs\STR;
use \libs\Json;
use \libs\Folders; 
use \libs\Video;
use \bundles\bruno\data\models\ModelBruno;

class File extends ModelBruno {

	protected $connection = 'data';

	protected $table = 'file';
	protected $morphClass = 'file';

	protected $primaryKey = 'id';

	protected static $pivot_include = true;

	protected $visible = array(
		'id',
		'md5',
		'c_at',
		'u_at',
		'updated_json',
		'parent_type',
		'parent_id',
		'uploaded_by',
		'title',
		'category',
		'ori_type',
		'ori_ext',
		'thu_type',
		'thu_ext',
		'size',
		'width',
		'height',
		'progress',
		'link',
	);

	protected $model_integer = array(
		'size',
		'width',
		'height',
	);

	protected static $parent_list = array('pitch', 'question', 'answer', 'user');

////////////////////////////////////////////

	protected $imagequalitysize = '1024';
	protected $imagequalitycomp = '75';
	protected $realorientation = true;

	protected static $list_categories = array(
		
		'image' => array('image/bmp', 'image/x-windows-bmp', 'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/vnd.wap.wbmp'),

		//Note: because compilation of ffmpeg fails with webm support, we cannot include it
		'video' => array('application/asx', 'application/vnd.ms-asf', 'application/vnd.rn-realmedia', 'application/vnd.rn-realmedia-vbr', 'application/x-mplayer2', 'application/x-pn-mpg', 'application/x-troff-msvideo', 'content/unknown', 'image/mov', 'image/mpg', 'video/3gpp', 'video/avi', 'video/dvd', 'video/mp4', 'video/mp4v-es', 'video/mpeg', 'video/mpeg2', 'video/mpg', 'video/msvideo', 'video/quicktime', 'video/xmpg2', 'video/x-flv', 'flv-application/octet-stream', 'video/x-m4v', 'video/x-matroska', 'video/x-mpeg', 'video/x-mpeg2a', 'video/x-mpg', 'video/x-msvideo', 'video/x-ms-asf', 'video/x-ms-asf-plugin', 'video/x-ms-wm', 'video/x-ms-wmv', 'video/x-ms-wmx', 'video/x-quicktime', 'video/x-sgi-movie'),

	);

////////////////////////////////////////////

	/*
	//Many(File) to One(Pitch)
	public function pitch(){
		//This do get all files from File table, even if unlinked from a pitch
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Pitch', 'parent_id');
	}
	*/

	//One(File) to One(Pitch)
	public function pitch_file(){
		//This do get only file linked from a parent, but it's limited to one to one
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Pitch', 'id', 'file_id');
	}

	//One(File) to One(Pitch)
	public function pitch_brand(){
		//This do get only file linked from a parent, but it's limited to one to one
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Pitch', 'id', 'brand_pic');
	}

	//One(File) to One(Pitch)
	public function pitch_ad(){
		//This do get only file linked from a parent, but it's limited to one to one
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Pitch', 'id', 'ad_pic');
	}

	//One(File) to One(Question)
	public function question(){
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Question', 'id', 'file_id');
	}

	//One(File) to One(Question)
	public function answer(){
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\Answer', 'id', 'file_id');
	}

	//One(File) to One(User)
	public function user(){
		return $this->belongsTo('\\bundles\\bruno\\data\\models\\data\\User', 'parent_id'); //parent_id => user_id
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
			if(
				   isset($form->parent_type)
				&& isset($form->parent_id)
				&& isset($form->parent_md5)
				&& is_string($form->parent_type)
				&& is_numeric($form->parent_id)
				&& is_string($form->parent_md5)
				&& in_array($form->parent_type, static::$parent_list)
			){
					$class = self::getClass($form->parent_type);
					if($class && $class::Where('id', $form->parent_id)->where('md5', $form->parent_md5)->first()){
						$error = false;
						$model->parent_type = $form->parent_type;
						$model->parent_id = (int) $form->parent_id;
					}
			} else if(
				   isset($form->parent_type)
				&& isset($form->parent_data)
				&& is_string($form->parent_type)
			){
				if($parent_data = json_decode($form->parent_data)){
					$class = self::getClass($form->parent_type);
					if($class){
						$parent = $class::setItem($parent_data);
						if(is_object($parent)){
							if($parent->save()){
								$error = false;
								$model->parent_type = $form->parent_type;
								$model->parent_id = (int) $parent->id;
							}
						}
					}
				}
			}
			if($error){
				$errfield = $form->parent_type;
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

		return $model;

		failed:
		if($new){
			$errmsg = $app->trans->getBRUT('api', 11, 1)."\n"; //File upload failed
		} else {
			$errmsg = $app->trans->getBRUT('api', 13, 5)."\n"; //File upload failed
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
		if(!isset($list['question'])){ $list['question']=array(); }
		if(!isset($list['answer'])){ $list['answer']=array(); }
		$query = $query
		->where(function($query) use ($list) {
			$query
			->whereHas('pitch_file', function($query) use ($list) {
				$query->whereIn('pitch.id', $list['pitch']);
			})
			->orWhereHas('pitch_brand', function($query) use ($list) {
				$query->whereIn('pitch.id', $list['pitch']);
			})
			->orWhereHas('pitch_ad', function($query) use ($list) {
				$query->whereIn('pitch.id', $list['pitch']);
			})
			->orWhereHas('question', function($query) use ($list) {
				$query->whereIn('question.id', $list['question']);
			})
			->orWhereHas('answer', function($query) use ($list) {
				$query->whereIn('answer.id', $list['answer']);
			})
			->orWhereHas('user', function($query) use ($list) {
				$app = ModelBruno::getApp();
				$query->where('user.id', $app->bruno->data['user_id']);
			});
		});
		if($get){
			return $query->get();
		} else {
			return $query;
		}
	}

////////////////////////////////////////////

	public function setCompression($value = true){
		$this->imagecompressed = $value;
	}

	public function setRealOrientation($value = true){
		$this->realorientation = $value;
	}

	protected function setOrientation(){
		$orientation = 1;
		$flip_x = false;
		$flip_y = false;
		$angle = false;
		if(isset($this->tmp_name)){
			if($exif = @exif_read_data($this->tmp_name)){
				if(isset($exif['Orientation'])){
					$orientation = $exif['Orientation'];
					switch ($orientation) {
						case 1:
							// 1 => Do nothing
							break;
						case 2:
							// 2 => Flip horizontal (x)
							$flip_x = true;
							break;
						case 3:
							// 3 => Rotate 180 clockwise (180)
							$angle = 180;
							break;
						case 4:
							// 4 => vertical flip (y)
							$flip_y = true;
							break;
						case 5:
							// 5 => Rotate 90 clockwise and flip vertically (90 + y)
							$flip_y = true;
							$angle = 90;
							break;
						case 6:
							// 6 => Rotate 90 clockwise
							$angle = 90;
							break;
						case 7:
							// 7 => Rotate 90 clockwise and flip horizontally (90 + x)
							$flip_x = true;
							$angle = 90;
							break;
						case 8:
							// 8 => Rotate 270 clockwise (270)
							$angle = 270;
							break;
						default:
							// 1 => Do nothing
							$orientation = 1; //Force no orientation
					}
				}
			}
		}
		$this->orientation = $orientation;
		return array($flip_x, $flip_y, $angle);
	}

	public function saveParent(){
		return parent::save();
	}

	public function save(array $options = array()){
		$app = ModelBruno::getApp();
		$new = false;
		if(!$this->id){ //Only copy a file for new items
			if($this->error!=0 || !$this->fileformat()){
				return false;
			}
			if($this->size > 1000000000){
				$msg = $app->trans->getBRUT('api', 3, 7); //File too large
				$json = new Json($msg, true, 400);
				$json->render(400);
				return false;
			}
			try {
				$this->setCategory();
				$this->link = md5(uniqid('', true));
				$folder_ori = new Folders;
				$folder_ori->createPath($app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/');
				$this->thu_type = null;
				$this->thu_ext = null;
				$this->progress = 100;
				$source = $this->tmp_name;
				if($this->category=='image'){
					$orientation = $this->setOrientation();
					$src = WideImage::load($this->tmp_name);
					$this->width = $src->getWidth();
					$this->height = $src->getHeight();
					$modify = false;
					$resize = false;
					$compression = 90;
					if($this->ori_type == 'image/jpeg' || $this->ori_type == 'image/png') {
						if($this->ori_type == 'image/jpeg'){
							exec("identify -format '%Q' \"$source\" 2>&1 ", $tablo, $error);
							if(!$error && is_numeric($tablo[0]) && $tablo[0]>0){
								$compression = $tablo[0];
							}
							if($this->imagecompressed){
								if($compression > $this->imagequalitycomp){
									$modify = true;
									$compression = $this->imagequalitycomp;
								}
								if($this->width > $this->imagequalitysize || $this->height > $this->imagequalitysize){
									$modify = true;
									$src = $src->resize($this->imagequalitysize, $this->imagequalitysize, 'inside', 'any');
								}
							}
						}
						if($this->orientation!=1){
							$modify = true;
							if($this->realorientation){
							//For a jpeg we check if there is any orientation, if yes we rotate and overwrite
								if($orientation[0]){ $src = $src->mirror(); } //Mirror left/right
								if($orientation[1]){ $src = $src->flip(); } //Flip up/down
								if($orientation[2]){ $src = $src->rotate($orientation[2]); }
							} //Rotation
							$this->orientation = 1;
						}

						$this->width = $src->getWidth();
						$this->height = $src->getHeight();
					}

					if($modify){
						if($this->ori_type == 'image/png'){
							$src = $src->saveToFile($folder_ori->getPath().$this->link.'.png');
						} else {
							$src = $src->saveToFile($folder_ori->getPath().$this->link.'.jpg', $compression);
						}
						$this->size = filesize($folder_ori->getPath().$this->link.'.'.$this->ori_ext);
					} else {
						copy($this->tmp_name, $folder_ori->getPath().$this->link.'.'.$this->ori_ext);
					}

					$folder_thu = new Folders;
					
					$folder_thu->createPath($app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/thumbnail/');
					try {
						$src = WideImage::load($this->tmp_name);
						$src = $src->resize(256, 256, 'inside', 'any');
						if($this->realorientation){
							if($orientation[0]){ $src = $src->mirror(); } //Mirror left/right
							if($orientation[1]){ $src = $src->flip(); } //Flip up/down
							if($orientation[2]){ $src = $src->rotate($orientation[2]); }
						} //Rotation

						$has_transparency = false;
						//For PNG, check if we have any transparent pixel, if yes we do keep PNG format;
						if($this->ori_type == 'image/png'){

							$im = $src->getHandle();
							$width = $src->getWidth();
							$height = $src->getHeight();
							for($x = 0; $x < $width; $x++){
								for($y = 0; $y < $height; $y++) {
									$alpha = (imagecolorat($im,$x,$y) & 0x7F000000) >> 24;
									if($alpha > 0){
										$has_transparency = true;
										break 2;
									}
								}
							}
						}

						//if($this->ori_type == 'image/png' && $has_transparency){
						if($this->ori_type == 'image/png'){
							$this->thu_type = 'image/png';
							$this->thu_ext = 'png';
							$src = $src->saveToFile($folder_thu->getPath().$this->link.'.png');
						} else {
							$this->thu_type = 'image/jpeg';
							$this->thu_ext = 'jpg';
							$src = $src->saveToFile($folder_thu->getPath().$this->link.'.jpg', 60);
						}
					} catch(\Exception $e){
						\libs\Watch::php(\error\getTraceAsString($e, 10), 'Exception: '.$e->getLine().' / '.$e->getMessage(), __FILE__, __LINE__, true);
						$this->thu_type = 'image/png';
						$this->thu_ext = 'png';
						copy($app->bruno->path.'/bundles/bruno/api/public/images/generic/unavailable.png', $folder_thu->getPath().$this->link.'.png');
					}
				} else if($this->category=='video'){
					$this->progress = 0; //Only video needs significant time for compression
					$this->size = 0;
					$folder_thu = new Folders;
					$folder_thu->createPath($app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/thumbnail/');
					$folder_txt = new Folders;
					$folder_txt->createPath($app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/convert/'); //Because of exec limitation (does not work with ssh2.sftp), we use local link
					$this->thu_type = 'image/jpeg';
					$this->thu_ext = 'jpg';
					if($dot = strrpos($this->name, '.')){
						$this->name = substr($this->name, 0, $dot).'.mp4';
					}
					$this->ori_type = 'video/mp4';
					$this->ori_ext = 'mp4';

					$destination = $app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/'.$this->link.'.'.$this->ori_ext;
					$thumbnail = $app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/thumbnail/'.$this->link.'.'.$this->thu_ext;

					$video = new Video($source, $destination, $thumbnail, $folder_txt->getPath().$this->link);
					
					if($video->thumbnail()!==0){
						return false;
					}
					if($video->convert(1)!==0){
						return false;
					}
					$info = $video->getInfo();
					$this->width = $info['width_new'];
					$this->height = $info['height_new'];
				} else {
					//Reject any other format
					return false;
				}
				$new = true;
			} catch(\Exception $e){
				\libs\Watch::php(\error\getTraceAsString($e, 10), 'Exception: '.$e->getLine().' / '.$e->getMessage(), __FILE__, __LINE__, true);
				return false;
			}
		}
		$return = parent::save($options);

		//Only allow one File per parent object
		if($new){
			$files = File::Where('id', '!=', $this->id)->where('parent_type', $this->parent_type)->where('parent_id', $this->parent_id)->get(array('id'));
			foreach ($files as $file) {
				$file->delete();
			}
		}

		if($new && $this->category=='video'){
			sleep(1); //wait 1s to make sure the conversion is starting
			$this->checkProgress();
		}
		
		return $return;
	}

	public function checkProgress(){
		if($this->category=='video' && $this->progress<100 && !$this->error && isset($this->id)){
			$app = ModelBruno::getApp();
			$path = $app->bruno->filePath.'/'.$this->uploaded_by.'/convert/'.$this->link;
			if(is_file($path) && time()-filemtime($path) < 60 && $this->progress>=1){ //If the conversion file is less than 1 minutes, we should be in middle of conversion
				return true;
			}
			$url = $app->environment['slim.url_scheme'].'://'.$app->request->headers->Host.'/file/progress/'.$this->md5.'/'.$this->id;
			$data = false;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1); //Cannot use MS, it will crash the request
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json; charset=UTF-8',
					'Content-Length: ' . mb_strlen($data),
				)
			);
			curl_exec($ch);
			@curl_close($ch);
		}
		return true;
	}

	public function setProgress(){
		if($this->category == 'video' && $this->progress < 100 && !$this->error){
			$app = ModelBruno::getApp();
			set_time_limit(24*3600); //Set to 1 day workload at the most
			$path = $app->bruno->filePath.'/'.$this->uploaded_by.'/convert/'.$this->link;
			$file = $app->bruno->filePath.'/'.$this->uploaded_by.'/'.$this->link.'.'.$this->ori_ext;
			$loop = true;
			$progress = 100;
			$try = 10; //5s of try
			$time_ms = ModelBruno::getMStime();
			self::where('id', $this->id)->getQuery()->update(['progress' => 1, 'u_at' => $time_ms]);
			usleep(rand(10000, 15000)); //10ms
			while($loop){
				$handle = fopen($path, 'r');
				if($handle){
					if(is_file($path) && filesize($path)>0){
						$contents = fread($handle, filesize($path));
						$reg_duration = "/\b.*?Duration:\s*?(\d\d):(\d\d):(\d\d)\.(\d\d).*\b/i";
						if(preg_match_all($reg_duration, $contents, $matches, PREG_SET_ORDER)){
							$match = $matches[count($matches)-1];
							$duration = $match[1]*360000 + $match[2]*6000 + $match[3]*100 + $match[4];
							$reg_time  = "/ time=\s*?(\d\d):(\d\d):(\d\d)\.(\d\d) /i";
							if(preg_match_all($reg_time, $contents, $matches, PREG_SET_ORDER)){
								$match = $matches[count($matches)-1];
								$time = $match[1]*360000 + $match[2]*6000 + $match[3]*100 + $match[4];
								$reg_size  = "/\b.*?\d Lsize=.*\b/i";
								if($time == 0 || $duration == 0){
									$progress = 0;
								} else if($time>=$duration || preg_match_all($reg_size, $contents)){
									$progress = 100;
								} else {
									$progress = round(100*$time/$duration);
									if($progress<0){ $progress = 0; }
									else if($progress>100){ $progress = 100; }
								}
							}
							if(!is_file($file) || filesize($file)<=0){
								$try--;
								$progress = 0;
							} else if(filemtime($path) < time()-3600){ //If the conversion log is more than one hour without modification, we considerate it as fail
								$progress = 100;
								$try = 0;
							}
						}
					}
				}
				fclose($handle);
				$size = 0;
				if(is_file($file)){
					$size = (int) filesize($file);
				}
				if($progress<1){
					$progress = 1; //1% helps to show we are in middle of compression
				} else if($progress>100){
					$progress = 100;
				}
				
				if($progress>=100 || $try<=0){ 
					$loop = false;
				}

				$time_ms = ModelBruno::getMStime();
				self::where('id', $this->id)->getQuery()->update(['progress' => $progress, 'size' => $size, 'u_at' => $time_ms]);
				usleep(rand(10000, 15000)); //10ms
			}
			if($try<=0){
				$time_ms = ModelBruno::getMStime();
				self::where('id', $this->id)->getQuery()->update(['progress' => 100, 'size' => $size, 'error' => 1, 'u_at' => $time_ms]);
				usleep(rand(10000, 15000)); //10ms
			}

		}
	}

	public function setCategory(){
		$this->ori_type = strtolower($this->ori_type);
		foreach (static::$list_categories as $category => $list) {
			if(in_array($this->ori_type, $list)){
				$this->category = $category;
				break;
			}
		}
		return $this->category;
	}

	public function getCategory(){
		return $this->category;
	}

	//Return the true file extension if it has be artificially modified (works only for pictures)
	//http://stackoverflow.com/questions/1282351/what-kind-of-file-types-does-php-getimagesize-return
	//$file must be a full link
	protected function fileformat(){
		$this->ori_ext = false;
		if(is_file($this->tmp_name) && filesize($this->tmp_name)!==false){
			/*
			[IMAGETYPE_GIF] => 1
			[IMAGETYPE_JPEG] => 2
			[IMAGETYPE_PNG] => 3
			[IMAGETYPE_SWF] => 4
			[IMAGETYPE_PSD] => 5
			[IMAGETYPE_BMP] => 6
			[IMAGETYPE_TIFF_II] => 7
			[IMAGETYPE_TIFF_MM] => 8
			[IMAGETYPE_JPC] => 9
			[IMAGETYPE_JP2] => 10
			[IMAGETYPE_JPX] => 11
			[IMAGETYPE_JB2] => 12
			[IMAGETYPE_SWC] => 13
			[IMAGETYPE_IFF] => 14
			[IMAGETYPE_WBMP] => 15
			[IMAGETYPE_JPEG2000] => 9
			[IMAGETYPE_XBM] => 16
			[IMAGETYPE_ICO] => 17
			[IMAGETYPE_UNKNOWN] => 0
			[IMAGETYPE_COUNT] => 18 
			*/
			$formattab = array(
			1 => 'gif',
			2 => 'jpg',
			3 => 'png',
			4 => 'swf',
			5 => 'psd',
			6 => 'bmp',
			7 => 'tif',
			8 => 'tif',
			9 => 'jpc',
			10 => 'jp2',
			11 => 'jpf',
			12 => 'jb2',
			13 => 'swc',
			14 => 'aiff',
			15 => 'wbmp',
			16 => 'xbm',
			//[IMAGETYPE_ICO] => 17 => "ico", //mpeg videos are detected as IMAGETYPE_ICO !!
			//[IMAGETYPE_UNKNOWN] => 0 => "", //No need to use it
			//[IMAGETYPE_COUNT] => 18 => "" //I don't know what it is
			);
			if(strstr($this->name, '.')){
				$this->ori_ext = mb_strtolower(substr($this->name,strrpos($this->name, ".")+1));
			}
			
			if(filesize($this->tmp_name)>=12){ //Because getimagesize bug below de 12 bytes (can try with .txt "12345678901")
				if($size = getimagesize($this->tmp_name)){
					$tab = $size[2];
					if(array_key_exists($tab, $formattab)){
						$this->ori_ext = $formattab[$tab];
					}
				}
			}
		}
		return $this->ori_ext;
	}

	//IPTC data list
	protected function output_iptc_data( $image_path ) {
		$info = 0;
			if(is_file($image_path) && filesize($image_path)>=12){
				$size = getimagesize ( $image_path, $info);
			}
		$list = "";
		if(is_array($info)) {
			$iptc = iptcparse($info['APP13']);
			foreach (array_keys($iptc) as $s) {
				$c = count ($iptc[$s]);
				for ($i=0; $i <$c; $i++)
				{
					$list.=$iptc[$s][$i].'<br />';
				}
			}
		}
		return $list;
	}

	//EXIF data list
	protected function output_exif_data($image_path){
		$exif = exif_read_data($image_path);
		$list = "";
		foreach ($exif as $key => $section) {
			if(is_array($section)){
				foreach ($section as $name => $val) {
					$list .= "$key.$name: $val <br />";
				}
			} else {
				$list .= "$key : $section <br />";
			}
		}
		return $list;
	}

	//Return a Unix date if the fiel IPTC has been filled
	protected function UnixIPTCDdate($filetp){
		$unixDate = false;
		if(is_file($filetp) && filesize($filetp)>=12){
			$size = getimagesize($filetp, $info);
			if (!empty($filetp) && is_file($filetp) && $size[2]==2 && isset($info['APP13'])){ //$size[2]==2 is JPEG
				if($iptc = iptcparse($info['APP13'])){
					if(isset($iptc['2#055'][0]) && isset($iptc['2#060'][0])){
						$YMD = $iptc['2#055'][0];
						$HMS = $iptc['2#060'][0];
						$unixDate = mktime(substr($HMS,0,2),substr($HMS,2,2),substr($HMS,4,2),substr($YMD,4,2),substr($YMD,6,2),substr($YMD,0,4));
					}
				}
			}
		}
		return $unixDate;
	}


	//Return UNIX timestamp date
	protected function convertDate($filetp){
		if(is_file($filetp) && filesize($filetp)>=12){
			$size = getimagesize($filetp, $info);
			if($unixIPTC=UnixIPTCDdate($filetp)){
				return $unixIPTC;
			}	else if($size[2]==2 && $exif=@exif_read_data($filetp)){
				if (isset($exif['EXIF']['DateTimeOriginal'])){
					$tpdate = $exif['EXIF']['DateTimeOriginal'];
					$tpdate = mktime(substr($tpdate,11,2),substr($tpdate,14,2),substr($tpdate,17,2),substr($tpdate,5,2),substr($tpdate,8,2),substr($tpdate,0,4));
					return $tpdate;
				} else if (isset($exif['DateTimeOriginal'])){
					$tpdate = $exif['DateTimeOriginal'];
					$tpdate = mktime(substr($tpdate,11,2),substr($tpdate,14,2),substr($tpdate,17,2),substr($tpdate,5,2),substr($tpdate,8,2),substr($tpdate,0,4));
					return $tpdate;
				} else if (isset($exif['EXIF']['DateTimeDigitized'])){
					$tpdate = $exif['EXIF']['DateTimeDigitized'];
					$tpdate = mktime(substr($tpdate,11,2),substr($tpdate,14,2),substr($tpdate,17,2),substr($tpdate,5,2),substr($tpdate,8,2),substr($tpdate,0,4));
					return $tpdate;
				} else if (isset($exif['DateTimeDigitized'])){
					$tpdate = $exif['DateTimeDigitized'];
					$tpdate = mktime(substr($tpdate,11,2),substr($tpdate,14,2),substr($tpdate,17,2),substr($tpdate,5,2),substr($tpdate,8,2),substr($tpdate,0,4));
					return $tpdate;
				} else if (isset($exif['IFD0']['DateTime'])){
					$tpdate = $exif['IFD0']['DateTime'];
					$tpdate = mktime(substr($tpdate,11,2),substr($tpdate,14,2),substr($tpdate,17,2),substr($tpdate,5,2),substr($tpdate,8,2),substr($tpdate,0,4));
					return $tpdate;
				} else if (isset($exif['DateTime'])){
					$tpdate = $exif['DateTime'];
					$tpdate = mktime(substr($tpdate,11,2),substr($tpdate,14,2),substr($tpdate,17,2),substr($tpdate,5,2),substr($tpdate,8,2),substr($tpdate,0,4));
					return $tpdate;
				}	else if (filectime($filetp)) { //File creation date
					return filectime($filetp);
				}	else if (isset($exif['FILE']['FileDateTime'])){
					return $exif['FILE']['FileDateTime']; //File modification date
				}	else if (isset($exif['FileDateTime'])){
					return $exif['FileDateTime']; //File modification date
				} else {
					return time();
				}
			} else if (filectime($filetp)) {
				return filectime($filetp);
			} else {
				return time();
			}
		} else {
			return time();
		}
	}

}
