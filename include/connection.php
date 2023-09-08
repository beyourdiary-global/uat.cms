<?php
include 'init.php';

if(!(isset($_SESSION['userid'])))
    echo("<script>location.href = 'index.php';</script>");
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

?>