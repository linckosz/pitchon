<?php

namespace bundles\bruno\data\models\websocket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class WebSocket implements WampServerInterface {

	// A lookup of all the topics clients have subscribed to
	protected $subscribedTopics = array();

	public function onSubscribe(ConnectionInterface $conn, $topic) {
		$this->subscribedTopics[$topic->getId()] = $topic;
	}

	public function onUnSubscribe(ConnectionInterface $conn, $topic) {
	}

	public function onOpen(ConnectionInterface $conn) {
	}

	public function onClose(ConnectionInterface $conn) {
	}

	public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
	}

	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
	}



	 /**
	 * @param string JSON'ified string we'll receive from ZeroMQ
	 */
	public function onBlogEntry($entry) {
		$entryData = json_decode($entry, true);
\libs\Watch::php($entryData, '$var', __FILE__, __LINE__, false, false, true);
		// If the lookup topic object isn't set there is no one to publish to
		if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
			return;
		}

		$topic = $this->subscribedTopics[$entryData['category']];

		// re-send the data to all the clients subscribed to that category
		$topic->broadcast($entryData);
	}

}
