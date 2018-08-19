<?php

namespace bundles\bruno\wrapper\hooks;

use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\Guest;
use \bundles\bruno\data\models\data\User;
use \libs\Vanquish;

function checkRoute(){
	$app = ModelBruno::getApp();

	$route = $app->router->getMatchedRoutes($app->request->getMethod(), $app->request->getResourceUri());
	if (is_array($route) && count($route) > 0) {
		$route = $route[0];
	}
	
	if($route){
		return $route->getName();
	}
	return false;
}

function SetData(){
	$app = ModelBruno::getApp();

	$data = json_decode($app->request->getBody());
	if(!$data){
		if($app->bruno->method_suffix=='_get' && $get = (object) $app->request->get()){
			$data = $get;
		} else if($app->bruno->method_suffix=='_post' && $post = (object) $app->request->post()){
			$data = $post;
		}
	}
	$data = json_decode(json_encode($data, JSON_FORCE_OBJECT)); //Force to object convertion
	ModelBruno::setData($data);

	if($app->bruno->bundle == 'screen'){
		//Do nothing
	} else if($app->bruno->bundle == 'quiz'){
		\bundles\bruno\wrapper\hooks\SetGuest();
	} else {
		\bundles\bruno\wrapper\hooks\SetLogin();
	}
}

function SetGuest(){
	//guest_md5 is actually a unique md5
	$app = ModelBruno::getApp();
	$guest_md5 = Vanquish::get('guest_md5');
	if($guest_md5){
		if($guest = Guest::Where('md5', $guest_md5)->first(array('id', 'md5'))){
			$app->bruno->data['guest_id'] = $guest->id;
			$app->bruno->data['guest_md5'] = $guest->md5;
		} else {
			$guest_md5 = false;
		}
	}
	if(!$guest_md5){
		$guest_md5 = md5(uniqid('', true));
		while(Guest::Where('md5', $guest_md5)->first(array('id', 'md5'))){
			usleep(50000);
			$guest_md5 = md5(uniqid('', true));
		}
		$guest = new Guest;
		$guest->md5 = $guest_md5;
		$guest->setLanguage(false);
		if(!$guest->save()){
			return false;
		}
		$app->bruno->data['guest_id'] = $guest->id;
		$app->bruno->data['guest_md5'] = $guest->md5;
		Vanquish::set(array('guest_md5' => $guest_md5,));
	}
	return true;
}

function SetLogin(){
	$app = ModelBruno::getApp();

	//Clear if remember if false
	$remember = Vanquish::get('remember');
	if(!isset($_SESSION['set_login']) && !$remember){
		Vanquish::unsetAll(array('user_language', 'remember', 'host_id'));
	}
	$_SESSION['set_login'] = true;

	//It will give null if not set
	$user_id = Vanquish::get('user_id');
	$user_md5 = Vanquish::get('user_md5');
	$user_email = Vanquish::get('user_email');
	$user_language = Vanquish::get('user_language');	

	$app->bruno->data['user_id'] = false;
	if($user_id && $user_md5 && $user_language && $user_email){
		//Simple verification of the user
		if($user = User::Where('id', $user_id)->where('md5', $user_md5)->first(array('id', 'email'))){
			$app->bruno->data['user_id'] = $user_id;
			//This is a dirty fix: When I switch account, I do not understand why Vanquish::get('user_email') keep the old account.
			if($user_email  != $user->email){
				$user_email = $user->email;
				Vanquish::set(array('user_email' => $user_email,));
			}
		}
	}
	if(!$app->bruno->data['user_id']){
		if($remember){
			//It feels better to keep track of last email login
			Vanquish::unsetAll(array('user_language', 'remember', 'host_id', 'user_email'));
		} else {
			Vanquish::unsetAll(array('user_language', 'remember', 'host_id'));
		}
		$user_id = null;
		$user_md5 = null;
	}

	//This will force the checkbox 'remember me' to be checked as default
	if(is_null($remember)){
		$remember = true;
	}
	
	$app->bruno->data['user_md5'] = $user_md5;
	$app->bruno->data['user_email'] = $user_email;
	$app->bruno->data['remember'] = $remember;
	
	return true;
}
