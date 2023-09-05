<?php

use PgSql\Lob;

function post($key){
	return isset($_POST[$key]) ? $_POST[$key] : '';
}

function input($key)
{
    $results = '';
    if (isset($_GET[$key]) && !is_array($_GET[$key])) {
        $results = isset($_GET[$key])&&strlen($_GET[$key])<=256 ? /* globalSanitizeFilter($_GET[$key], $key) */ $_GET[$key] : '';
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

function audit_log($data=array()){
	if(count($data) > 0)
	{
		extract($data);
		
		switch($log_act)
		{
			case 'view':
				$query = "INSERT INTO ".AUDIT_LOG." (log_action, screen_type, user_id, action_message, create_date, create_time, create_by) VALUES ('1', '$page', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'edit':
				$query = "INSERT INTO ".AUDIT_LOG." (log_action, screen_type, query_record, query_table, old_value, changes, user_id, action_message, create_date, create_time, create_by) VALUES ('2', '$page', \"$query_rec\", '$query_table', '$oldval', '$changes', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'delete':
				$query = "INSERT INTO ".AUDIT_LOG." (log_action, screen_type, query_record, query_table, user_id, action_message, create_date, create_time, create_by) VALUES ('3', '$page', \"$query_rec\", '$query_table', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'add':
				$query = "INSERT INTO ".AUDIT_LOG." (log_action, screen_type, query_record, query_table, new_value, user_id, action_message, create_date, create_time, create_by) VALUES ('4', '$page', \"$query_rec\", '$query_table', '$newval', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'import':
				$query = "INSERT INTO ".AUDIT_LOG." (log_action, screen_type, query_record, query_table, new_value, user_id, action_message, create_date, create_time, create_by) VALUES ('5', '$page', \"$query_rec\", '$query_table', '$newval', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'export':
				$query = "INSERT INTO ".AUDIT_LOG." (log_action, screen_type, user_id, action_message, create_date, create_time, create_by) VALUES ('6', '$page', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'login':
				$query = "INSERT INTO ".AUDIT_LOG." (screen_type, log_action, user_id, action_message, create_date, create_time, create_by) VALUES ('Login Screen', '7', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'logout':
				$query = "INSERT INTO ".AUDIT_LOG." (screen_type, log_action, user_id, action_message, create_date, create_time, create_by) VALUES ('Login Screen', '8', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
		}

		if(isset($query))
			mysqli_query($connect, $query);
		/* return $query; */
	}
}

function getCountry($param)
{
	$all_country = array(
		"AF" => "Afghanistan",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"SZ" => "Swaziland",
		"AS" => "American Samoa",
		"SE" => "Sweden",
		"AD" => "Andorra",
		"AO" => "Angola",
		"CH" => "Switzerland",
		"AI" => "Anguilla",
		"AG" => "Antigua",
		"AR" => "Argentina",
		"SY" => "Syria",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"PF" => "Tahiti",
		"TW" => "Taiwan",
		"AU" => "Australia",
		"AT" => "Austria",
		"TJ" => "Tajikistan",
		"AZ" => "Azerbaijan",
		"TZ" => "Tanzania",
		"BS" => "Bahamas",
		"TH" => "Thailand",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"TG" => "Togo",
		"BE" => "Belgium",
		"TO" => "Tonga",
		"BZ" => "Belize",
		"TT" => "Trinidad And Tobago",
		"BJ" => "Benin",
		"TN" => "Tunisia",
		"BM" => "Bermuda",
		"TR" => "Turkey",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BQ" => "Bonaire",
		"BA" => "Bosnia And Herzegovina",
		"BW" => "Botswana",
		"TM" => "Turkmenistan",
		"TC" => "Turks And Caicos Islands",
		"BR" => "Brazil",
		"TV" => "Tuvalu",
		"BN" => "Brunei Darussalam",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"UG" => "Uganda",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"UA" => "Ukraine",
		"CA" => "Canada",
		"IC" => "Canary Islands, The",
		"AE" => "United Arab Emirates",
		"CV" => "Cape Verde",
		"GB" => "United Kingdom",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CO" => "Colombia",
		"CW" => "Curacao",
		"MZ" => "Mozambique",
		"AN" => "Netherlands Antilles",
		"GF" => "French Guiana",
		"US" => "United States",
		"CN" => "China",
		"KM" => "Comoros",
		"CG" => "Congo",
		"UM" => "United States Minor Outlying Islands",
		"CD" => "Congo, The Democratic Republic Of The",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"CI" => "Cote D'ivoire",
		"HR" => "Croatia",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"CU" => "Cuba",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DK" => "Denmark",
		"VU" => "Vanuatu",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"VE" => "Venezuela",
		"TP" => "East Timor",
		"EC" => "Ecuador",
		"VN" => "Vietnam",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"VG" => "Virgin Islands, British",
		"EE" => "Estonia",
		"VI" => "Virgin Islands, U.S.",
		"ET" => "Ethiopia",
		"FK" => "Falkland Island (Malvinas)",
		"FO" => "Faroe Islands",
		"YE" => "Yemen",
		"FJ" => "Fiji",
		"ZM" => "Zambia",
		"FI" => "Finland",
		"FR" => "France",
		"ZW" => "Zimbabwe",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea Republic",
		"GW" => "Guinea Bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran, Islamic Republic Of",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KP" => "Korea, Democratic People's Republic Of",
		"KR" => "Korea, Republic Of",
		"XK" => "Kosovo",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Lao People's Democratic Republic",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macau",
		"MK" => "Macedonia, The Former Yugoslav Republic Of",
		"MG" => "Madagascar",
		"MY" => "Malaysia",
		"MW" => "Malawi",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"MX" => "Mexico",
		"FM" => "Micronesia, Federated States Of",
		"MD" => "Moldova, Republic of",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MM" => "Myanmar",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"XN" => "Nevis",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NO" => "Norway",
		"OM" => "Oman",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RE" => "Reunion",
		"RO" => "Romania",
		"RU" => "Russian Federation",
		"RW" => "Rwanda",
		"MP" => "Saipan",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"ST" => "Sao Tome And Principe",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"XS" => "Somaliland (North Somalia)",
		"ZA" => "South Africa",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"BL" => "St.Barthelemy",
		"XE" => "St.Eustatius",
		"KN" => "St.Kitts",
		"LC" => "St.Lucia",
		"XM" => "St.Maarten",
		"VC" => "St.Vincent",
		"SD" => "Sudan",
		"SR" => "Suriname",
	);

	switch($param)
	{
		case 'MY': case 'my':
			return 'Malaysia';
			break;
		case 'SG': case 'sg':
			return 'Singapore';
			break;
		case 'all': case 'All':
			return $all_country;
			break;
	}
}

function getCountryArea($param)
{
	$MY_State = array(
			"jhr" => "Johor",
			"kdh" => "Kedah",
			"ktn" => "Kelantan",
			"mlk" => "Melaka",
			"nsn" => "Negeri Sembilan",
			"phg" => "Pahang",
			"prk" => "Perak",
			"pls" => "Perlis",
			"png" => "Pulau Pinang",
			"sgr" => "Selangor",
			"trg" => "Terengganu",
			"kul" => "Kuala Lumpur",
			"pjy" => "Putra Jaya",
			"srw" => "Sarawak",
			"sbh" => "Sabah",
			"lbn" => "Labuan"
	);

	$SG_State = array(
		"Central" => "Central",
		"East" => "East",
		"North" => "North",
		"Northeast" => "Northeast",
		"West" => "West"
	);

	switch($param)
	{
		case 'MY': case 'my':
			return $MY_State;
			break;
		case 'SG': case 'sg':
			return $SG_State;
			break;
	}
}

function getCountryTelCode($param)
{
	switch($param)
	{
		case 'MY': case 'my':		//	Malaysia
			return '+60';	break;
		case 'SG': case 'sg':		//	Singapore
			return '+65';	break;
		case 'AF': case 'af':		//	Afghanistan
			return '+93';	break;
		case 'AL': case 'al':		//	Albania
			return '+355';	break;
		case 'DZ': case 'dz':		//	Algeria
			return '+213';	break;
		case 'SZ': case 'sz':		//	Swaziland
			return '+268';	break;
		case 'AS': case 'as':		//	American Samoa
			return '+1684';	break;
		case 'SE': case 'se':		//	Sweden
			return '+46';	break;
		case 'AD': case 'ad':		//	Andorra
			return '+376';	break;
		case 'AO': case 'ao':		//	Angola
			return '+244';	break;
		case 'CH': case 'ch':		//	Switzerland
			return '+41';	break;
		case 'AI': case 'ai':		//	Anguilla
			return '+1264';	break;
		case 'AG': case 'ag':		//	Antigua
			return '+1268';	break;
		case 'AR': case 'ar':		//	Argentina
			return '+54';	break;
		case 'SY': case 'sy':		//	Syria
			return '+963';	break;
		case 'AM': case 'am':		//	Armenia
			return '+374';	break;
		case 'AW': case 'aw':		//	Aruba
			return '+297';	break;
		case 'PF': case 'pf':		//	Tahiti
			return '+689';	break;
		case 'TW': case 'TW':		//	Taiwan
			return '+886';	break;
		case 'AU': case 'au':		//	Australia
			return '+61';	break;
		case 'AT': case 'at':		//	Austria
			return '+43';	break;
		case 'TJ': case 'tj':		//	Tajikistan
			return '+992';	break;
		case 'AZ': case 'az':		//	Azerbaijan
			return '+994';	break;
		case 'TZ': case 'tz':		//	Tanzania
			return '+255';	break;
		case 'BS': case 'bs':		//	Bahamas
			return '+1242';	break;
		case 'TH': case 'th':		//	Thailand
			return '+66';	break;
		case 'BH': case 'bh':		//	Bahrain
			return '+973';	break;
		case 'BD': case 'bd':		//	Bangladesh
			return '+880';	break;
		case 'BB': case 'bb':		//	Barbados
			return '+1246';	break;
		case 'BY': case 'by':		//	Belarus
			return '+375';	break;
		case 'TG': case 'tg':		//	Togo
			return '+228';	break;
		case 'BE': case 'be':		//	Belgium
			return '+32';	break;
		case 'TO': case 'to':		//	Tonga
			return '+676';	break;
		case 'BZ': case 'bz':		//	Belize
			return '+501';	break;
		case 'TT': case 'tt':		//	Trinidad And Tobago
			return '+1868';	break;
		case 'BJ': case 'bj':		//	Benin
			return '+229';	break;
		case 'TN': case 'tn':		//	Tunisia
			return '+216';	break;
		case 'BM': case 'bm':		//	Bermuda
			return '+1441';	break;
		case 'TR': case 'tr':		//	Turkey
			return '+90';	break;
		case 'BT': case 'bt':		//	Bhutan
			return '+975';	break;
		case 'BO': case 'bo':		//	Bolivia
			return '+591';	break;
		case 'BQ': case 'bq':		//	Bonaire
			return '+599';	break;
		case 'BA': case 'ba':		//	Bosnia And Herzegovina
			return '+387';	break;
		case 'BW': case 'bw':		//	Botswana
			return '+267';	break;
		case 'TM': case 'tm':		//	Turkmenistan
			return '+993';	break;
		case 'TC': case 'tc':		//	Turks And Caicos Islands
			return '+1649';	break;
		case 'BR': case 'br':		//	Brazil
			return '+55';	break;
		case 'TV': case 'tv':		//	Tuvalu
			return '+688';	break;
		case 'BN': case 'bn':		//	Brunei Darussalam
			return '+673';	break;
		case 'BG': case 'bg':		//	Bulgaria
			return '+359';	break;
		case 'BF': case 'bf':		//	Burkina Faso
			return '+226';	break;
		case 'BI': case 'bi':		//	Burundi
			return '+257';	break;
		case 'UG': case 'ug':		//	Uganda
			return '+256';	break;
		case 'KH': case 'kh':		//	Cambodia
			return '+855';	break;
		case 'CM': case 'cm':		//	Cameroon
			return '+237';	break;
		case 'UA': case 'ua':		//	Ukraine
			return '+380';	break;
		case 'CA': case 'ca':		//	Canada
			return '+1';	break;
		case 'IC': case 'ic':		//	Canary Islands, The
			return '+34';	break;
		case 'AE': case 'ae':		//	United Arab Emirates
			return '+971';	break;
		case 'CV': case 'cv':		//	Cape Verde
			return '+238';	break;
		case 'GB': case 'gb':		//	United Kingdom
			return '+44';	break;
		case 'KY': case 'ky':		//	Cayman Islands
			return '+1345';	break;
		case 'CF': case 'cf':		//	Central African Republic
			return '+236';	break;
		case 'TD': case 'td':		//	Chad
			return '+235';	break;
		case 'CL': case 'cl':		//	Chile
			return '+56';	break;
		case 'CO': case 'co':		//	Colombia
			return '+57';	break;
		case 'CW': case 'cw':		//	Curacao
			return '+599';	break;
		case 'MZ': case 'mz':		//	Mozambique
			return '+258';	break;
		case 'AN': case 'an':		//	Netherlands Antilles
			return '+599';	break;
		case 'GF': case 'gf':		//	French Guiana
			return '+594';	break;
		case 'US': case 'us':		//	United States
			return '+1';	break;
		case 'CN': case 'cn':		//	China
			return '+86';	break;
		case 'KM': case 'km':		//	Comoros
			return '+269';	break;
		case 'CG': case 'cg':		//	Congo
			return '+243';	break;
		case 'UM': case 'um':		//	United States Minor Outlying Islands
			return '+1';	break;
		case 'CD': case 'cd':		//	Congo, The Democratic Republic Of The
			return '+243';	break;
		case 'CK': case 'ck':		//	Cook Islands
			return '+682';	break;
		case 'CR': case 'cr':		//	Costa Rica
			return '+506';	break;
		case 'CI': case 'ci':		//	Cote D'ivoire
			return '+225';	break;
		case 'HR': case 'hr':		//	Croatia
			return '+385';	break;
		case 'UY': case 'uy':		//	Uruguay
			return '+598';	break;
		case 'UZ': case 'uz':		//	Uzbekistan
			return '+998';	break;
		case 'CU': case 'cu':		//	Cuba
			return '+53';	break;
		case 'CY': case 'cy':		//	Cyprus
			return '+357';	break;
		case 'CZ': case 'cz':		//	Czech Republic
			return '+420';	break;
		case 'DK': case 'dk':		//	Denmark
			return '+45';	break;
		case 'VU': case 'vu':		//	Vanuatu
			return '+678';	break;
		case 'DJ': case 'dj':		//	Djibouti
			return '+253';	break;
		case 'DM': case 'dm':		//	Dominica
			return '+1767';	break;
		case 'DO': case 'do':		//	Dominican Republic
			return array('+1809','+1829','+1849');	break;
		case 'VE': case 've':		//	Venezuela
			return '+58';	break;
		case 'TP': case 'tp':		//	East Timor
			return '+670';	break;
		case 'EC': case 'ec':		//	Ecuador
			return '+593';	break;
		case 'VN': case 'vn':		//	Vietnam
			return '+84';	break;
		case 'EG': case 'eg':		//	Egypt
			return '+20';	break;
		case 'SV': case 'sv':		//	El Salvador
			return '+503';	break;
		case 'GQ': case 'gq':		//	Equatorial Guinea
			return '+240';	break;
		case 'ER': case 'er':		//	Eritrea
			return '+291';	break;
		case 'VG': case 'vg':		//	Virgin Islands, British
			return '+1284';	break;
		case 'EE': case 'ee':		//	Estonia
			return '+372';	break;
		case 'VI': case 'vi':		//	Virgin Islands, U.S.
			return '+1340';	break;
		case 'ET': case 'et':		//	Ethiopia
			return '+251';	break;
		case 'FK': case 'fk':		//	Falkland Island (Malvinas)
			return '+500';	break;
		case 'FO': case 'fo':		//	Faroe Islands
			return '+298';	break;
		case 'YE': case 'ye':		//	Yemen
			return '+967';	break;
		case 'FJ': case 'fj':		//	Fiji
			return '+679';	break;
		case 'ZM': case 'zm':		//	Zambia
			return '+260';	break;
		case 'FI': case 'fi':		//	Finland
			return '+358';	break;
		case 'FR': case 'fr':		//	France
			return '+33';	break;
		case 'ZW': case 'zw':		//	Zimbabwe
			return '+263';	break;
		case 'GA': case 'ga':		//	Gabon
			return '+241';	break;
		case 'GM': case 'gm':		//	Gambia
			return '+220';	break;
		case 'GE': case 'ge':		//	Georgia
			return '+995';	break;
		case 'DE': case 'de':		//	Germany
			return '+49';	break;
		case 'GH': case 'gh':		//	Ghana
			return '+233';	break;
		case 'GI': case 'gi':		//	Gibraltar
			return '+350';	break;
		case 'GR': case 'gr':		//	Greece
			return '+30';	break;
		case 'GL': case 'gl':		//	Greenland
			return '+299';	break;
		case 'GD': case 'gd':		//	Grenada
			return '+1473';	break;
		case 'GP': case 'gp':		//	Guadeloupe
			return '+590';	break;
		case 'GU': case 'gu':		//	Guam
			return '+1671';	break;
		case 'GT': case 'gt':		//	Guatemala
			return '+502';	break;
		case 'GG': case 'gg':		//	Guernsey
			return '+441481';	break;
		case 'GN': case 'gn':		//	Guinea Republic
			return '+224';	break;
		case 'GW': case 'gw':		//	Guinea Bissau
			return '+245';	break;
		case 'GY': case 'gy':		//	Guyana
			return '+592';	break;
		case 'HT': case 'ht':		//	Haiti
			return '+509';	break;
		case 'HN': case 'hn':		//	Honduras
			return '+504';	break;
		case 'HK': case 'hk':		//	Hong Kong
			return '+852';	break;
		case 'HU': case 'hu':		//	Hungary
			return '+36';	break;
		case 'IS': case 'is':		//	Iceland
			return '+354';	break;
		case 'IN': case 'in':		//	India
			return '+91';	break;
		case 'ID': case 'id':		//	Indonesia
			return '+62';	break;
		case 'IR': case 'ir':		//	Iran, Islamic Republic Of
			return '+98';	break;
		case 'IQ': case 'iq':		//	Iraq
			return '+964';	break;
		case 'IE': case 'ie':		//	Ireland
			return '+353';	break;
		case 'IL': case 'il':		//	Israel
			return '+972';	break;
		case 'IT': case 'it':		//	Italy
			return '+39';	break;
		case 'JM': case 'jm':		//	Jamaica
			return '+1876';	break;
		case 'JP': case 'jp':		//	Japan
			return '+81';	break;
		case 'JE': case 'je':		//	Jersey
			return '+44';	break;
		case 'JO': case 'jo':		//	Jordan
			return '+962';	break;
		case 'KZ': case 'kz':		//	Kazakhstan
			return '+7';	break;
		case 'KE': case 'ke':		//	Kenya
			return '+254';	break;
		case 'KI': case 'ki':		//	Kiribati
			return '+686';	break;
		case 'KP': case 'kp':		//	Korea, Democratic People's Republic Of
			return '+850';	break;
		case 'KR': case 'kr':		//	Korea, Republic Of
			return '+82';	break;
		case 'XK': case 'xk':		//	Kosovo
			return '+383';	break;
		case 'KW': case 'kw':		//	Kuwait
			return '+965';	break;
		case 'KG': case 'kg':		//	Kyrgyzstan
			return '+996';	break;
		case 'LA': case 'la':		//	Lao People's Democratic Republic
			return '+856';	break;
		case 'LV': case 'lv':		//	Latvia
			return '+371';	break;
		case 'LB': case 'lb':		//	Lebanon
			return '+961';	break;
		case 'LS': case 'ls':		//	Lesotho
			return '+266';	break;
		case 'LR': case 'lr':		//	Liberia
			return '+231';	break;
		case 'LY': case 'ly':		//	Libya
			return '+218';	break;
		case 'LI': case 'li':		//	Liechtenstein
			return '+423';	break;
		case 'LT': case 'lt':		//	Lithuania
			return '+370';	break;
		case 'LU': case 'lu':		//	Luxembourg
			return '+352';	break;
		case 'MO': case 'mo':		//	Macau
			return '+853';	break;
		case 'MK': case 'mk':		//	Macedonia, The Former Yugoslav Republic Of
			return '+389';	break;
		case 'MG': case 'mg':		//	Madagascar
			return '+261';	break;
		case 'MW': case 'mw':		//	Malawi
			return '+265';	break;
		case 'MV': case 'mv':		//	Maldives
			return '+960';	break;
		case 'ML': case 'ml':		//	Mali
			return '+223';	break;
		case 'MT': case 'mt':		//	Malta
			return '+356';	break;
		case 'MH': case 'mh':		//	Marshall Islands
			return '+692';	break;
		case 'MQ': case 'mq':		//	Martinique
			return '+596';	break;
		case 'MR': case 'mr':		//	Mauritania
			return '+222';	break;
		case 'MU': case 'mu':		//	Mauritius
			return '+230';	break;
		case 'YT': case 'yt':		//	Mayotte
			return '+262';	break;
		case 'MX': case 'mx':		//	Mexico
			return '+52';	break;
		case 'FM': case 'fm':		//	Micronesia, Federated States Of
			return '+691';	break;
		case 'MD': case 'md':		//	Moldova, Republic of
			return '+373';	break;
		case 'MC': case 'mc':		//	Monaco
			return '+377';	break;
		case 'MN': case 'mn':		//	Mongolia
			return '+976';	break;
		case 'ME': case 'me':		//	Montenegro
			return '+382';	break;
		case 'MS': case 'ms':		//	Montserrat
			return '+1664';	break;
		case 'MA': case 'ma':		//	Morocco
			return '+212';	break;
		case 'MM': case 'mm':		//	Myanmar
			return '+95';	break;
		case 'NA': case 'na':		//	Namibia
			return '+264';	break;
		case 'NR': case 'nr':		//	Nauru
			return '+674';	break;
		case 'NP': case 'np':		//	Nepal
			return '+977';	break;
		case 'NL': case 'nl':		//	Netherlands
			return '+31';	break;
		case 'XN': case 'xn':		//	Nevis
			return '+869';	break;
		case 'NC': case 'nc':		//	New Caledonia
			return '+687';	break;
		case 'NZ': case 'nz':		//	New Zealand
			return '+64';	break;
		case 'NI': case 'ni':		//	Nicaragua
			return '+505';	break;
		case 'NE': case 'ne':		//	Niger
			return '+227';	break;
		case 'NG': case 'ng':		//	Nigeria
			return '+234';	break;
		case 'NU': case 'nu':		//	Niue
			return '+683';	break;
		case 'NO': case 'no':		//	Norway
			return '+47';	break;
		case 'OM': case 'om':		//	Oman
			return '+968';	break;
		case 'PK': case 'pk':		//	Pakistan
			return '+92';	break;
		case 'PW': case 'pw':		//	Palau
			return '+680';	break;
		case 'PA': case 'pa':		//	Panama
			return '+507';	break;
		case 'PG': case 'pg':		//	Papua New Guinea
			return '+675';	break;
		case 'PY': case 'py':		//	Paraguay
			return '+595';	break;
		case 'PE': case 'pe':		//	Peru
			return '+51';	break;
		case 'PH': case 'ph':		//	Philippines
			return '+63';	break;
		case 'PL': case 'pl':		//	Poland
			return '+48';	break;
		case 'PT': case 'pt':		//	Portugal
			return '+351';	break;
		case 'PR': case 'pr':		//	Puerto Rico
			return array('+1787','+1939');	break;
		case 'QA': case 'qa':		//	Qatar
			return '+974';	break;
		case 'RE': case 're':		//	Reunion
			return '+262';	break;
		case 'RO': case 'ro':		//	Romania
			return '+40';	break;
		case 'RU': case 'ru':		//	Russian Federation
			return '+7';	break;
		case 'RW': case 'rw':		//	Rwanda
			return '+250';	break;
		case 'MP': case 'mp':		//	Saipan // Northern Mariana Islands
			return '+1670';	break;
		case 'WS': case 'ws':		//	Samoa
			return '+685';	break;
		case 'SM': case 'sm':		//	San Marino
			return '+378';	break;
		case 'ST': case 'st':		//	Sao Tome And Principe
			return '+239';	break;
		case 'SA': case 'sa':		//	Saudi Arabia
			return '+966';	break;
		case 'SN': case 'sn':		//	Senegal
			return '+221';	break;
		case 'RS': case 'rs':		//	Serbia
			return '+381';	break;
		case 'SC': case 'sc':		//	Seychelles
			return '+248';	break;
		case 'SL': case 'sl':		//	Sierra Leone
			return '+232';	break;
		case 'SK': case 'sk':		//	Slovakia
			return '+421';	break;
		case 'SI': case 'si':		//	Slovenia
			return '+386';	break;
		case 'SB': case 'sb':		//	Solomon Islands
			return '+677';	break;
		case 'SO': case 'so':		//	Somalia
			return '+252';	break;
		case 'XS': case 'xs':		//	Somaliland (North Somalia)
			return '+252';	break;
		case 'ZA': case 'za':		//	South Africa
			return '+27';	break;
		case 'ES': case 'es':		//	Spain
			return '+34';	break;
		case 'LK': case 'lk':		//	Sri Lanka
			return '+94';	break;
		case 'BL': case 'bl':		//	St.Barthelemy
			return '+590';	break;
		case 'XE': case 'xe':		//	St.Eustatius
			return '+5993';	break;
		case 'KN': case 'kn':		//	St.Kitts
			return '+1869';	break;
		case 'LC': case 'lc':		//	St.Lucia
			return '+1758';	break;
		case 'XM': case 'xm':		//	St.Maarten
			return '+721';	break;
		case 'VC': case 'vc':		//	St.Vincent
			return '+1784';	break;
		case 'SD': case 'sd':		//	Sudan
			return '+249';	break;
		case 'SR': case 'sr':		//	Suriname
			return '+597';	break;
		default:
			return '';
	}
}

function getCurrencyUnit($param)
{
	switch($param)
	{
		case 'MY': case 'my':
			return 'MYR';
			break;
		case 'SG': case 'sg':
			return 'SGD';
			break;
	}
}

function rate_checking($data=array()){
	$action = "MPRateCheckingBulk";
		
	switch($data['country'])
	{
		case "MY": case "my":
			$domain = EASYPARCEL_DOMAIN_MY;
			$auth = EASYPARCEL_AUTH_MY;
			$api = EASYPARCEL_API_MY;
			$bulk = array(
				array(
				'pick_code'	=> $data['postcode_from'],
				'pick_state'	=> $data['area_from'],
				'pick_country'	=> $data['from'],
				'send_code'	=> $data['postcode_to'],
				'pick_state'	=> $data['area_to'],
				'send_country'	=> $data['to'],
				'weight'	=> $data['weight'],
				'width'	=> '0',
				'length'	=> '0',
				'height'	=> '0',
				'date_coll'	=> '',
				),
			);
			$ex = `'exclude_fields'	=> array(
				'rates.*.pickup_point',
				),`;
			break;
		case "SG": case "sg":
			$domain = EASYPARCEL_DOMAIN_SG;
			$auth = EASYPARCEL_AUTH_SG;
			$api = EASYPARCEL_API_SG;
			$bulk = array(
				array(
				'pick_code'	=> $data['postcode_from'],
				'pick_country'	=> $data['from'],
				'send_code'	=> $data['postcode_to'],
				'send_country'	=> $data['to'],
				'weight'	=> $data['weight'],
				'width'	=> '0',
				'length'	=> '0',
				'height'	=> '0',
				'date_coll'	=> '',
				),
				);
				$ex = '';
			break;
		default:
			$domain = '';
			$api = '';
			$bulk = '';
			$ex = '';
	}

	$postparam = array(
	'authentication'	=> $auth,
	'api'	=> $api,
	'bulk'	=> $bulk,
	$ex
	);

	$url = $domain.$action;
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

	$json = json_decode($return, true); // true for array
	return $json;
}

function make_order($data=array()){
	$action = "MPSubmitOrderBulk";

	switch($data['pick_country'])
	{
		case "MY": case "my":
			$domain = EASYPARCEL_DOMAIN_MY;
			$auth = EASYPARCEL_AUTH_MY;
			$api = EASYPARCEL_API_MY;
			$bulk = array(
				array(
					'weight'	=> $data['weight'],
					'width'	=> '0',
					'length'	=> '0',
					'height'	=> '0',
					'content'	=> $data['content'],
					'value'	=> $data['value'],
					'service_id'	=> $data['sid'],
					'pick_point'	=> $data['pick_point'],
					'pick_name'	=> $data['pick_name'],
					'pick_company'	=> $data['pick_company'],
					'pick_contact'	=> $data['pick_contact'],
					'pick_mobile'	=> $data['pick_mobile'],
					'pick_addr1'	=> $data['pick_addr1'],
					'pick_addr2'	=> $data['pick_addr2'],
					'pick_addr3'	=> '',
					'pick_addr4'	=> '',
					'pick_city'	=> $data['pick_city'],
					'pick_state'	=> $data['pick_state'],
					'pick_code'	=> $data['pick_code'],
					'pick_country'	=> $data['pick_country'],
					'send_point'	=> $data['send_point'],
					'send_name'	=> $data['send_name'],
					'send_company'	=> $data['send_company'],
					'send_contact'	=> $data['send_contact'],
					'send_mobile'	=> $data['send_mobile'],
					'send_addr1'	=> $data['send_addr1'],
					'send_addr2'	=> $data['send_addr2'],
					'send_addr3'	=> '',
					'send_addr4'	=> '',
					'send_city'	=> $data['send_city'],
					'send_state'	=> $data['send_state'],
					'send_code'	=> $data['send_code'],
					'send_country'	=> $data['send_country'],
					'collect_date'	=> $data['collect_date'],
					'sms'	=> '0',
					'send_email'	=> $data['send_email'],
					'hs_code'	=> '',
					'reference'	=> $data['reference']
				)
				);
			break;
		case "SG": case "sg":
			$domain = EASYPARCEL_DOMAIN_SG;
			$auth = EASYPARCEL_AUTH_SG;
			$api = EASYPARCEL_API_SG;
			$bulk = array(
				array(
					'weight'	=> $data['weight'],
					'width'	=> '0',
					'length'	=> '0',
					'height'	=> '0',
					'content'	=> $data['content'],
					'value'	=> $data['value'],
					'service_id'	=> $data['sid'],
					'pick_name'	=> $data['pick_name'],
					'pick_company'	=> $data['pick_company'],
					'pick_contact'	=> $data['pick_contact'],
					'pick_mobile'	=> $data['pick_mobile'],
					'pick_unit'	=> $data['pick_addr1'],
					'pick_code'	=> $data['pick_code'],
					'pick_country'	=> $data['pick_country'],
					'send_name'	=> $data['send_name'],
					'send_company'	=> $data['send_company'],
					'send_contact'	=> $data['send_contact'],
					'send_mobile'	=> $data['send_mobile'],
					'send_unit'	=> $data['send_addr1'],
					'send_addr1'	=> $data['send_addr1'],
					'send_state'	=> $data['send_state'],
					'send_code'	=> $data['send_code'],
					'send_country'	=> $data['send_country'],
					'collect_date'	=> $data['collect_date'],
					'reference'	=> $data['reference']
				)
				);
			break;
		default:
			$domain = '';
			$api = '';
			$bulk = '';
	}

	$postparam = array(
	'authentication'	=> $auth,
	'api'	=> $api,
	'bulk'	=> $bulk,
	);

	$url = $domain.$action;
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

	$json = json_decode($return, true);
	return $json;
}

function make_order_payment($data=array()){
	$action = "MPPayOrderBulk";

	switch($data['country'])
	{
		case 'MY': case 'my':
			$domain = EASYPARCEL_DOMAIN_MY;
			$auth = EASYPARCEL_AUTH_MY;
			$api = EASYPARCEL_API_MY;
			$bulk = array(
				array(
				'order_no'	=> $data['order_number'],
				),
			);
			break;
		case 'SG': case 'sg':
			$domain = EASYPARCEL_DOMAIN_SG;
			$auth = EASYPARCEL_AUTH_SG;
			$api = EASYPARCEL_API_SG;
			$bulk = array(
				array(
				'order_no'	=> $data['order_number'],
				),
			);
			break;
	}

	$postparam = array(
	'authentication'	=> $auth,
	'api'	=> $api,
	'bulk'	=> $bulk
	);

	$url = $domain.$action;
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

	$json = json_decode($return, true);
	return $json;
}
?>