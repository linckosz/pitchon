<?php
//Category 2
namespace bundles\bruno\wrapper\controllers;

use \bundles\bruno\data\models\ModelBruno;
use \libs\Controller;
use \libs\STR;

class ControllerDate extends Controller {

	public function date_get(){
		$app = ModelBruno::getApp();
		$app->response->headers->set('Content-Type', 'application/javascript');
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
		$this->setDate();
	}

	protected function setDate(){
		$app = ModelBruno::getApp();
		$app->trans->setDefaultLanguage('wrapper');

		echo "
		//timestamp_ms (optional) default is current UTC
		function wrapper_date(timestamp_ms) {

			if(typeof this.Constructor === 'function'){
				this.Constructor(timestamp_ms);
			}
		};

		wrapper_date.prototype = {
	
			day: [";
			for($i=0; $i<=6; $i++){
				echo '"'.$app->trans->getJS('wrapper', 2, 1000+$i).'", ';
			}
		echo "],
			
			day_short: [";
			for($i=0; $i<=6; $i++){
				echo '"'.$app->trans->getJS('wrapper', 2, 1010+$i).'", ';
			}
		echo "],
			
			day_very_short: [";
			for($i=0; $i<=6; $i++){
				echo '"'.$app->trans->getJS('wrapper', 2, 1020+$i).'", ';
			}
		echo "],
			
			month: [";
			for($i=0; $i<=11; $i++){
				echo '"'.$app->trans->getJS('wrapper', 2, 1030+$i).'", ';
			}
		echo "],
			
			month_short: [";
			for($i=0; $i<=11; $i++){
				echo '"'.$app->trans->getJS('wrapper', 2, 1050+$i).'", ';
			}
		echo "],
			
			month_short_num: [";
			for($i=0; $i<=11; $i++){
				echo '"'.$app->trans->getJS('wrapper', 2, 1070+$i).'", ';
			}
		echo "],

			//The first key '0' is actually 31st of last month because JS table start from 0, not 1.
			ordinal: [";
			for($i=-1; $i<=30; $i++){
				//echo '"'.date('S', strtotime($i.' days', 0))."\", ";
				echo '"", '; //[toto] don't use cardinal because PHP setlocal has issue, it keeps to english
			}
		echo "],

			format: {
				date_very_short: \"".$app->trans->getJS('wrapper', 2, 7)."\", //Jul 8

				date_short: \"".$app->trans->getJS('wrapper', 2, 1)."\", //Jul 8th (Default)

				date_medium: \"".$app->trans->getJS('wrapper', 2, 2)."\", //July 8th, 2015

				date_medium_simple: \"".$app->trans->getJS('wrapper', 2, 8)."\", //July 8, 2015

				date_long: \"".$app->trans->getJS('wrapper', 2, 3)."\", //8 Jul 2015 01:51 PM

				date_full: \"".$app->trans->getJS('wrapper', 2, 4)."\", //Sat 08 Jul 2015 01:51 PM

				time_short: \"".$app->trans->getJS('wrapper', 2, 5)."\", //01:51 PM

				time_full: \"".$app->trans->getJS('wrapper', 2, 6)."\", //01:51:31 PM
			},

		};
		\n";
	}

}
