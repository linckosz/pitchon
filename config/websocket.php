<?php

use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\websocket\WebSocket;
use \bundles\bruno\data\models\websocket\WSsession;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$path = $_SERVER['DOCUMENT_WWW'];

require_once $path.'/vendor/autoload.php';

$app = new \Slim\Slim();

require_once $path.'/config/global.php';
require_once $path.'/config/language.php';
require_once $path.'/param/config.php';

//Add manually all databases
$app->bruno->databases['api'] = true;
$app->bruno->databases['data'] = true;

require_once $path.'/param/unique/env.php';

$app->config(array(
	'log.enable' => false,
));
ini_set('display_errors', '1');
ini_set('opcache.enable', '0');

require_once $path.'/error/errorPHP.php';
require_once $path.'/config/eloquent.php';

$app->group('/api/websocket', function() use ($app) {

	$app->post('/sessionold', function($ip = null, $hostname = null, $deployment = null) use ($app) {
		$app = ModelBruno::getApp();

		proc_nice(10); //Low priority run
		$wsserver = new WsServer(new WSsession());
		$server = IoServer::factory(
			new HttpServer(
				$wsserver
			),
			8080
		);
		//https://blogs.msdn.microsoft.com/whereismysolution/2018/03/12/does-system-net-websockets-includes-a-keepalive-mechanism-which-automatically-takes-care-of-pingpong-control-frames/
		$wsserver->enableKeepAlive($server->loop, 30);
		$server->run();
		
		return exit(0);
	})
	->name('api_websocket_session_post');



	$app->post('/session', function($ip = null, $hostname = null, $deployment = null) use ($app) {
		$app = ModelBruno::getApp();

		$loop   = React\EventLoop\Factory::create();
		$pusher = new WebSocket();

		// Listen for the web server to make a ZeroMQ push after an ajax request
		$context = new React\ZMQ\Context($loop);
		$pull = $context->getSocket(ZMQ::SOCKET_PULL);
		$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
		$pull->on('message', array($pusher, 'onBlogEntry'));

		// Set up our WebSocket server for clients wanting real-time updates
		$webSock = new React\Socket\Server('0.0.0.0:8080', $loop); // Binding to 0.0.0.0 means remotes can connect
		$webServer = new Ratchet\Server\IoServer(
			new Ratchet\Http\HttpServer(
				new Ratchet\WebSocket\WsServer(
					new Ratchet\Wamp\WampServer(
						$pusher
					)
				)
			),
			$webSock
		);

		$loop->run();
		
		return exit(0);
	})
	->name('api_websocket_start_post');




});

$app->map('/:catchall', function() use ($app) {
	echo 'Page not found';
})->conditions(array('catchall' => '.*'))
->name('catchall')
->via('GET', 'POST', 'PUT', 'DELETE');

$app->run();
