<?php

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \libs\Vanquish;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\User;

class ControllerUser extends Controller {

	public function signin_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();

		if(
			   isset($data->email)
			&& isset($data->password)
		){
			if($user = User::Where('email', $data->email)->where('pwd', $data->password)->first(array('id', 'md5', 'email', 'language'))){
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
			Vanquish::unsetAll(array('user_language', 'remember', 'user_email'));
		} else {
			Vanquish::unsetAll(array('user_language', 'remember'));
		}
		(new Json($app->trans->getBRUT('api', 1, 3), false, 200, true))->render(); //You have signed out of your account.
		return exit(0);
	}

}
