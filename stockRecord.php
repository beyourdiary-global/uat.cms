<?php
include "./include/common.php";
include "./include/connection.php";

$barcode = input('barcode');
$pkg_id =  input('pkg_id');
$whse_id =  input('whse_id');
$usr_id = $_SESSION['userid'];
// checking
$rst_stock = getData('*',"barcode = $barcode",'',STK_REC,$connect);

if (!$rst_stock) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}

if($rst_stock && $rst_stock != 0)
    $redirect = "stockIn.php?barcode=$barcode&pkg_id=$pkg_id&whse_id=$whse_id&usr_id=$usr_id";
else
    $redirect = "stockOut.php?barcode=$barcode&pkg_id=$pkg_id&whse_id=$whse_id&usr_id=$usr_id";

if($redirect)
    echo("<script>location.href = '$redirect';</script>");
?>