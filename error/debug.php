<?php
/*
	Write here anything you need as debugging information to be display on main page
	For twig display use: {{ _debug() }} or {{ _debug(data) }}
	For php display use: include($app->bruno->path.'/error/debug.php');
	Or simply open the link http://{domain}/debug

	To get data
	print_r($data);

	Then open the link (change the domain name according to dev(.net)/stage(.co)/production(.com) server)
	https://bruno.co/wrapper/debug
*/

$app = \Slim\Slim::getInstance();
//print_r($data);
//phpinfo();

$entryData = array(
        'category' => 'kittensCategory'
      , 'title'    => 'title'
      , 'article'  => 'article'
      , 'when'     => time()
    );



    // This is our new stuff
    $context = new \ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://127.0.0.1:5555");
\libs\Watch::php(true, '$var', __FILE__, __LINE__, false, false, true);
    $a = $socket->send(json_encode($entryData));

\libs\Watch::php($a, '$var', __FILE__, __LINE__, false, false, true);
