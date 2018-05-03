<?php
//你好 Léo & Luka

namespace libs;

use Overtrue\Pinyin\Pinyin;

class Ord_table {
	// Hold an instance of the class
	private static $instance;

	private static $table = array();
 
	// The singleton method
	public static function singleton() {
		if (!isset(self::$instance)) {
			self::$instance = self::create_table();
		}
		return self::$instance;
	}

	private static function create_table() {
		static $tab = array();
		$entities = get_html_translation_table(HTML_SPECIALCHARS, ENT_HTML5 | ENT_QUOTES, 'UTF-8');
		foreach( $entities as $k => $v ){
			$tab[$k] = '&#' . ord($k) . ';';
		}
		return $tab;
	}
	
}

class STR {

	//Convert a text to HTML entities, readable by HTML, INPUT, TEXTAREA
	public static function sql_to_html($text) {
		//Cannot use htmlspecialchars because Android 2.3 does not recognizes "named entity" ($quote;), but only "numerical entity" ($#39;)
		$text = htmlspecialchars($text, ENT_HTML5 | ENT_QUOTES);
		//$text = self::name_to_numerical($text);
		$text = nl2br($text);
		$text = self::break_line_conv($text, '');
		return $text;
	}

	//Convert a text to JS entities, readable by JS
	//Note, use quotes "..." around the JS variable while displaying
	public static function sql_to_js($text) {
		$text = json_encode($text);
		$text = str_replace("\\r\\n", "\\n", $text);
		//Cancel the quote " added by json_encode
		$text = preg_replace("/^\"|\"$/u", '', $text);
		return $text;
	}

	//Delete any line return
	public static function break_line_conv($text, $replace) {
		return str_replace(array("\r\n", "\r", "\n", CHR(10), CHR(13)), $replace, $text); 
	}

	//Convert BR to space
	public static function br2space($text) {
		return preg_replace("/<br\s*?\/?>/i", ' ', $text);
	}

	//Convert BR to LN
	public static function br2ln($text) {
		return preg_replace("/<br\s*?\/?>/i", "\n", $text);
	}

	//Add return line to a HTML content
	public static function HTMLwithReturnLine($text) {
		$text = preg_replace("/<br\s*?\/?>/i", "<br>\n", $text);
		$text = str_ireplace('<\p>', "<\p>\n", $text);
		$text = str_ireplace('<\div>', "<\div>\n", $text);
		return $text;
	}

	private static function name_to_numerical($string) {
		$tab = Ord_table::singleton();
		return strtr($string, $tab);
	}

	//Convert "any_SHORT description " to "AnyShortDescription"
	public static function textToFirstUC($text){
		$text = str_replace('_', ' ', $text);
		$text = ucwords(strtolower($text));
		$text = str_replace(' ', '', $text);
		return $text;
	}

	//Convert any variable into JS readable in a JS file
	//echo convertToJS($arr);
	public static function convertToJS(&$arr, &$echo='response', $first=true){
		if($first && !empty($echo)){
			$echo .= '=';
		}
		if((is_object($arr) || is_array($arr)) && !empty($arr)){
			$echo .= '{';
			foreach ($arr as $key => $value) {
				if(is_bool($value)){
					if($value){
						$echo .= '\''.$key.'\':true,';
					} else {
						$echo .= '\''.$key.'\':false,';
					}
				} else if(is_integer($value)){
					$echo .= '\''.$key.'\':'.$value.',';
				} else if(is_string($value)){
					$echo .= '\''.$key.'\':"'.STR::sql_to_js($value).'",';
				} else if((is_object($value) || is_array($value)) && !empty($value)){
					$echo .= '\''.$key.'\':';
					STR::convertToJS($value, $echo, false);
				}
			}
			$echo .= '}';
			if(!$first){
				$echo .= ',';
			} else {
				return $echo .= ';';
			}
		}
	}

	//Get an hazard alphanumeric mix of lengh X
	public static function random($lengh=16) {
		$characters = '123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';
		for($p=0; $p<$lengh; $p++) {
			$string .= $characters[mt_rand(0,mb_strlen($characters)-1)];
		}
		return ''.$string; //To be sure it will return a string
	}

	public static function searchString($text){
		$pinyin_lib = new Pinyin();
		$text = strip_tags($text);
		$text = trim($text);
		$text = strtolower($text);
		$text = str_replace(array("\r\n", "\r", "\n", CHR(10), CHR(13), '&nbsp;'), ' ', $text); 
		$text_ori = $text;
		$text = html_entity_decode($text);
		$text = preg_replace('/\p{P}/u', ' ', $text);
		$text = preg_replace('/\s\s+/u', ' ', $text);
		$text = preg_replace('/(\p{Han}+)/u', ' $1 ', $text);
		$text = explode(' ', $text);
		$text = array_unique($text);
		$text_bis = array();
		$text_orig = array(); //original char is merged to the end
		foreach ($text as $key => $value) {
			$temp = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value); //remove accents
			if($temp != $value){
				if(preg_match('/[a-z0-9]+/u', $temp)){
					$text_bis[] = $temp;
					$text_orig[] = $value;
				} else if(preg_match('/(\p{Han})/u', $value)){
					//convert to pinyin first, then split hanzi into separate char
					$value_pinyin = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', pinyin($value));
					if(strlen($value_pinyin) < 1){ //if error, fall back to old pinyin lib
						\libs\Watch::php(true, 'pinyin ext failed', __FILE__, __LINE__, true);
						$value_pinyin = implode('', $pinyin_lib->convert($value));
					}
					$text_bis[] = $value_pinyin;
					$value = preg_replace('/(\p{Han})/u', ' $1 ', $value);
					$text_orig = array_merge($text_orig, explode(' ', $value));
				} else {
					$text_orig[] = $value;
				}
			} else {
				$text_bis[] = $value; //Place it at the end like that the pinyin is in the front, it's easier for abc order
			}
		}
		$text_bis = array_unique($text_bis);
		$text_orig = array_unique($text_orig);
		$text = implode(' ', array_merge($text_bis, $text_orig));
		if($text == $text_ori){
			$text = false; //Note: For some small content, it may search through some html tags, we better to use .text() on front
		}
		$text = trim($text);
		return $text;
	}

	//$decode at fasle will decode
	public static function integer_map($text, $decode=false){
		$result = '';
		$map_encode = array(
			array('m', '5', 'g'), //0
			array('9', 'r', 'w'), //1
			array('q', 'h', 'c'), //2
			array('6', 'j', 'a'), //3
			array('z', 'v', '3'), //4
			array('b', 'n', '8'), //5
			array('y', '7', 'e'), //6
			array('t', 'x', 'd'), //7
			array('f', '2', '4'), //8
			array('s', 'u', 'p'), //9
		);

		if($decode){
			$map_decode = array();
			foreach ($map_encode as $num => $map) {
				foreach ($map as $letter) {
					$map_decode[$letter] = $num;
				}
			}
			$arr = str_split($text);
			foreach ($arr as $letter) {
				if(isset($map_decode[$letter])){
					$result .= ''.$map_decode[$letter];
				}
			}
		} else {
			$mod = fmod((int) $text, 3);
			$arr = str_split((string) $text);
			foreach ($arr as $num) {
				$result .= ''.$map_encode[$num][$mod];
			}
		}

		return $result;
	}

}
