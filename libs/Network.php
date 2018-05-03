<?php

namespace libs;

class Network {

	public static function checkAccess($network, $port=80, $timeout=2){
		$port = (int) $port;
		$timeout = (int) $timeout;
		exec("nc -z -w $timeout \"$network\" $port 2>&1", $tablo, $error);
		if($error){
			return false;
		}
		return true;
	}
	
}
