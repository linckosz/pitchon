<?php

namespace bundles\lincko\launch\models;


class Terms {

	public static function getTerm(){
		$app = \Slim\Slim::getInstance();
		$language = $app->trans->getClientLanguage();
		$folder = $app->lincko->path.'/bundles/bruno/www/public/files/terms';
		$term_file = '';
		$term_file_default = '';

		if(is_dir($folder)){
			$files = glob($folder.'/*');
			if (is_array($files) && count($files) > 0) {
				foreach($files as $file) {
					if(is_file($file)){
						if(preg_match("/.+_".$language."\.pdf\b/ui",$file)){
							$term_file = $file;
						} else if(preg_match("/.+_en\.pdf\b/ui",$file)){
							$term_file_default = $file;
						}
					}
				}
				if($term_file === '' && $term_file_default !== ''){
					$term_file = $term_file_default;
				}
			}
		}

		return '/bruno/www/files/terms/'.pathinfo($term_file)['basename'];
	}

}
