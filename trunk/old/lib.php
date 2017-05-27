<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('SHIELDFY_SERVER_URL_CONST','https://shieldfy.com');
define('SHIELDFY_API_URL_CONST','http://api2.shieldfy.com');

function shieldfy_call_server($url,$postdata = array(),$token){
	$url = SHIELDFY_SERVER_URL_CONST."/api/".$url;
	return _shieldfy_call($url,$postdata,$token);
}

function _shieldfy_call($url,$postdata = array(),$token){
	if(extension_loaded('curl') && is_callable('curl_init')){
		return _shieldfy_curl($url,$postdata,$token);
	}else{
		return _shieldfy_file($url,$postdata,$token);
	}
}
function _shieldfy_curl($url,$postdata = array(),$token){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('X-Shieldfy-Website-Key: '.$token));
  	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if(!empty($postdata)){
		curl_setopt($ch,CURLOPT_POST, count($postdata));
		$postdata = http_build_query(
		    $postdata
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
	}

	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
function _shieldfy_file($url,$postdata = array(),$token){
	$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => "Content-type: application/x-www-form-urlencoded \r\n".
		        			 'X-Shieldfy-Website-Key: '.$token,
		    )
		);
	if(!empty($postdata)){
		$postdata = http_build_query(
		    $postdata
		);
		$opts['http']['content'] = $postdata;
	}
	$context  = stream_context_create($opts);
	return file_get_contents($url, false, $context);
}
