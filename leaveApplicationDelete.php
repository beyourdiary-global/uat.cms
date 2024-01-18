<?php
$pageTitle = "Leave Application";
include 'menuHeader.php';
$leavePendingTblName = L_PENDING;

//Get Leave Application ID and action
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');

$_SESSION['act'] = '';
$_SESSION['delChk'] = '';

//Leave Application Result By ID 
if ($dataID) {
    $leavePendingResult = getData('*', 'id = "' . $dataID . '"', '', $leavePendingTblName, $connect);

    if (!$leavePendingResult) {
        echo $errorRedirectLink;
    }

    $rowLeavePending = $leavePendingResult->fetch_assoc();
}

if ($act == 'D') {
    deleteRecord($leavePendingTblName, $dataID, '123', $connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}
