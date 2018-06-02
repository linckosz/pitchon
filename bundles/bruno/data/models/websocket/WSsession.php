<?php

namespace bundles\bruno\data\models\websocket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class WSsession implements WampServerInterface {
	
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
		// If the lookup topic object isn't set, there is no one to publish to
		if (!array_key_exists($entryData['topicid'], $this->subscribedTopics)) {
			return;
		}
		$topic = $this->subscribedTopics[$entryData['topicid']];
		// Send the data to all clients who subscribed to that topic
		$topic->broadcast($entryData);
	}
	
}
