<?php
include "include/common.php";
include "include/common_variable.php";
include "include/connection.php";
 
$searchText = mysqli_real_escape_string($connect,post('searchText'));
$searchType = post('searchType');
$tblname = postSpaceFilter('tblname');
$path = "./data/" . "$tblname.json";

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
        if($bool !== false)
        {
            array_push($rstArr,$x);
        }
    }
}

if($searchType == 'buyer_username'){
    if(sizeof($rstArr) == 0)
    {
        $rstArr[0]['desc'] = '<button type="button" onclick="toggleNewBuyer()">Create New Customer ID</button>';
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