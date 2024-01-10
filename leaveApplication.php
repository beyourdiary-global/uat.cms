<?php

$leavePendingTblName = L_PENDING;

//Get Leave Application ID and action
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';

$errorRedirectLink = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script><script>location.href ='$SITEURL/dashboard.php';</script>";

//Attachment IMG
$leaveAttachmentPath = './' . ATCH . 'pending_leave/';
$allowed_ext = array("png", "jpg", "jpeg", "svg");

if (!file_exists($leaveAttachmentPath)) {
    mkdir($leaveAttachmentPath, 0777, true);
}

//Leave Application Result By ID 
if ($dataID) {
    $leavePendingResult = getData('*', 'id = "' . $dataID . '"', '', $leavePendingTblName, $connect);

    if (!$leavePendingResult) {
        echo $errorRedirectLink;
    }

    $rowLeavePending = $leavePendingResult->fetch_assoc();
}

//Leave Application Edit,Delete,Add

//Delete Leave Application
if ($act == 'D') {
    deleteRecord($leavePendingTblName, $dataID, $leavePendingResult['numOfdays'], $connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

$action = post('actionBtn');

if ($action) {

    $leaveType = postSpaceFilter('leaveType');
    $fromTime = formatTime(postSpaceFilter('fromTime'));
    $toTime = formatTime(postSpaceFilter('toTime'));
    $numOfdays = postSpaceFilter('numOfdays');
    $remainingLeave = postSpaceFilter('remainingLeave');
    $attachment = postSpaceFilter('leaveAttachment');
    $remark = postSpaceFilter('remark');

    $resultLeave = getData('name', 'id = "' . $leaveType . '"', '', L_TYPE, $connect);

    if (!$resultLeave) {
        echo $errorRedirectLink;
    }

    $leaveRow = $resultLeave->fetch_assoc();
    $leaveTypeName = $leaveRow['name'];

    if ($_FILES["leaveAttachment"]["size"] != 0) {
        $leaveAttachment = $_FILES["leaveAttachment"]["name"];
        $leaveAttachment_tmp_name = $_FILES["leaveAttachment"]["tmp_name"];
        $img_ext = pathinfo($leaveAttachment, PATHINFO_EXTENSION);
        $img_ext_lc = strtolower($img_ext);
    }

    switch ($action) {
        case 'addLeave':
        case 'editLeave':

            if (in_array($img_ext_lc, $allowed_ext)) {
                move_uploaded_file($leaveAttachment_tmp_name, $leaveAttachmentPath . $leaveAttachment);
            } else {
                $err2 = "Only allow PNG, JPG, JPEG or SVG file";
                $errCount = 1;
            }

            $oldvalarr = $chgvalarr = $newvalarr = array();

            $values = [
                'leave_type' => $leaveType,
                'from_time' => $fromTime,
                'to_time' => $toTime,
                'numOfdays' => $numOfdays,
                'remainingLeave' => $remainingLeave,
                'attachment' => $leaveAttachment,
                'remark' => $remark
            ];

            if ($action == 'addLeave') {

                $leaveApplicationAction = 'add';

                foreach ($values as $fieldName => $value) {
                    if ($value) {
                        array_push($newvalarr, $value);
                    }
                }

                try {
                    $query = "INSERT INTO " . $leavePendingTblName . "(leave_type,from_time,to_time,numOfdays,remainingLeave,attachment,remark,create_by,create_date,create_time)VALUES('$leaveType','$fromTime','$toTime','$numOfdays','$remainingLeave','$leaveAttachment','$remark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            } else {

                $leaveApplicationAction = 'edit';

                foreach ($values as $fieldName => $value) {
                    if ($rowLeavePending[$fieldName] != $value) {
                        array_push($oldvalarr, $rowLeavePending[$fieldName]);
                        array_push($chgvalarr, $value);
                    }
                }

                try {
                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE $leavePendingTblName SET leave_type='$leaveType', from_time='$fromTime', to_time='$toTime', numOfdays='$numOfdays', remainingLeave='$remainingLeave', attachment='$leaveAttachment', remark='$remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($connect, $query);
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            }

            if (isset($errorMsg)) {
                $errorMsg = str_replace('\'', '', $errorMsg);
            }

            // audit log
            if (isset($query)) {

                $successSubmitLeave = 1;

                $log = [
                    'log_act'      => $leaveApplicationAction,
                    'cdate'        => $cdate,
                    'ctime'        => $ctime,
                    'uid'          => USER_ID,
                    'cby'          => USER_ID,
                    'query_rec'    => $query,
                    'query_table'  => $leavePendingTblName,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($leaveApplicationAction == 'add') {

                    $log['newval'] = implodeWithComma($newvalarr);

                    if (isset($returnData)) {
                        $log['act_msg'] = USER_NAME . " added <b>$leaveTypeName [$numOfdays Day]</b> into <b><i>$leavePendingTblName Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>$leaveTypeName [$numOfdays Day]</b> into <b><i>$leavePendingTblName Table</i></b> ( $errorMsg )";
                    }
                } else if ($leaveApplicationAction == 'edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($oldvalarr, $chgvalarr, $leavePendingTblName, (isset($returnData) ? '' : $errorMsg));
                }

                audit_log($log);

                echo "<script>localStorage.clear();</script>";
                echo "<script>location.href ='$SITEURL/employeeDetailsTable.php';</script>";
            }

            break;
        default:
            break;
    }
}
