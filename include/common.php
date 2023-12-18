<?php

use PgSql\Lob;

function post($key)
{
	return isset($_POST[$key]) ? $_POST[$key] : '';
}

function postSpaceFilter($key)
{
	return trim(isset($_POST[$key]) ? $_POST[$key] : '');
}

function input($key)
{
	$results = '';
	if (isset($_GET[$key]) && !is_array($_GET[$key])) {
		$results = isset($_GET[$key]) && strlen($_GET[$key]) <= 256 ? /* globalSanitizeFilter($_GET[$key], $key) */ $_GET[$key] : '';
	}
	return xssFilter($results);
}


function searchInput($key)
{
	$input = input($key);
	//check the input query string with script tags will return empty string
	if (preg_match("/<script(.*?)>(.*?)<\/script>/is", $input) || preg_match("/<script(.*?)>/is", $input)) {
		return '';
	}
	$input = strip_tags($input);
	return trim(preg_replace('/[^(a-zA-Z0-9.()\-,\/)\&\'\"]+/i', ' ', $input));
}

function numberInput($key)
{
	$val = input($key);
	return isNumber($val) ? $val : '';
}


function isNumber($str)
{
	return preg_match("/^[0-9]+$/", $str);
}

function isEmail($str)
{
	return preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,})$/", $str);
}

function getSelfUrl()
{
	$s = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' ? 's' : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '');
	$protocol = strLeft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
	$url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
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

function redirect($addr, $alert = '')
{
	global $siteOrlocalMode;
	if ($alert)
		$_SESSION['global_flash_alert'] = $alert;

	$url = $addr;
	if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) {
		$url = str_ireplace(array('http://', 'https://'), '', $url);
		if (!preg_match('/^[a-z0-9-_]+\.(beyourdiary)\.com/i', $url) && $siteOrlocalMode)
			$addr = SITEURL;
	}
	header("Location:" . $addr);
	exit();
}

function myCurl($url, $ops = array())
{
	if (stripos($url, 'https://uat.cms.beyourdiary') !== false) {
		$url = str_replace('https://', 'http://', $url);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	if (!array_key_exists('CURLOPT_RETURNTRANSFER', $ops))
		$ops['CURLOPT_RETURNTRANSFER'] = true;
	if (!array_key_exists('CURLOPT_CONNECTTIMEOUT', $ops))
		$ops['CURLOPT_CONNECTTIMEOUT'] = 1;
	if (!array_key_exists('CURLOPT_TIMEOUT', $ops))
		$ops['CURLOPT_TIMEOUT'] = 1;

	foreach ($ops as $op => $val) {
		curl_setopt($ch, constant($op), $val);
	}

	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function generateShortURL($url)
{
	$url = urlencode($url);
	$ch = curl_init();
	$timeout = 5;
	$result = '';
	curl_setopt($ch, CURLOPT_URL, 'http://api.bit.ly/v3/shorten?login=paustina&apiKey=' . BITLYKEY . '&uri=' . $url . '&format=txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	// Execute the post
	$result = curl_exec($ch);
	// Close the connection
	curl_close($ch);
	// Return the result

	return $result;
}

function googleShortURL($longurl)
{
	$data = array('dynamicLinkInfo' => array('dynamicLinkDomain' => 'beyourdiary.co', 'link' => $longurl));
	$data_string = json_encode($data);

	$ch = curl_init('https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=' . fbaseURLSHORTERNER);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt(
		$ch,
		CURLOPT_HTTPHEADER,
		array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string)
		)
	);

	$result = curl_exec($ch);

	$decodeResult =  json_decode($result);
	$firebaseShortURL = isset($decodeResult->shortLink) ? $decodeResult->shortLink : '';

	return $firebaseShortURL;
}

function strLeft($s1, $s2)
{
	return substr($s1, 0, strpos($s1, $s2));
}

function shippingDetail_Curl($ship_action, $postparam)
{
	global $curl_ship_domain;
	$return = '';
	$url = $curl_ship_domain . $ship_action;
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

function convertobj($element)
{
	$return_arr = '';
	foreach ($element as $x => $value) {
		$return_arr[] = $value;
	}
	return $return_arr;
}

function convertCapitalCase($param)
{
	return trim(strtoupper($param));
}

function convertSmallerCase($param)
{
	return trim(strtolower($param));
}

function convertFirstEachWordCap($param)
{
	return trim(ucwords($param));
}

function isStatusFieldAvailable($tbl, $conn)
{
	$query = "SHOW COLUMNS FROM $tbl LIKE 'status'";
	$result = $conn->query($query);

	return $result && $result->num_rows > 0;
}

function isDuplicateRecord($fieldName, $fieldValue, $tbl, $connect, $primaryKeyValue)
{
	if ($fieldValue !== null) {
		$query = "SELECT COUNT(*) as count FROM `$tbl` WHERE `$fieldName` = '$fieldValue' AND `status` = 'A'";
		//Help to check the query where wrong
		// If editing an existing record, exclude the current record from the duplicate check
		if ($primaryKeyValue) {
			$query .= " AND id != '$primaryKeyValue'";
		}

		$result = mysqli_query($connect, $query);

		if ($result) {
			$row = mysqli_fetch_assoc($result);
			$count = $row['count'];
			return $count > 0; // If count is greater than 0, it's a duplicate
		}
	}
}

function getData($search_val, $val, $tbl, $conn)
{

	$statusAvailable = isStatusFieldAvailable($tbl, $conn);

	//Checking a status is available in data field or not then check a val is exist or not
	if ($statusAvailable) {
		$chk_val = $val == '' ? "WHERE status = 'A' " : "WHERE $val AND status = 'A'";
	} else {
		$chk_val = $val == '' ? "" : "WHERE $val";
	}
	//combine together to process a query
	$query = "SELECT $search_val FROM $tbl " . $chk_val . "order by id desc";

	$result = $conn->query($query);

	if (empty($result) && $result->num_rows == 0)
		return false;
	else
		return $result;
}

function generateDBData($tblname, $conn)
{
	$rst = getData('*', '', $tblname, $conn);
	$data = array();
	while ($row = $rst->fetch_assoc()) {
		$data[] = $row;
	}
	$encode_rst = json_encode($data);

	$path = "./data/" . "$tblname.json";

	$f = fopen($path, 'w');
	fwrite($f, $encode_rst);
	fclose($f);
}

function audit_log($data = array())
{
	if (count($data) > 0) {
		extract($data);

		switch (strtolower($log_act)) {
			case 'view': 
				$query = "INSERT INTO " . AUDIT_LOG . " (log_action, screen_type, user_id, action_message, create_date, create_time, create_by) VALUES ('1', '$page', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'edit':
				$query = "INSERT INTO " . AUDIT_LOG . " (log_action, screen_type, query_record, query_table, old_value, changes, user_id, action_message, create_date, create_time, create_by) VALUES ('2', '$page', \"$query_rec\", '$query_table', '$oldval', '$changes', '$uid', \"$act_msg\", '$cdate', '$ctime', '$cby')";
				break;
			case 'delete':
				$query = "INSERT INTO " . AUDIT_LOG . " (log_action, screen_type, query_record, query_table, user_id, action_message, create_date, create_time, create_by) VALUES ('3', '$page', \"$query_rec\", '$query_table', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'add':
				$query = "INSERT INTO " . AUDIT_LOG . " (log_action, screen_type, query_record, query_table, new_value, user_id, action_message, create_date, create_time, create_by) VALUES ('4', '$page', \"$query_rec\", '$query_table', '$newval', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'import':
				$query = "INSERT INTO " . AUDIT_LOG . " (log_action, screen_type, query_record, query_table, new_value, user_id, action_message, create_date, create_time, create_by) VALUES ('5', '$page', \"$query_rec\", '$query_table', '$newval', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'export':
				$query = "INSERT INTO " . AUDIT_LOG . " (log_action, screen_type, user_id, action_message, create_date, create_time, create_by) VALUES ('6', '$page', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'login':
				$query = "INSERT INTO " . AUDIT_LOG . " (screen_type, log_action, user_id, action_message, create_date, create_time, create_by) VALUES ('Login Screen', '7', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
			case 'logout':
				$query = "INSERT INTO " . AUDIT_LOG . " (screen_type, log_action, user_id, action_message, create_date, create_time, create_by) VALUES ('Login Screen', '8', '$uid', '$act_msg', '$cdate', '$ctime', '$cby')";
				break;
		}

		if (isset($query))
			mysqli_query($connect, $query);
	}
}

function getCountry($param, $connect)
{
	$all_country = array();

	$result = getData('*', '', 'countries', $connect);

	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$all_country[$row['code']] = $row['name'];
		}
	}

	switch ($param) {
		case 'MY':
		case 'my':
			return 'Malaysia';
			break;
		case 'SG':
		case 'sg':
			return 'Singapore';
			break;
		case 'all':
		case 'All':
			return $all_country;
			break;
	}
}

function getCountryTelCode($param, $connect)
{
	$result = getData('*', 'code = "' . $param . '"', 'countries', $connect);

	if ($result) {
		$row = $result->fetch_assoc();

		if ($row) {
			return "+" . $row['phonecode'];
		} else {
			return 'No data found';
		}
	} else {
		return 'Query failed';
	}
}

function getCurrencyUnit($param)
{
	switch ($param) {
		case 'MY':
		case 'my':
			return 'MYR';
			break;
		case 'SG':
		case 'sg':
			return 'SGD';
			break;
	}
}

function rate_checking($data = array())
{
	$action = "MPRateCheckingBulk";

	switch ($data['country']) {
		case "MY":
		case "my":
			$domain = EASYPARCEL_DOMAIN_MY;
			$auth = EASYPARCEL_AUTH_MY;
			$api = EASYPARCEL_API_MY;
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
			$ex = `'exclude_fields'	=> array(
				'rates.*.pickup_point',
				),`;
			break;
		case "SG":
		case "sg":
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

	$url = $domain . $action;
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

function make_order($data = array())
{
	$action = "MPSubmitOrderBulk";

	switch ($data['pick_country']) {
		case "MY":
		case "my":
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
		case "SG":
		case "sg":
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

	$url = $domain . $action;
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

function make_order_payment($data = array())
{
	$action = "MPPayOrderBulk";

	switch ($data['country']) {
		case 'MY':
		case 'my':
			$domain = EASYPARCEL_DOMAIN_MY;
			$auth = EASYPARCEL_AUTH_MY;
			$api = EASYPARCEL_API_MY;
			$bulk = array(
				array(
					'order_no'	=> $data['order_number'],
				),
			);
			break;
		case 'SG':
		case 'sg':
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

	$url = $domain . $action;
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


function displayPageAction($act, $page)
{
	switch ($act) {
		case 'I':
			return "Add $page";
		case 'E':
			return "Edit $page";
		default:
			return "View $page";
	}
}

function implodeWithComma($data)
{
	return implode(",", $data);
}

function actMsgLog($oldvalarr = array(), $chgvalarr = array(), $tblName, $errorMsg)
{
	$actMsg = USER_NAME . (empty($errorMsg) ? "" : " fail to") . " edited the data";

	for ($i = 0; $i < sizeof($oldvalarr); $i++) {
		if ($i == 0)
			$actMsg .= " from <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
		else
			$actMsg .= ", <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
	}
	$actMsg .= "  under <b><i>$tblName Table</i></b>.";

	(!empty($errorMsg)) ? $actMsg .= "( $errorMsg )" : '';

	return $actMsg;
}

// Function to update previous and final amounts for transactions
function updateTransactionAmounts($finance_connect, $table_name) {
    // Initialize an associative array to store previous amounts for each bank and currency combination
    $prevAmounts = array();

    // Select all transactions ordered by id
    $query = "SELECT id, `type`, amount, bank, currency, `status` FROM $table_name WHERE `status` <> 'D' ORDER BY id";
    $result = mysqli_query($finance_connect, $query);

    if (!$result) {
        die("Error reading records: " . mysqli_error($finance_connect));
    }

    // Loop through each transaction
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $type = $row['type'];
        $amount = $row['amount'];
        $currency = $row['currency'];
        $bank = $row['bank'];

        $key = $bank . '_' . $currency;

        if (!isset($prevAmounts[$key])) {
            $prevAmounts[$key] = 0;
        }
        $prevFinalAmt = $prevAmounts[$key];

        // Calculate final_amt based on transaction type
        if ($type === 'Add') {
            $finalAmt = $prevFinalAmt + $amount;
        } else if ($type === 'Deduct') {
            $finalAmt = $prevFinalAmt - $amount;
        }

        // Update the row in the database
        $updateQuery = "UPDATE $table_name SET prev_amt ='$prevFinalAmt', final_amt ='$finalAmt' WHERE id = '$id'";
        $updateResult = mysqli_query($finance_connect, $updateQuery);

        if (!$updateResult) {
            die("Update failed: " . mysqli_error($finance_connect));
        }

        $prevAmounts[$key] = $finalAmt;
    }
	return true;
}
?>
