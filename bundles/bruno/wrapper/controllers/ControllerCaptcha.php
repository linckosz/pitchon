<?php

namespace bundles\bruno\wrapper\controllers;

use \libs\Controller;
use \libs\STR;
use \libs\SimpleImageCaptcha;
use \libs\File;

class ControllerCaptcha extends Controller {

	public function get_captcha($total_num=4, $width=120, $height=40){
		
		$wrapper_captcha = rand(pow(10,$total_num-1),pow(10,$total_num)-1);

		//Background picture
		$src = new SimpleImageCaptcha(File::getLocalFile('/bruno/wrapper/images/captcha/bg/'.rand(1,4).'.png'));
		
		$src->resize($width, $height);
		for($i=1; $i<=$total_num; $i++){
			$num = mb_substr($wrapper_captcha, $i-1, 1);
			$src->bruno_addcaptcha(File::getLocalFile("/bruno/wrapper/images/captcha/$num/$num".rand(0,9).'.png'), $i, $total_num);
		}
		
		$_SESSION['wrapper_captcha'] = $wrapper_captcha;
		
		$src->output(IMAGETYPE_JPEG);
		unset($src);
		
	}

}
