<?php

namespace bundles\bruno\data\models\websocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use \bundles\bruno\data\models\data\Session;

//http://socketo.me/docs/hello-world
class WSsession implements MessageComponentInterface {

	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn){
		//\libs\Watch::php('New connection! ({'.$conn->resourceId.'})', 'onOpen', __FILE__, __LINE__, false, false, true);
		// Store the new connection to send messages to later
		$this->clients->attach($conn);
	}

	public function onMessage(ConnectionInterface $from, $msg){
		$numRecv = count($this->clients) - 1;
		//\libs\Watch::php(sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's'), 'onMessage', __FILE__, __LINE__, false, false, true);
		foreach ($this->clients as $client) {
			if ($from !== $client) {
				// The sender is not the receiver, send to each client connected
				$client->send($msg);
			}
		}
	}

	public function onClose(ConnectionInterface $conn){
		// The connection is closed, remove it, as we can no longer send it messages
		//\libs\Watch::php('Connection {'.$conn->resourceId.'} has disconnected', 'onClose', __FILE__, __LINE__, false, false, true);
		$this->clients->detach($conn);
	}

	public function onError(ConnectionInterface $conn, \Exception $e){
		//\libs\Watch::php('An error has occurred: {'.$e->getMessage().'}', '$onError', __FILE__, __LINE__, false, false, true);
		$conn->close();
	}

}
