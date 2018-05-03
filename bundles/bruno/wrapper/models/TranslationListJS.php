<?php

namespace bundles\bruno\wrapper\models;

use \libs\STR;

// It's currently not used, we use the ControllerTranslation instead
// To use it, we need to activate it on setData.php with in the merge: 'translation_list_js' => TranslationListJS::setList(),
// This can be used as bellow to get JS translation file in a Twig file
// {{ translation_list_js|raw }}
// We do use ControllerTranslation because it gives a better feeling to load list.js than seeing all code in HTML code, but the result is same

class TranslationListJS {

	public static function setList(){
		$app = \Slim\Slim::getInstance();
		$list = $app->trans->getList(true, 8000);
		$script = "Bruno.Translation = {};\n";
		$script .= "Bruno.Translation._list = [];\n";
		foreach ($list as $bundle => $list_bundles) {
			foreach ($list_bundles as $category => $list_categories) {
				foreach ($list_categories as $phrase => $value) {
					$script .= 'Bruno.Translation._list["'.$bundle.'_'.$category.'_'.$phrase.'"] = {
						js: "'.STR::sql_to_js($value).'",
						html: "'.STR::sql_to_html($value).'",
					};'."\n";
				}
			}
		}
		$script .= "
		Bruno.Translation.get = function(bundle, phrase, format, param){
			var format_tp = 'js';
			var category = '8000'; //Default category for JS sentences
			if(bundle+'_'+category+'_'+phrase in Bruno.Translation._list){
				if(typeof format !== 'undefined') {
					if(format in Bruno.Translation._list[bundle+'_'+category+'_'+phrase]){
						format_tp = format;
					}
				}
				var text = Bruno.Translation._list[bundle+'_'+category+'_'+phrase][format_tp];
				if(typeof param == 'object'){
					text = Translation_filter(text, param,false);
				}
				return text;
			} else {
				if(format_tp=='html'){
					return \"&quot;".$app->trans->getJS('wrapper', 1, 2)."&quot;\"; //[unknown value]  
				} else {
					return \"[".$app->trans->getBRUT('wrapper', 1, 2)."]\"; //[unknown value]  
				}
			}
		};\n";

		return $script;
	}

}
