<?php

namespace libs;

use \Exception;
use \Slim\Http\Util;

class Vanquish {

	//This variable keep track of all cookies set and then run setCookie by a hook function at alim.after
	protected static $cookies = array();

	//Help to not run many time the data fetching for cookies
	protected static $first = true;

	//Tell if there is any change. Only if yes, we modify the cookie
	protected static $change = false;

	//We set values in memory first, then at slim.after we set the cookie
	public static function set($array){
		$app = \Slim\Slim::getInstance();
		if(!is_array($array) && !empty($array)){
			throw new Exception("Vanquish::set => The variable must be an array not empty.");
			return false;
		}
		self::getCookies();
		//Add or Update key/value
		foreach($array as $key => $value) {
			self::$cookies[$key] = $value;
			$_SESSION['vanquish'][$key] = $value;
		}
		self::$change = true;
		return true;
	}

	public static function get($key){
		$app = \Slim\Slim::getInstance();
		if(!is_string($key) && !empty($key)){
			throw new Exception("Vanquish::get => The variable must be a string not empty.");
			return null;
		}
		self::getCookies();
		if(isset(self::$cookies[$key])){
			return self::$cookies[$key];
		}
		return null;
	}

	public static function getCookies(){
		//Assign only once all real cookies to memory
		if(self::$first || count(self::$cookies)<=0){
			$app = \Slim\Slim::getInstance();
			if($vanquish = $app->getCookie($app->bruno->data['bruno_dev'].'_vanquish', false)){
				$vanquish = json_decode($vanquish);
				foreach($vanquish as $key => $value) {
					if(!isset(self::$cookies[$key])){
						self::$cookies[$key] = $value;
					}
				}
			}
			if(isset($_SESSION['vanquish'])){
				foreach($_SESSION['vanquish'] as $key => $value) {
					//The session overwrite cookies
					self::$cookies[$key] = $value;
				}
			}
			if(isset($_SESSION)){
				//Stop checking only once the session (=database) is available
				self::$first = false;
			}
			return self::$cookies;
		}
		return false;
	}

	public static function unsetKey($key){
		$app = \Slim\Slim::getInstance();
		if(!is_string($key) && !empty($key)){
			throw new Exception("Vanquish::unsetKey => The variable must be a string not empty.");
			return false;
		}
		self::getCookies();
		unset(self::$cookies[$key]);
		if(isset($_SESSION['vanquish'])){
			unset($_SESSION['vanquish'][$key]);
		}
		self::$change = true;
		return true;
	}

	public static function unsetAll($array_exception = array()){
		if(!is_array($array_exception)){
			throw new Exception("Vanquish::unsetAll => The variable must be an array.");
			return false;
		}
		foreach(self::$cookies as $key => $value) {
			if(!in_array($key, $array_exception)){
				unset(self::$cookies[$key]);
			}
		}
		if(isset($_SESSION['vanquish'])){
			foreach($_SESSION['vanquish'] as $key => $value) {
				if(!in_array($key, $array_exception)){
					unset($_SESSION['vanquish'][$key]);
				}
			}
		}
		self::$change = true;
		return true;
	}

	//To be launch after we finish to set all cookies value, at slim.after as a hook
	public static function setCookies(){
		if(self::$change){
			$app = \Slim\Slim::getInstance();
			//Re/create the cookie
			if(!empty(self::$cookies)){
				$_SESSION['vanquish'] = self::$cookies;
				//Do no use " $app->setCookie('vanquish', json_encode(self::$cookies)); " avoid cookie issue that keep its status accross non-ssl and ssl site and different subdomains
				$json = self::encodeSecureCookie(json_encode(self::$cookies), $app->bruno->cookies_lifetime);
				setcookie($app->bruno->data['bruno_dev'].'_vanquish', $json, $app->bruno->cookies_lifetime, '/', '.'.$app->bruno->http_host);
			} else {
				unset($_SESSION['vanquish']);
				$app->deleteCookie($app->bruno->data['bruno_dev'].'_vanquish');
				setcookie($app->bruno->data['bruno_dev'].'_vanquish', null, time()-3600, '/', '.'.$app->bruno->http_host);
				self::$first = true;
			}
		}
		return true;
	}

	public static function encodeSecureCookie($data, $expire){
		$app = \Slim\Slim::getInstance();
		$data = Util::encodeSecureCookie(
			$data,
			$expire,
			$app->config('cookies.secret_key'),
			$app->config('cookies.cipher'),
			$app->config('cookies.cipher_mode')
		);
		return $data;
	}

}
