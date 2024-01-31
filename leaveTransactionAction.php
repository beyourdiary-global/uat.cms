<?php

$pageTitle = '';

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = L_PENDING;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = '';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");

//Check a current page pin is exist or not
$pageAction = getPageAction($act);

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Approval/Reject Leave Transaction
if ($act) {
    switch ($act) {
        case 'LA':
            $action = "approval";
            $resultSuccessApproval = getData('success_approver', 'id="' . $dataID . '"', '', $tblName, $connect);
            $rowSuccessApproval = $resultSuccessApproval->fetch_assoc();

            $string = $rowSuccessApproval['success_approver'];

            if ($string) {
                $array = explode(",", $string);

                array_push($array, USER_ID);

                $successApproval = implode(",", $array);
            } else {
                $successApproval = USER_ID;
            }

            $query = "UPDATE $tblName SET success_approver = '$successApproval' WHERE id = '$dataID '";
            mysqli_query($connect, $query);

            $resultPendingApproval = getData('pending_approver', 'id="' . $dataID . '"', '', $tblName, $connect);
            $rowPendingApproval = $resultPendingApproval->fetch_assoc();

            $string = $rowPendingApproval['pending_approver'];
            $array = explode(",", $string);

            $indexToRemove = array_search(USER_ID, $array);
            unset($array[$indexToRemove]);

            if (count($array)) {
                $newString = implode(",", $array);

                $query = "UPDATE $tblName SET pending_approver = '$newString' WHERE id = '$dataID '";
                mysqli_query($connect, $query);
            } else {
                $query = "UPDATE $tblName SET pending_approver = '' WHERE id = '$dataID '";
                mysqli_query($connect, $query);
                $query = "UPDATE $tblName SET leave_transaction_status = 'approval' WHERE id = '$dataID '";
                mysqli_query($connect, $query);
            }

            break;
        case 'LD':
            //Set status to declined 
            $action = "declined";
            $query = "UPDATE $tblName SET leave_transaction_status = 'declined' WHERE id = '$dataID '";
            mysqli_query($connect, $query);

            $resultManager = getData('pending_approver', 'id="' . $dataID . '"', '', $tblName, $connect);
            $rowManager = $resultManager->fetch_assoc();

            $string = $rowManager['pending_approver'];
            $array = explode(",", $string);
            $indexToRemove = array_search(USER_ID, $array);

            if ($indexToRemove !== false) {
                unset($array[$indexToRemove]);
            }

            $newString = implode(",", $array);

            $query = "UPDATE $tblName SET pending_approver = '$newString' WHERE id = '$dataID '";
            mysqli_query($connect, $query);

            $query = "UPDATE $tblName SET reject_approver = '" . USER_ID . "' WHERE id = '$dataID '";
            mysqli_query($connect, $query);

            break;
    }

    // audit log
    $log = [
        'log_act'       => $action,
        'cdate'         => $cdate,
        'ctime'         => $ctime,
        'uid'           => USER_ID,
        'cby'           => USER_ID,
        'act_msg'       => (isset($errorMsg)) ? USER_NAME . " failed to $action the leave transaction [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>. ( $errorMsg )" : USER_NAME . " $action the leave transaction [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.",
        'query_rec'     => $query,
        'query_table'   => $tblName,
        'page'          => $pageTitle,
        'connect'       => $connect,
    ];

    audit_log($log);
}
