<?php
// Category 3

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \libs\Datassl;
use \libs\STR;
use \libs\Folders;
use \libs\Video;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\User;
use \bundles\bruno\data\models\data\File;
use \bundles\bruno\data\models\data\Question;
use WideImage\WideImage;
use Endroid\QrCode\QrCode;

class ControllerFile extends Controller {

	public function result(){
		$app = ModelBruno::getApp();
		$app->response->headers->set('Content-Type', 'content="text/html; charset=UTF-8"');//header('Content-Type: content="text/html; charset=UTF-8"');
		ob_clean();
		echo '
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>jQuery Iframe Transport Plugin Redirect Page</title>
	</head>
	<body>
		<script>
			document.body.innerText=document.body.textContent=decodeURIComponent(window.location.search.slice(1));
		</script>
	</body>
</html>
		';
		return exit(0);
	}

	public function upload_options(){
		$msg = array('msg' => 'OK');
		(new Json($msg))->render();
		return exit(0);
	}

	public function upload_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();

		$result = new \stdClass;
		$result->read = new \stdClass;
		$result->read->file = new \stdClass;

		$failmsg = $app->trans->getBRUT('api', 11, 1)."\n"; //File upload failed.
		$errmsg = $failmsg.$app->trans->getBRUT('api', 0, 7); //Please try again.
		$errfield = 'undefined';

		$success = false;
		$files_array = array();
		if(isset($_FILES)){
			foreach ($_FILES as $file => $fileArray) {
				if(is_array($fileArray['tmp_name'])){
					$i = 0;
					foreach ($fileArray['tmp_name'] as $j => $temp) {
						$files_array[$i] = array();
						foreach ($fileArray as $key => $value) {
							if(isset($fileArray[$key][$j])){
								$files_array[$i][$key] = $fileArray[$key][$j];
							}
						}
						$i++;
					}
				} else {
					$files_array[0] = $fileArray;
				}
			}
			foreach ($files_array as $key => $value) {
				$model = File::setItem($data);
				if(is_object($model)){
					if(isset($model->parent_id)){
						$data->parent_id = $model->parent_id;
					}
					$parent = false;
					if($parent_class = ModelBruno::getClass($data->parent_type)){
						$parent = $parent_class::find($data->parent_id); //Parent has been validated in setItem previously
					}
					if(!$parent){
						$success = false;
						break;
					}
					$model->title = STR::break_line_conv($value['name'], '');
					$model->ori_type = mb_strtolower($value['type']);
					$model->tmp_name = $value['tmp_name'];
					$model->error = $value['error'];
					$model->size = $value['size'];
					$model->parent_type = $data->parent_type;
					$model->parent_id = $data->parent_id;
					$model->uploaded_by = $app->bruno->data['user_id'];
					if(isset($data->precompress)){ 
						if(!is_bool($data->precompress)){
							if($data->precompress == 'true'){
								$data->precompress = true;
							} else if($data->precompress == 'false'){
								$data->precompress = false;
							} else {
								$data->precompress = true;
							}
						}	
						$model->setCompression($data->precompress); 
					}
					if(isset($data->real_orientation)){
						if(!is_bool($data->real_orientation)){
							if($data->real_orientation == 'true'){
								$data->real_orientation = true;
							} else if($data->real_orientation == 'false'){
								$data->real_orientation = false;
							} else {
								$data->real_orientation = true;
							}
						}
						$model->setRealOrientation($data->real_orientation); 
					}
					if($model->save()){
						//$parent
						$success = true;
						/*
							NOTE: The use of linking the id immediatly to the parent is that we can quicker display on front, on JS we do have to update the parent once the upload is finish (it's also safer).
							But in another hand it request one more field on parent to not forgot, we are not in a system with complex relationship, we can allow it for now.
						*/
						if(isset($data->parent_file_id) && is_string($data->parent_file_id) && array_key_exists($data->parent_file_id, $parent->getAttributes())){
							$parent->{$data->parent_file_id} = $model->id;
							if(!$parent->save()){
								$success = false;
								break;
							}
						}
						if($success){
							$result->read->file->{$model->id} = $model->getNoSQL();
							if(!isset($result->read->{$data->parent_type})){ $result->read->{$data->parent_type} = new \stdClass; }
							$result->read->{$data->parent_type}->{$parent->id} = $parent->getNoSQL();
						}
					} else {
						$success = false;
						break;
					}
				}
			}
		} else {
			$errmsg = $failmsg.$app->trans->getBRUT('api', 3, 2); //No file selected to upload.
		}

		if($success){
			$msg = array('data' => $result, 'msg' => $app->trans->getBRUT('api', 11, 2)); //File uploaded.
			(new Json($msg))->render();
			return exit(0);
		} else {
			if(isset($_FILES)){
				\libs\Watch::php(array_merge((array) $data, $_FILES), 'Upload failed', __FILE__, __LINE__, true);
			} else {
				\libs\Watch::php($data, 'Upload failed (no file)', __FILE__, __LINE__, true);
			}
		}

		//We have to use Json object to be able to get back the message attached
		$msg = array('msg' => $errmsg);
		(new Json($msg, true, 401, true))->render();
		return exit(0);
	}

	public function qrcode_get(){
		$app = ModelBruno::getApp();
		ob_clean();
		flush();
		$user_id = $app->bruno->data['user_id'];
		$user = User::getUser();
		$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_HOST'].'/uid/'.Datassl::encrypt($user->id, 'invitation');
		$timestamp = $user->c_at->timestamp; 
		$gmt_mtime = gmdate('r', $timestamp);
		header('Last-Modified: '.$gmt_mtime);
		header('Expires: '.gmdate(DATE_RFC1123, time()+16000000)); //About 6 months cached
		header('ETag: "'.md5($user_id.'-'.$timestamp).'"');
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($user_id.'-'.$timestamp)) {
				header('HTTP/1.1 304 Not Modified');
				return exit(0);
			}
		}

		//https://packagist.org/packages/endroid/qr-code
		$qrCode = new QrCode();

		$folder = new Folders;
		$folder->createPath($app->bruno->filePath.'/'.$app->bruno->data['user_id'].'/qrcode/');

		$exists = false;
		$basename = $_SERVER['REQUEST_SCHEME'].'-'.$_SERVER['SERVER_HOST'].'-';

		$path = $folder->getPath().$basename.'qrcode.png';
		if(is_file($path)){
			$exists = true;
		}
		
		if(!$exists){
			$qrCode
				->setText($url)
				->setSize(640)
				->setPadding(20)
				->setErrorCorrection('medium')
				->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
				->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
				->setImageType(QrCode::IMAGE_TYPE_PNG)
			;
			header('Content-Type: '.$qrCode->getContentType());
			$qrCode->save($path);
			$qrCode->render();
		} else {
			WideImage::load($path)->output('png');
		}
		
		return exit(0);
	}

	public function open_get($type, $md5, $id){
		$app = ModelBruno::getApp();
		ob_clean();
		flush();
		$width = 200;
		$height = 200;
		$scale = false;
		$access = false;
		if($file = File::Where('id', $id)->where('md5', $md5)->first()){
			$content_type = 'application/force-download';
			if($file->progress<100 && $file->category=='video'){
				$file->checkProgress();
			}
			if($type=='thumbnail' && is_null($file->thu_type)){ //If the thumbnail is not available we link
				$type = 'link';
			}
			if($type=='link' && $file->category=='file'){ //If it's a common file, we force to download
				$type = 'download';
			}
			$name = $file->name;
			if($type=='download'){
				$path = $app->bruno->filePath.'/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
				$path_xsend = '/protected_files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
				if($file->progress<100 && $file->category=='video'){
					$path = $app->bruno->path.'/bundles/bruno/api/public/images/generic/mp4.png';
					$path_xsend = false;
					$name = 'converting.png';
				}
				if(is_file($path) && filesize($path)!==false){
					//note that the root and internal redirect paths are concatenated.
					//https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/
					header('Content-Description: File Transfer');
					header('Content-Type: attachment/force-download;');
					header('Content-Transfer-Encoding: binary');
					$content_type = $file->ori_type;
					header('Content-Type: '.$content_type.';');
					header('Content-Disposition: attachment; filename="'.$name.'"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					$size = filesize($path);
					header('Content-Length: '.$size);
					header('Content-Range: bytes 0-'.($size-1).'/'.$size);
					if($path_xsend){
						header('X-Accel-Redirect: '.$path_xsend);
					} else {
						readfile($path);
					}
					return exit(0);
				}
			} else if($type=='link' || $type=='thumbnail'){
				if($type=='thumbnail'){
					$path = $app->bruno->filePath.'/'.$file->uploaded_by.'/thumbnail/'.$file->link.'.'.$file->thu_ext;
					$path_xsend = '/protected_files/'.$file->uploaded_by.'/thumbnail/'.$file->link.'.'.$file->thu_ext;
					if($file->ori_type=='image/gif'){ //It will keep the animation if any
						$path = $app->bruno->filePath.'/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
						$path_xsend = '/protected_files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
					}
					$content_type = $file->thu_type;
					$name = pathinfo($path, PATHINFO_FILENAME).'.'.$file->ori_thu;
				} else {
					$path = $app->bruno->filePath.'/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
					$path_xsend = '/protected_files/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
					if($file->category=='video'){
						$path_xsend = '/protected_videos/'.$file->uploaded_by.'/'.$file->link.'.'.$file->ori_ext;
					}
					$content_type = $file->ori_type;
				}
				if(is_file($path) && filesize($path)!==false){
					//http://stackoverflow.com/questions/2000715/answering-http-if-modified-since-and-http-if-none-match-in-php/2015665#2015665
					$timestamp = filemtime($path); 
					$gmt_mtime = gmdate('r', $timestamp);
					header('Last-Modified: '.$gmt_mtime);
					header('Expires: '.gmdate(DATE_RFC1123,time()+16000000)); //About 6 months cached
					header('ETag: "'.md5($timestamp.$path).'"');
					if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
						if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp.$path)) {
							header('HTTP/1.1 304 Not Modified');
							return exit(0);
						}
					}
					header('Content-Type: '.$content_type.';');
					header('Content-Disposition: inline; filename="'.$name.'"');
					header('Pragma: public');
					$size = filesize($path);
					header('Content-Length: '.$size);
					header('Content-Range: bytes 0-'.($size-1).'/'.$size);
					if($path_xsend){
						header('X-Accel-Redirect: '.$path_xsend);
					} else {
						readfile($path);
					}
					return exit(0);
				}
			}
		} else {
			\libs\Watch::php("md5: $md5\ntype: $type\nid: $id", 'File not avalable', __FILE__, __LINE__, true);
		}
		
		return self::unavailable();
	}

	protected static function unavailable(){
		$app = ModelBruno::getApp();
		$path = $app->bruno->path.'/bundles/bruno/api/public/images/generic/unavailable.png';
		$timestamp = filemtime($path); 
		$gmt_mtime = gmdate('r', $timestamp);
		header('Last-Modified: '.$gmt_mtime);
		header('Expires: '.gmdate(DATE_RFC1123,time()+3000000)); //About 1 month
		header('ETag: "'.md5($timestamp.$path).'"');
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp.$path)) {
				header('HTTP/1.1 304 Not Modified');
				return exit(0);
			}
		}
		$src = WideImage::load($path);
		$white = $src->allocateColor(255, 255, 255);
		$src = $src->resizeCanvas(200, 200, 'center', 'center', $white);
		$src->output('png');
		return exit(0);
	}

	//This is a process to run in background, so we are not expecting an output
	public function progress_post($md5, $id){
		if($file = File::withTrashed()->where('id', $id)->where('md5', $md5)->first()){
			$file->setProgress();
		}
		return exit(0);
	}

}
