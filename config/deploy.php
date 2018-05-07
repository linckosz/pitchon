<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use \libs\TranslationModel;
use \libs\Datassl;
use \libs\Folders;
use \libs\Version;

$path = $_SERVER['DOCUMENT_WWW'];

require_once $path.'/vendor/autoload.php';

$app = new \Slim\Slim();

require_once $path.'/config/global.php';
require_once $path.'/config/language.php';
require_once $path.'/param/config.php';

//Add manually all databases
$app->bruno->databases['api'] = true;
$app->bruno->databases['app'] = true;
$app->bruno->databases['data'] = true;
$app->bruno->databases['quiz'] = true;
$app->bruno->databases['remote'] = true;
$app->bruno->databases['screen'] = true;
$app->bruno->databases['wrapper'] = true;
$app->bruno->databases['www'] = true;

require_once $path.'/param/unique/env.php';

$app->config(array(
	'log.enable' => false,
));
ini_set('display_errors', '1');
ini_set('opcache.enable', '0');

require_once $path.'/error/errorPHP.php';
require_once $path.'/config/eloquent.php';

$app->get('/get/:ip/:hostname/:deployment', function($ip = null, $hostname = null, $deployment = null) use ($app) {
	
	$version = Version::find(1);
	if(!$version){
		$version = new Version;
		$version->id = 1;
	}
	$version->version = md5(uniqid('', true));
	$version->save();

	$list = array();
	foreach ($app->bruno->databases as $bundle => $value) {
		if(Capsule::schema($bundle)->hasTable('translation')){
			$list[$bundle] = TranslationModel::on($bundle)->get()->toArray();
		}
	}

	$domain = $_SERVER['HTTP_HOST'];
	if(strpos($domain, ':')){
		$domain = strstr($domain, ':', true);
	}
	//PASSWORD_DEFAULT
	if( !password_verify($deployment, '$2y$10$GMcS920.m8T49taFatUYbOSmr4zP0t4LfWiBbCp5A4DkrXWIXbTv6') ){
		echo "You are not authorized to modify the translation database\n";
		return true;
	}
	echo "Get the translation data [$domain]\n";

	$data = json_encode(array(
		'translation' => $list,
		'deployment' => $deployment,
		'git' => $version->version,
	));
	$ch = curl_init($ip.':38890/update');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=UTF-8',
			'Content-Length: ' . mb_strlen($data),
			'Host: api.'.$hostname,
		)
	);

	$verbose = fopen('php://temp', 'w+');
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_STDERR, $verbose);
	
	if($result = curl_exec($ch)){
		\libs\Watch::php(curl_getinfo($ch), '$ch', __FILE__, __LINE__, false, false, true);
		\libs\Watch::php(stream_get_contents($verbose), '$verbose', __FILE__, __LINE__, false, false, true);
		echo $result;
	} else {
		echo "cURL error!\n";
		\libs\Watch::php(curl_getinfo($ch), '$ch', __FILE__, __LINE__, false, false, true);
		$error = '['.curl_errno($ch)."] => ".htmlspecialchars(curl_error($ch));
		\libs\Watch::php($error, '$error', __FILE__, __LINE__, false, false, true);
		rewind($verbose);
		\libs\Watch::php(stream_get_contents($verbose), '$verbose', __FILE__, __LINE__, false, false, true);
		fclose($verbose);
	}
	
	@curl_close($ch);

	echo "DONE\n";
})
->conditions(array(
	'ip' => '(?:[0-9]{1,3}\.){3}[0-9]{1,3}',
	'hostname' => '([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}',
	'deployment' => '\w+',
))
->name('get_translation_data');

$app->post('/update', function() use ($app) {
	$domain = $_SERVER['HTTP_HOST'];
	if(strpos($domain, ':')){
		$domain = strstr($domain, ':', true);
	}
	$data = json_decode($app->request->getBody());
	$app->bruno->deployment = $data->deployment;
	//PASSWORD_DEFAULT
	if( !password_verify($app->bruno->deployment, '$2y$10$GMcS920.m8T49taFatUYbOSmr4zP0t4LfWiBbCp5A4DkrXWIXbTv6') ){
		echo "You are not authorized to modify the translation database\n";
		return true;
	}
	echo "Update the translation data [$domain] => \n";
	$translation = $data->translation;
	foreach ($translation as $bundle => $items) {
		foreach ($items as $item) {
			if($sentence = TranslationModel::on($bundle)->where('category', $item->category)->where('phrase', $item->phrase)->first()){
				//If The sentence already exists
				foreach ($item as $key => $attribute) {
					$sentence->$key = $attribute;
				}
				$dirty = $sentence->getDirty();
				if(count($dirty) > 0){
					foreach ($dirty as $value) {
						if($value){ //Check that there is a value inside the 
							$original = $sentence->getOriginal();
							if($sentence->querySave()){
								$str_dirty = preg_replace( "/\r|\n/", "\\n", json_encode($dirty, JSON_UNESCAPED_UNICODE) );
								$str_original = preg_replace( "/\r|\n/", "\\n", json_encode($original, JSON_UNESCAPED_UNICODE) );
								echo "  - [FROM]: $str_original\n";
								echo "  - [ TO ]: $str_dirty\n\n";
							}
							break;
						}
					}
				}
			} else {
				//New sentence
				if(TranslationModel::queryInsert($bundle, $item)){
					$str_new = preg_replace( "/\r|\n/", "\\n", json_encode($item, JSON_UNESCAPED_UNICODE) );
					echo "  - {NEW} : $str_new\n\n";
				}
			}
		}
	}
	
	$version = Version::find(1);
	if(!$version){
		$version = new Version;
		$version->id = 1;
	}
	$version->version = $data->git;
	$version->save();
	
	echo "Translation ok\n";
})
->name('update_translation_data');

$app->map('/:catchall', function() use ($app) {
	echo 'Page not found';
})->conditions(array('catchall' => '.*'))
->name('catchall')
->via('GET', 'POST', 'PUT', 'DELETE');

$app->run();
//Checking $app (print_r) after run can make php crashed out of memory because it contains files data
