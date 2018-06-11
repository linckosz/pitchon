<?php

use \bundles\bruno\data\models\ModelBruno;
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

	$app->post('/session', function($ip = null, $hostname = null, $deployment = null) use ($app) {
		$app = ModelBruno::getApp();

		$loop   = React\EventLoop\Factory::create();
		$pusher = new WSsession();

		// Listen for the web server to make a ZeroMQ push after an ajax request
		$context = new React\ZMQ\Context($loop);
		$pull = $context->getSocket(\ZMQ::SOCKET_PULL, 'session_code');
		try {
			$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
		} catch (\Exception $e){
			//Exception: Failed to bind the ZMQ: Address already in use
			return exit(0);
		}
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
	->name('api_websocket_session_post');

});

$app->map('/:catchall', function() use ($app) {
	echo 'Page not found';
})->conditions(array('catchall' => '.*'))
->name('catchall')
->via('GET', 'POST', 'PUT', 'DELETE');

$app->run();
