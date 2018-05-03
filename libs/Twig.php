<?php

namespace libs;

class Twig extends \Slim\Views\Twig {

	public function render($template, $data = null){
		$app = \Slim\Slim::getInstance();
		$data = array_merge($app->bruno->data, (array) $data);
		return parent::render($template, $data);
	}

}
