<?php

function post($key){
	return isset($_POST[$key]) ? $_POST[$key] : '';
}

function input($key)
{
    $results = '';
    if (isset($_GET[$key]) && !is_array($_GET[$key])) {
        $results = isset($_GET[$key])&&strlen($_GET[$key])<=256 ? globalSanitizeFilter($_GET[$key], $key) : '';
    }
    return xssFilter($results);
}


function searchInput($key){
	$input = input($key);
	//check the input query string with script tags will return empty string
	if (preg_match("/<script(.*?)>(.*?)<\/script>/is", $input) || preg_match("/<script(.*?)>/is", $input)){
		return '';
	}
	$input = strip_tags($input);
	return trim(preg_replace('/[^(a-zA-Z0-9.()\-,\/)\&\'\"]+/i',' ', $input));
}

function numberInput($key){
	$val = input($key);
	return isNumber($val) ? $val : '';
}


function isNumber($str){
	return preg_match("/^[0-9]+$/", $str);
}

function isEmail($str){
	return preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,})$/", $str);
}

function getSelfUrl(){
	$s = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'])=='https' ? 's' : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's' : '');
	$protocol = strLeft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	$url = $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
	return parse_url($url);
}

function xssFilter($url)
{
    $pattern = "/(<script|<\/script|onstart|onfocus|onerror|onload|onmouseover|iframe|onblur|payload|onmousemove|prompt|\")/i";
    $url = urldecode($url);
    while (preg_match($pattern, $url)) {
        $url = preg_replace($pattern, '', $url);
    }
    return $url;
}

function redirect($addr, $alert=''){
	global $siteOrlocalMode;
	if($alert)
		$_SESSION['global_flash_alert'] = $alert;

	$url = $addr;
	if(stripos($url,'http://')===0 || stripos($url,'https://')===0){
		$url = str_ireplace(array('http://','https://'), '', $url);
		if(!preg_match('/^[a-z0-9-_]+\.(beyourdiary)\.com/i', $url) && $siteOrlocalMode )
			$addr = SITEURL;
	}
	header("Location:" . $addr);
	exit();
}

function myCurl($url, $ops=array()){
	if(stripos($url, 'https://uat.cms.beyourdiary')!==false){
		$url = str_replace('https://', 'http://', $url);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	if(!array_key_exists('CURLOPT_RETURNTRANSFER', $ops))
		$ops['CURLOPT_RETURNTRANSFER'] = true;
	if(!array_key_exists('CURLOPT_CONNECTTIMEOUT', $ops))
		$ops['CURLOPT_CONNECTTIMEOUT'] = 1;
	if(!array_key_exists('CURLOPT_TIMEOUT', $ops))
		$ops['CURLOPT_TIMEOUT'] = 1;

	foreach($ops as $op=>$val){
		curl_setopt($ch, constant($op), $val);
	}

	$result = curl_exec($ch);
	curl_close($ch);
    return $result;
}

function generateShortURL($url){
	$url = urlencode($url);
	$ch = curl_init();
	$timeout = 5;
	$result = '';
	curl_setopt($ch,CURLOPT_URL,'http://api.bit.ly/v3/shorten?login=paustina&apiKey='.BITLYKEY.'&uri='.$url.'&format=txt');
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	// Execute the post
	$result = curl_exec($ch);
	// Close the connection
	curl_close($ch);
	// Return the result

	return $result;
}

function googleShortURL($longurl){
	$data = array('dynamicLinkInfo' => array('dynamicLinkDomain'=>'beyourdiary.co', 'link' => $longurl));
	$data_string = json_encode($data);

	$ch = curl_init('https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key='.fbaseURLSHORTERNER);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);

	$result = curl_exec($ch);

	$decodeResult =  json_decode($result);
	$firebaseShortURL = isset($decodeResult->shortLink)?$decodeResult->shortLink:'';

	return $firebaseShortURL;
}

function strLeft($s1, $s2){
	return substr($s1, 0, strpos($s1, $s2));
}

function shippingDetail_Curl($ship_action,$postparam){
	global $curl_ship_domain;
	$return = '';
	$url = $curl_ship_domain.$ship_action;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postparam));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	ob_start(); 
	$return = curl_exec($ch);
	ob_end_clean();
	curl_close($ch);
	return $return;
}

function convertobj($element){
	$return_arr = '';
	foreach($element as $x => $value) {
		$return_arr[]= $value;
	}
	return $return_arr;
}

function convertCapitalCase($param){
	return trim(strtoupper($param));
}

function convertSmallerCase($param){
	return trim(strtolower($param));
}

function convertFirstEachWordCap($param){
	return trim(ucwords($param));
}
?>