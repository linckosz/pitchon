<?php

namespace param;

////////////////////////////////////
// FOLDER PERMISSIONS
////////////////////////////////////
/*
cd /path/to/appli
chown -R apache:apache logs
chown -R apache:apache public
*/

////////////////////////////////////
// CALLBACK ORDER
////////////////////////////////////
/*
Before run (no $app | no environment)
MyMiddleware Before (with $app | no environment)
slim.before (with environment)
MyMiddleware After
slim.before.router
slim.before.dispatch
Before render
[render]
After render
slim.after.dispatch
slim.after.router (before buffer rendering)
slim.after (after buffer rendering)
After run
*/

////////////////////////////////////
// DEFAULT SETTING
////////////////////////////////////

//Create a default class to store special data
$app->bruno = new \stdClass;

//Used to track operation time
$app->bruno->time_record = false; //Turn at true to track and \time_checkpoint('ok');
$app->bruno->time_start = 0;

//Application title
$app->bruno->title = 'PitchOn';

//Domain name
if(isset($_SERVER['SERVER_HOST'])){
	$app->bruno->domain = $_SERVER['SERVER_HOST'];
} else if(strpos($_SERVER['HTTP_HOST'], ':')){
	$app->bruno->domain = strstr($_SERVER['HTTP_HOST'], ':', true);
} else {
	$app->bruno->domain = $_SERVER['HTTP_HOST'];
}

if(!isset($_SERVER['SERVER_ADMIN'])){
	$_SERVER['SERVER_ADMIN'] = 'webmaster@pitchon.net';
}
if(!isset($_SERVER['SERVER_HOST'])){
	$_SERVER['SERVER_HOST'] = 'pitchon.net';
}
if(!isset($_SERVER['BRUNO_DEV'])){
	$_SERVER['BRUNO_DEV'] = '';
}
if(!isset($_SERVER['BRUNO_BUNDLE']) || empty($_SERVER['BRUNO_BUNDLE'])){
	$_SERVER['BRUNO_BUNDLE'] = 'app';
}

$app->bruno->http_host = $_SERVER['HTTP_HOST'];

//$app->bruno->cookies_lifetime = time()+1200; //Valid 20 minutes
$app->bruno->cookies_lifetime = time()+(3600*24*90); //Valid 3 months

//Do not enable debug when we are using json ajax respond
$app->config(array(
	'debug' => false,
	'mode' => 'production',
	'cookies.encrypt' => true, //Must use $app->getCookie('foo', false);
	'cookies.secret_key' => 'au6G7dbSh87Ws',
	'cookies.lifetime' => $app->bruno->cookies_lifetime,
	'cookies.secure' => false, //At true it keeps record only on SSL connection
	'cookies.path' => '/',
	'cookies.httponly' => true,
	'templates.path' => '..',
	'debug' => false,
));

//Root directory (which is different from landing page which is in public folder)
$app->bruno->path = $_SERVER['DOCUMENT_WWW'];

//Insure the the folder is writable by chown apache:apache slim.api/logs and is in share(=writable) path in gluster mode.
//chown apache:apache /path/to/applilogs
$app->bruno->logPath = $app->bruno->path.'/logs';

//Insure the the folder is writable by chown apache:apache slim.api/public and is in share(=writable) path in gluster mode.
//chown apache:apache /path/to/applipublic
$app->bruno->publicPath = $app->bruno->path.'/public';

//For uploading
//$app->bruno->filePath is redefined in parameters.php
$app->bruno->filePath = $app->bruno->publicPath.'/upload';

//False if we want to use Slim error display, use True for json application
$app->bruno->jsonException = false;

$app->bruno->enableSession = true;
$app->bruno->session = array(); //Used to edit and keep some session variable value before session_start command

//Use true for development to show error message on browser screen
//Do not allow that for production, in case of any single bug, all users will see the message
$app->bruno->showError = false;

//List all bundles to load (routes are loaded in the order of appearance)
$app->bruno->bundles = array(
	//'bundle name'
	'bruno/wrapper', //Must for front end server
	'bruno/data',
);

//Selection of database used
$app->bruno->databases = array(
	'wrapper' => true,
	'data' => true,
);

//List all middlewares to load in the order of appearance
$app->bruno->middlewares = array_reverse(array(
	//Full path of classes (including namespace)
	//['bundle name', 'subfolder\class name'],
	['bruno/wrapper', 'Twig'],
));

//List all hooks to load in the order of appearance and pound
$app->bruno->hooks = array(
	//Full path of classes (including namespace)
	//['bundle name', 'subfolder\function name', 'the.hook.name', priority value],
	['bruno/wrapper', 'SetData', 'slim.before', 10],
	['bruno/wrapper', 'SetCookies', 'slim.after.router', 10],
);

//Class with email default parameters, it use local Sendmail.postfix function
$app->bruno->email = new \stdClass;
$app->bruno->email->CharSet = 'utf-8';
$app->bruno->email->Abuse = 'abuse@'.$app->bruno->domain;
$app->bruno->email->Sender = 'noreply@'.$app->bruno->domain;
$app->bruno->email->From = 'noreply@'.$app->bruno->domain;
$app->bruno->email->FromName = $app->bruno->title.' server';
$app->bruno->email->Port = 587;
$app->bruno->email->Host = 'localhost';
$app->bruno->email->List = array();

//Translator parameters
//microsoft@bruno.com/ lin**2**5**@#
$app->bruno->translator = array(
	'text_key1' => '8b5032784084462c97cfe442cf489577',
);

//Translation list
$app->bruno->translation = array(
	'domain' => $app->bruno->domain,
	'title' => $app->bruno->title,
);

//Some generic data for translation twig
$app->bruno->data = array(
	'user_id' => false,
	'guest_id' => false,
	'domain' => $app->bruno->domain,
	'http_host' => $app->bruno->http_host,
	'title' => $app->bruno->title,
	'support' => 'support@pitchon.net',
	'bruno_dev' => $_SERVER['BRUNO_DEV'],
	'bruno_show_dev' => 'false', //Display some error for developpers on JS (NOTE: it has to be a string because of Twig conversion to JS)
);

//Messages to be sent along with rendering
$app->bruno->flash = array();

$app->bruno->security = array(
	'expired' => '7200', //Expiration time in seconds (2H)
);

$app->bruno->method_suffix = '_'.strtolower($app->request->getMethod());


$app->bruno->bundle = $_SERVER['BRUNO_BUNDLE'];
//CUSTOMIZATION
if($app->bruno->bundle == 'api'){
	$app->bruno->bundles[] = 'bruno/api';
	$app->bruno->databases['api'] = true;
} else if($app->bruno->bundle == 'www'){
	$app->bruno->bundles[] = 'bruno/www';
	$app->bruno->databases['www'] = true;
} else if($app->bruno->bundle == 'app'){
	$app->bruno->bundles[] = 'bruno/app';
	$app->bruno->databases['app'] = true;
} else if($app->bruno->bundle == 'screen'){
	$app->bruno->bundles[] = 'bruno/screen';
	$app->bruno->databases['screen'] = true;
} else if($app->bruno->bundle == 'quiz'){
	$app->bruno->bundles[] = 'bruno/quiz';
	$app->bruno->databases['quiz'] = true;
	$app->bruno->cookies_lifetime = time()+(3600*24*365*2); //Valid 2 years
	$app->config(array(
		'cookies.lifetime' => $app->bruno->cookies_lifetime,
	));
} else if($app->bruno->bundle == 'remote'){
	$app->bruno->bundles[] = 'bruno/remote';
	$app->bruno->databases['remote'] = true;
}
