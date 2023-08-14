<?php 
$domain = "https://demo.connect.easyparcel.my/?ac=";

$action = "MPRateCheckingBulk";
$postparam = array(
'authentication'	=> 'MwxHG9i3Wu',
'api'	=> 'EP-GDDwnNR72',
'bulk'	=> array(
array(
'pick_code'	=> '059893',
'pick_country'	=> 'SG',
'send_code'	=> '059897',
'send_country'	=> 'SG',
'weight'	=> '10',
'width'	=> '0',
'length'	=> '0',
'height'	=> '0',
'date_coll'	=> '2023-08-14',
),
array(
'pick_code'	=> '059893',
'pick_country'	=> 'SG',
'send_code'	=> '059897',
'send_country'	=> 'SG',
'weight'	=> '10',
'width'	=> '0',
'length'	=> '0',
'height'	=> '0',
'date_coll'	=> '2023-08-14',
),
),
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

$json = json_decode($return);
echo "<pre>"; print_r($json); echo "</pre>";
?>