<?php

namespace bundles\bruno\wrapper\controllers;

use \bundles\bruno\data\models\ModelBruno;
use \libs\Controller;

class ControllerExport extends Controller {

	//http://code.stephenmorley.org/php/creating-downloadable-csv-files/
	public function csv_get(){
		$app = ModelBruno::getApp();
		$app->response->headers->set('Content-Encoding', 'UTF-8');
		$app->response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
		$app->response->headers->set('Content-Disposition', 'attachment; filename=data.csv');
		$get = $app->request->get();
		if($get && isset($get['param']) && $json = json_decode($get['param'])){		
			$output = fopen('php://output', 'w');
			fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); //UTF-8 BOM
			$array = json_decode(json_encode($json), true);
			$fields = array();
			if(count($array)>0){
				foreach ($array as $item) {
					if(is_array($item)){
						foreach ($item as $key => $value) {
							if(!in_array($key, $fields)){
								$fields[] = $key;
							}
						}
					}
				}
				fputcsv($output, $fields);

				$flip = array_flip($fields);
				$results = array();
				$i = 0;
				foreach ($array as $item) {
					if(is_array($item)){
						$results[$i] = array();
						foreach ($fields as $j => $key) {
							if(isset($item[$key])){
								$results[$i][$j] = $item[$key];
							} else {
								$results[$i][$j] = null;
							}
						}
						$i++;
					}
				}
				foreach ($results as $result) {
					fputcsv($output, $result);
				}
			}
			fclose($output);
		}
		return true;
	}

}
