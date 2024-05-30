<?php
if (isset($isFinance) && ($isFinance ==1)) {
    include '../init.php';
} else if (empty($isFinance) || !(isset($isFinance) || $isFinance == null)) {
    include 'init.php';
}
$path =  $_SERVER['PHP_SELF'];
$path = explode("/", $path);
$login_url = $SITEURL."/index.php";

if(!($path[sizeof($path)-1] == 'forgotPassword.php'))
    if(!(isset($_SESSION['userid'])))
        echo("<script>location.href = '$login_url';</script>");
/* 
include ROOT.'/include/access.php';

include ROOT.'/include/header.php';
include ROOT.'/include/common.php';
include ROOT.'/include/breadcrumb.php';
include ROOT.'/include/menu.php';
include ROOT.'/include/sideMenu.php';
include ROOT.'/include/footer.php'; */

// include ROOT.'/includes/get_country.php';
// include ROOT.'/auditlog/auditor.php';

include ROOT.'/recordDelete.php';
?>