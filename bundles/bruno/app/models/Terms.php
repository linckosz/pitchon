<?php

namespace bundles\bruno\app\models;


class Terms {

	public static function getTerm(){
		$app = \Slim\Slim::getInstance();
		$language = $app->trans->getClientLanguage();
		$folder = $app->bruno->path.'/bundles/bruno/app/public/files';
		$term_file = '';
		$term_file_default = '';

		if(is_dir($folder)){
			$files = glob($folder.'/*');
			if (is_array($files) && count($files) > 0) {
				foreach($files as $file) {
					if(is_file($file)){
						if(preg_match("/.+_".$language."\.pdf\b/ui",$file)){
							$term_file = $file;
							break;
						} else if(preg_match("/.+_en\.pdf\b/ui",$file)){
							$term_file_default = $file;
						} else {
							$term_file_default = $file;
						}
					}
				}
				if($term_file === '' && $term_file_default !== ''){
					$term_file = $term_file_default;
				}
			}
		}

		return '/bruno/app/files/'.pathinfo($term_file)['basename'];
	}

}
