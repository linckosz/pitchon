<?php

//http://twig.sensiolabs.org/doc/advanced.html#id2

namespace libs;

use \libs\File;
use \libs\Folders;

class Twig_Extension extends \Slim\Views\TwigExtension {

	public function getName(){
		return 'Bruno';
	}

	public function get_Debug($data = NULL){
		$app = \Slim\Slim::getInstance();
		include($app->bruno->path.'/error/debug.php');
		return NULL;
	}

	public function get_Require($path){
		require_once($path);
		return NULL;
	}

	public function get_Filelatest($file){
		return File::getLatest($file);
	}

	public function get_Grouplatest($name, $group){
		return File::getGroupLatest($name, $group);
	}

	//Get the translation
	public function get_Trans($bundle, $category, $phrase, $type = NULL, array $data = array()){
		$app = \Slim\Slim::getInstance();
		if(mb_strtolower($type)=='input'){
			return $app->trans->getINPUT($bundle, $category, $phrase, $data);
		} else if(mb_strtolower($type)=='textarea'){
			return $app->trans->getTEXTAREA($bundle, $category, $phrase, $data);
		} else if(mb_strtolower($type)=='js'){
			return $app->trans->getJS($bundle, $category, $phrase, $data);
		} else if(mb_strtolower($type)=='html'){
			return $app->trans->getHTML($bundle, $category, $phrase, $data);
		} else if(mb_strtolower($type)=='json'){
			return $app->trans->getJSON($bundle, $category, $phrase, $data);
		} else if(mb_strtolower($type)=='brut'){
			return $app->trans->getBRUT($bundle, $category, $phrase, $data);
		}
		return $app->trans->getHTML($bundle, $category, $phrase, $data);
	}

	public function get_Languages($bundle = NULL){
		$app = \Slim\Slim::getInstance();
		return $app->trans->getLanguages($bundle);
	}

	public function get_Languages_short($bundle = NULL){
		$app = \Slim\Slim::getInstance();
		return $app->trans->getLanguagesShort($bundle);
	}

	public function get_Language(){
		$app = \Slim\Slim::getInstance();
		return $app->trans->setLanguage();
	}

	public function get_Language_full(){
		$app = \Slim\Slim::getInstance();
		return $app->trans->getClientLanguageFull();
	}

	public function get_Route(){
		$app = \Slim\Slim::getInstance();
		return $app->request->getPath();
	}

	public function get_Route_name(){
		$app = \Slim\Slim::getInstance();
		$route = $app->router->getMatchedRoutes($app->request->getMethod(), $app->request->getResourceUri());
		if (is_array($route) && count($route) > 0) {
			$route = $route[0];
		}
		if($route){
			return $route->getName();
		}
		return false;
	}

	public function get_TranslationUri(){
		$app = \Slim\Slim::getInstance();
		return '/language'.$app->request->getPath();
	}

	public function get_Loop_public_folder($directory){
		$app = \Slim\Slim::getInstance();
		$directory = $app->bruno->path.'/public'.$directory;
		$folder = new Folders($directory);
		return $folder->loopFolder();
	}

	public function get_Loop_twig_folder($bundle, $directory){
		$app = \Slim\Slim::getInstance();
		$path = $app->bruno->path.'/bundles/'.$bundle.'/templates'.$directory;
		$folder = new Folders($path);
		$files = array();
		foreach($folder->loopFolder() as $file) {
			if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'twig'){
				$files[] = '/bundles/'.$bundle.'/templates'.$directory.'/'.$file;
			}
		}
		return $files;
	}

	public function getFunctions(){
		return array_merge(
			parent::getFunctions(),
			array(
				new \Twig_SimpleFunction('_debug', array($this, 'get_Debug'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_require', array($this, 'get_Require'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_trans', array($this, 'get_Trans'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_filelatest', array($this, 'get_Filelatest'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_grouplatest', array($this, 'get_Grouplatest'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_languages', array($this, 'get_Languages'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_languages_short', array($this, 'get_Languages_short'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_language', array($this, 'get_Language'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_language_full', array($this, 'get_Language_full'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_route', array($this, 'get_Route'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_route_name', array($this, 'get_Route_name'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_language_uri', array($this, 'get_TranslationUri'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_loop_public_folder', array($this, 'get_Loop_public_folder'), array('is_safe' => array('html'))),
				new \Twig_SimpleFunction('_loop_twig_folder', array($this, 'get_Loop_twig_folder'), array('is_safe' => array('html'))),
			)
		);
	}

	public function getFilters(){
		return array_merge(
			parent::getFunctions(),
			array(
				new \Twig_SimpleFilter('ucfirst', 'ucfirst'),
			)
		);
	}

}
