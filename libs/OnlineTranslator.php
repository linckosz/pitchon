<?php

namespace libs;

use \libs\STR;

class OnlineTranslator {

	protected $app = null;
	protected $from = false;
	protected static $token = false;

	public function __construct(){
		$this->app = \Slim\Slim::getInstance();
	}

	protected function to(){
		return $this->app->trans->getClientLanguage();
	}

	protected function getToken(){
		$app = $this->app;
		if(!self::$token){
			$url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
			$ch = curl_init();
			$data_string = json_encode('{body}');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string),
					'Ocp-Apim-Subscription-Key: ' . $app->bruno->translator['text_key1']
				)
			);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if($strResponse = curl_exec($ch)){
				self::$token = $strResponse;
			} else {
				\libs\Watch::php('getToken', 'OnlineTranslator error', __FILE__, __LINE__, true);
				return '['.$app->trans->getBRUT('wrapper', 0, 1).']'; //The translation failed
			}
			curl_close($ch);
		}
		return self::$token;
	}

	public function from($str_source_text){
		$app = $this->app;
		if(!$this->from){
			$params = 'appId=Bearer+' . $this->getToken() . '&text=' . urlencode($str_source_text);
			$translateUrl = 'http://api.microsofttranslator.com/V2/Http.svc/Detect?'.$params;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $translateUrl);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$language = curl_exec($ch);
			curl_close($ch);
			if($language){
				$xml = json_decode(json_encode((new \SimpleXMLElement($language))), true);
				if(isset($xml[0])){
					$this->from = $xml[0];
				}
			}
		}
		if(!$this->from){
			\libs\Watch::php('detect', 'OnlineTranslator error', __FILE__, __LINE__, true);
			return 'en'; //Default to english in case of failure
		}
		return $this->from;
	}

	public function translate($str_source_text){
		$app = $this->app;
		$from = $this->from($str_source_text);
		$to = $this->to();
		//if same, we don't convert
		if($from==$to){
			return $str_source_text;
		}
		//Trick to keep breaking lines
		$str_source_text = STR::break_line_conv($str_source_text, '&#10;');
		$params = 'to=' . $to . '&from=' . $from . '&appId=Bearer+' . $this->getToken() . '&text=' . urlencode($str_source_text);
		$translateUrl = 'http://api.microsofttranslator.com/v2/Http.svc/Translate?'.$params;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $translateUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$str_target_text = curl_exec($ch);
		curl_close($ch);
		if($str_target_text){
			$xml = json_decode(json_encode((new \SimpleXMLElement($str_target_text))), true);
			if(isset($xml[0])){
				//Recover breaking lines
				return str_replace('&#10;', "\n", urldecode($xml[0]));
			}
		}
		\libs\Watch::php('translate', 'OnlineTranslator error', __FILE__, __LINE__, true);
		return '['.$app->trans->getBRUT('wrapper', 0, 1).']'; //The translation failed
	}

}
