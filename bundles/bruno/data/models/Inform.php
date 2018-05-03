<?php

namespace bundles\bruno\data\models;

use \bundles\bruno\data\models\ModelBruno;

class Inform {

	protected $content = '';

	protected $item = false;

	protected $sha = array();

	public function __construct($content, array $sha, $item=false){
		$app = ModelBruno::getApp();

		$this->content = $content;
		$this->item = $item;
		$this->sha = $sha;

		return true;
	}

	public function send(){
		
		//Send to websocket

	}

}
