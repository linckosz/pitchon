<?php

namespace libs;

class Datassl {

	const SALT = '7dujjbs&%9b=#nk!2v8v*1|1';
	const METHOD = 'AES-256-CBC';

	protected static function createIV($password){
		$ivlen = openssl_cipher_iv_length(self::METHOD);
		$iv = md5(base64_encode($password));
		while(mb_strlen($iv) < $ivlen){
			$iv .= md5($iv);
		}
		return mb_substr($iv, 0, $ivlen);
	}

	/**
	* Encrypt string using base64
	* @param string $textToEncrypt
	* @param string $password User's optional password
	*/
	public static function encrypt_unsafe($textToEncrypt, $password = ''){
		return base64_encode($textToEncrypt);
	}

	/**
	* Decrypt string using base64
	* @param string $textToDecrypt
	* @param string $password User's optional password
	*/
	public static function decrypt_unsafe($textToDecrypt){
		return base64_decode($textToDecrypt);
	}

	/**
	* Encrypt string using openSSL module
	* @param string $textToEncrypt
	* @param string $password User's optional password
	*/
	public static function encrypt($textToEncrypt, $password = ''){
		return base64_encode(openssl_encrypt($textToEncrypt, self::METHOD, self::SALT, OPENSSL_RAW_DATA, self::createIV($password)));
	}

	/**
	* Decrypt string using openSSL module
	* @param string $textToDecrypt
	* @param string $password User's optional password
	*/
	public static function decrypt($textToDecrypt, $password = ''){
		$data = openssl_decrypt(base64_decode($textToDecrypt), self::METHOD, self::SALT, OPENSSL_RAW_DATA, self::createIV($password));
		if(!$data){
			$data = self::decrypt_unsafe($textToDecrypt);
		}
		return $data;
	}

	//http://blog.justin.kelly.org.au/simple-mcrypt-encrypt-decrypt-functions-for-p/
	public static function encrypt_smp($text, $password = ''){
		$salt = self::SALT;
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($salt.$password), $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}

	public static function decrypt_smp($text, $password = ''){
		$salt = self::SALT;
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($salt.$password), base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}
	
}
