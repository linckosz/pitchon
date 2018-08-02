<?php

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \libs\Vanquish;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\User;
use \bundles\bruno\data\models\Promocode;

class ControllerUser extends Controller {

	public function signin_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		$logged = false;

		if(
			   isset($data->email)
			&& isset($data->password)
			&& User::validEmail($data->email)
			&& User::validPassword($data->password)
		){
			$user = User::Where('email', $data->email)->first(array('id', 'md5', 'pwd', 'email', 'language'));
			if($user && password_verify($data->password, $user->pwd)){
				$logged = true;
			} else if(!$user){ //New user
				$user = new User;
				$user->email = trim(mb_strtolower($data->email));
				$user->username = trim(mb_strstr($data->email, '@', true));
				$user->pwd = password_hash($data->password, PASSWORD_BCRYPT);
				if($user->save()){
					$logged = true;
				}
			}

			if($logged && $user){
				$app->bruno->data['user_id'] = $user->id;
				$remember = false;
				if(isset($data->remember) && $data->remember){
					$remember = true;
				}
				$data = array(
					'user_id' => $user->id,
					'user_md5' => $user->md5,
					'user_email' => $user->email,
					'user_language' => $user->language,
					'remember' => $remember,
				);
				Vanquish::set($data);
				$msg = array(
					'msg' => $app->trans->getBRUT('api', 1, 1), //Login successful!
					'data' => $data,
				);
				(new Json($msg))->render();
				return exit(0);
			}
		}

		(new Json($app->trans->getBRUT('api', 1, 2), true, 200, true))->render(); //Your username or password is incorrect.
		return exit(0);
	}

	public function signout_post(){
		$app = ModelBruno::getApp();
		if(Vanquish::get('remember')){
			//It feels better to keep track of last email login
			Vanquish::unsetAll(array('user_language', 'remember', 'host_id', 'user_email'));
		} else {
			Vanquish::unsetAll(array('user_language', 'remember', 'host_id'));
		}
		(new Json($app->trans->getBRUT('api', 1, 3), false, 200, true))->render(); //You have signed out of your account.
		return exit(0);
	}

	public function search_post(){
		$data = ModelBruno::getData();
		$result = false;
		if(isset($data->email) && $user = User::Where('email', $data->email)->first(array('id', 'md5', 'username', 'email'))){
			$result = array(
				'id' => $user->id,
				'md5' => substr($user->md5, 0, 8), //CR only needed
				'username' => $user->username,
				'email' => mb_strtolower($user->email),
			);
		}
		(new Json($result))->render(); //You have signed out of your account.
		return exit(0);
	}

	public function promocode_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		$promocode = false;
		$result = array(
			'type' => 0,
			'discount' => 0,
			'msg' => '',
		);
		if(isset($data->promocode)){
			if($promocode = Promocode::getItem($data->promocode)){
				if($promocode === -1){
					$result['msg'] = 'Code already used';
				} else if($promocode->type==1){ //Discount in %
					$result['type'] = intval($promocode->type);
					$result['discount'] = intval($promocode->discount);
					$result['msg'] = $app->trans->getBRUT('api', 1, 4, array('discount' => intval($promocode->discount))); //Disoucnt: 30%
				} else if($promocode->type==2){ //Disoucnt in Value
					$result['type'] = intval($promocode->type);
					$result['discount'] = intval($promocode->discount);
					$result['msg'] = $app->trans->getBRUT('api', 1, 5, array('discount' => intval($promocode->discount))); //Disoucnt: 30€
				}
			}
		}
		(new Json($result))->render();
		return exit(0);
	}

}
