<?php
include "include/common.php";
include "include/common_variable.php";
include "include/connection.php";
 
$searchText = mysqli_real_escape_string($connect,post('searchText'));
$searchType = post('searchType');
$tblname = postSpaceFilter('tblname');
$path = "./data/" . "$tblname.json";
$prod_id = $_POST['pkg'];
$whse_id = $_POST['whse'];
$usr_id = $_POST['usr'];

$f = fopen($path, 'r');
$c = fread($f, filesize($path));
fclose($f);


$cArr = json_decode($c,true);

$rstArr = array();
if($searchText != '')
{
    foreach($cArr as $x)
    {
        $bool = stripos($x[$searchType], $searchText);
        if($bool !== false &&(($x['package'])==$prod_id) &&(isset($x['order_status']) && strtoupper($x['order_status']) === 'WP'))
        {
            array_push($rstArr,$x);
        }
    }
}
if($searchType == 'buyer_username'){
    if(sizeof($rstArr) == 0)
    {
        $rstArr[0]['desc'] = '<a href="shopee_cust_info.php?act=I">Add a New Shopee Buyer</a>';
        $rstArr[0]['val'] = "emptyValue";
    }
    
}else{
    if(sizeof($rstArr) == 0)
    {
        $rstArr[0]['desc'] = '<i>No result</i>';
        $rstArr[0]['val'] = "emptyValue";
    }

}


echo json_encode($rstArr);
?>