<?php
$pageTitle = "Leave Transaction";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = L_PENDING;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
if (input('page')) {
    $redirect_page = $SITEURL . '/allLeaveTransaction.php';
    $pageTitleAccess = "All Leave Transaction";
} else {
    $redirect_page = $SITEURL . '/myLeaveTransaction.php';
    $pageTitleAccess = "My Leave Transaction";
}

$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitleAccess);

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Attachment IMG
$leaveAttachmentPath = '.' . ATCH . 'pending_leave/';
$allowed_ext = array("png", "jpg", "jpeg", "svg");

if (!file_exists($leaveAttachmentPath)) {
    mkdir($leaveAttachmentPath, 0777, true);
}

//Leave Application Result By ID 
if ($dataID) {
    $leavePendingResult = getData('*', 'id = "' . $dataID . '"', '', $tblName, $connect);

    if (!$leavePendingResult) {
        echo $errorRedirectLink;
    }

    $rowLeavePending = $leavePendingResult->fetch_assoc();
}

//Current Employee ID 
if (!input('empID')) {
    $userResult = getData('name', 'id="' . USER_ID . '"', '', USR_USER, $connect);

    if (!$userResult) {
        echo $errorRedirectLink;
    }

    $userRow = $userResult->fetch_assoc();
    $userName = $userRow['name'];

    $empResult = getData('*', 'name="' . $userName  . '"',  '', EMPPERSONALINFO, $connect);
    if (!$empResult) {
        echo $errorRedirectLink;
    }

    $empRow = $empResult->fetch_assoc();
    $currEmpID = $empRow['id'];
} else
    $currEmpID = input('empID');

//Manager Approver
$resultManagerApprover = getData('managers_for_leave_approval', 'employee_id="' . $currEmpID . '"', '', 'employee_info', $connect);

if (!$resultManagerApprover) {
    echo $errorRedirectLink;
}
$rowManagerApprover = $resultManagerApprover->fetch_assoc();
$managerApprover = $rowManagerApprover['managers_for_leave_approval'];

//Get Date
$currentEmpLeaveApplicationResult = getData('*', 'applicant="' . $currEmpID . '"', '', $tblName, $connect);

if (!$currentEmpLeaveApplicationResult) {
    echo $errorRedirectLink;
}

$leaveApplyDateArr = array();

while ($rowCurrentEmpLeaveTransaction = $currentEmpLeaveApplicationResult->fetch_assoc()) {

    $resultLeaveType = getData('name', "id='" . $rowCurrentEmpLeaveTransaction['leave_type'] . "'", '', L_TYPE, $connect);

    if (!$resultLeaveType) {
        echo $errorRedirectLink;
    }

    $rowLeaveType = $resultLeaveType->fetch_assoc();

    if (!empty($rowCurrentEmpLeaveTransaction['leave_type'])) {
        array_push($leaveApplyDateArr, $rowCurrentEmpLeaveTransaction['from_time'] . '->' . $rowCurrentEmpLeaveTransaction['to_time']);
    }
}
$leaveApplyDateArrJSON = json_encode($leaveApplyDateArr);

//Leave Application Edit,Delete,Add

if ($act == 'LC') {
    try {
        $query =  "UPDATE $tblName SET leave_transaction_status = 'cancel' WHERE id = " . $dataID;
        mysqli_query($connect, $query);
    } catch (Exception $e) {
        $errorMsg =  $e->getMessage();
    }

    if (isset($errorMsg)) {
        $errorMsg = str_replace('\'', '', $errorMsg);
    }

    // audit log
    $log = [
        'log_act'       => 'Cancel',
        'cdate'         => $cdate,
        'ctime'         => $ctime,
        'uid'           => USER_ID,
        'cby'           => USER_ID,
        'act_msg'       => (isset($errorMsg)) ? USER_NAME . " failed to cancel the leave transaction [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>. ( $errorMsg )" : USER_NAME . " cancel the leave transaction [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.",
        'query_rec'     => $query,
        'query_table'   => $tblName,
        'page'          => $pageTitle,
        'connect'       => $connect,
    ];

    audit_log($log);
}

$action = post('actionBtn');

if ($action) {

    $leaveType = postSpaceFilter('leaveType');
    $fromTime = formatTime(postSpaceFilter('fromTime'));
    $toTime = formatTime(postSpaceFilter('toTime'));
    $numOfdays = postSpaceFilter('numOfdays');
    $remainingLeave = postSpaceFilter('remainingLeave');
    $remark = postSpaceFilter('remark');

    $imgExist = false;

    if (postSpaceFilter('leaveAttachmenetImgValue')) {
        $leaveAttachment = postSpaceFilter('leaveAttachmenetImgValue');
    }

    switch ($action) {
        case 'addData':
        case 'updData':

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

                $imgExist = true;
            }

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            $values = [
                'leave_type' => $leaveType,
                'from_time' => $fromTime,
                'to_time' => $toTime,
                'numOfdays' => $numOfdays,
                'remainingLeave' => $remainingLeave,
                'attachment' => $leaveAttachment,
                'remark' => $remark
            ];

            if ($action == 'addData') {

                $leaveApplicationAction = 'add';
                $actLeave = 'I';

                foreach ($values as $fieldName => $value) {
                    if ($value) {
                        array_push($newvalarr, $value);
                        array_push($datafield, $fieldName);
                    }
                }

                $_SESSION['tempValConfirmBox'] = true;

                try {
                    $query = "INSERT INTO " . $tblName . "(applicant,leave_type,from_time,to_time,numOfdays,remainingLeave,attachment,pending_approver,leave_transaction_status,remark,create_by,create_date,create_time)VALUES('$currEmpID','$leaveType','$fromTime','$toTime','$numOfdays','$remainingLeave','$leaveAttachment','$managerApprover','pending','$remark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);

                    if ($imgExist)
                        move_uploaded_file($leaveAttachment_tmp_name, $leaveAttachmentPath . $leaveAttachment);

                    $dataID = $connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $actLeave = "F";
                }
            } else {

                $leaveApplicationAction = 'edit';
                $actLeave = 'E';

                if ($rowLeavePending['numOfdays'] == $values['numOfdays']) {
                    unset($values['remainingLeave']);
                }

                foreach ($values as $fieldName => $value) {
                    if ($rowLeavePending[$fieldName] != $value) {
                        array_push($oldvalarr, $rowLeavePending[$fieldName]);
                        array_push($chgvalarr, $value);
                        array_push($datafield, $fieldName);
                    }
                }

                $_SESSION['tempValConfirmBox'] = true;

                if ($oldvalarr && $chgvalarr) {
                    try {
                        $query = "UPDATE $tblName SET leave_type='$leaveType', from_time='$fromTime' ,to_time='$toTime', numOfdays='$numOfdays', attachment='$leaveAttachment', remark='$remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";

                        if ($imgExist)
                            move_uploaded_file($leaveAttachment_tmp_name, $leaveAttachmentPath . $leaveAttachment);

                        $returnData = mysqli_query($connect, $query);
                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        $actLeave = "F";
                    }
                } else {
                    $actLeave = 'NC';
                }
            }

            //Sending Mail Notification
            if ($actLeave === 'I') {

                $leaveTypeResult = getData('name', 'id="' . $leaveType . '"', '', L_TYPE, $connect);

                if (!$leaveTypeResult) {
                    echo $errorRedirectLink;
                }

                $leaveRow = $leaveTypeResult->fetch_assoc();
                $leaveTypeName = $leaveRow['name'];

                //Email Notification For Manager Approvel
                $managerArray = explode(',', $managerApprover);

                for ($x = 0; $x < count($managerArray); $x++) {

                    //Leave Type
                    $managerGmail = getData('email', 'id="' . $managerArray[$x] . '"', '', USR_USER, $connect);

                    if (!$managerGmail) {
                        echo $errorRedirectLink;
                    }

                    $managerGmailRow = $managerGmail->fetch_assoc();
                    $emailManager =  $managerGmailRow['email'];

                    ob_start();
                    $to = $emailManager;
                    $subject = 'Employee Leave Application';

                    $message = '
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">
                    </head>
                    <body>
                        <h3 style="margin-bottom:12px">You Have Received A Leave Application For Approval From The Employee <b>\'' . $userName . '\'</b></h3>
    
                        <table style="border-collapse: collapse;width: 100%;">
                            <thead style="background-color: #eee;">
                                <tr>
                                    <th style="border: 1px solid black;padding: 5px;">Leave Type</th>
                                    <th style="border: 1px solid black;padding: 5px;">From</th>
                                    <th style="border: 1px solid black;padding: 5px;">To</th>
                                    <th style="border: 1px solid black;padding: 5px;">Total Days Leave</th>
                                    <th style="border: 1px solid black;padding: 5px;">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid black;padding: 5px;">' . $leaveTypeName . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $fromTime . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $toTime . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $numOfdays . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $remark . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>                
                    ';

                    // To send HTML mail, the Content-type header must be set
                    $headers[] = 'MIME-Version: 1.0';
                    $headers[] = 'Content-type: text/html; charset=utf-8';

                    // Additional headers
                    $headers[] = 'To: <' . $to . '>';
                    $headers[] = 'From: noreply <noreply@beyourdiary.com>';
                    $headers[] = 'Cc:' . email_cc . '';
                    $headers[] = 'Bcc:';

                    //SETUP A php.ini sendmail
                    ini_set('SMTP', 'smtp-relay.brevo.com');
                    ini_set('smtp_port', '587');
                    ini_set('sendmail_from', 'fankaixuan159@gmail.com');
                    ini_set('sendmail_path', "C:\xampp\sendmail\sendmail.exe' -t");

                    try {
                        echo mail($to, $subject, $message, implode("\r\n", $headers));
                    } catch (Exception $e) {
                        echo 'Caught exception: ', $e->getMessage();
                    }

                    ob_get_clean();
                }

                $headers = array();

                //Email Notification For Applicant
                $empEmailResult = getData('email', 'id="' . $currEmpID  . '"',  '', EMPPERSONALINFO, $connect);
                if (!$empEmailResult) {
                    echo $errorRedirectLink;
                }

                $empEmailRow = $empEmailResult->fetch_assoc();
                $emailApplicant = $empEmailRow['email'];

                if ($emailApplicant) {
                    ob_start();
                    $to = $emailApplicant;
                    $subject = 'Employee Leave Application';

                    $message = '
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">
                    </head>
                    <body>
                        <h3 style="margin-bottom:12px">You Leave Application Have Successfully Submitted And Pending Approval</h3>
    
                        <table style="border-collapse: collapse;width: 100%;">
                            <thead style="background-color: #eee;">
                                <tr>
                                    <th style="border: 1px solid black;padding: 5px;">Leave Type</th>
                                    <th style="border: 1px solid black;padding: 5px;">From</th>
                                    <th style="border: 1px solid black;padding: 5px;">To</th>
                                    <th style="border: 1px solid black;padding: 5px;">Total Days Leave</th>
                                    <th style="border: 1px solid black;padding: 5px;">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid black;padding: 5px;">' . $leaveTypeName . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $fromTime . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $toTime . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $numOfdays . '</td>
                                    <td style="border: 1px solid black;padding: 5px;">' . $remark . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>                
                    ';

                    // To send HTML mail, the Content-type header must be set
                    $headers[] = 'MIME-Version: 1.0';
                    $headers[] = 'Content-type: text/html; charset=utf-8';

                    // Additional headers
                    $headers[] = 'To: <' . $to . '>';
                    $headers[] = 'From: noreply <noreply@beyourdiary.com>';
                    $headers[] = 'Cc:' . email_cc . '';
                    $headers[] = 'Bcc:';

                    //SETUP A php.ini sendmail
                    ini_set('SMTP', 'smtp-relay.brevo.com');
                    ini_set('smtp_port', '587');
                    ini_set('sendmail_from', 'fankaixuan159@gmail.com');
                    ini_set('sendmail_path', "C:\xampp\sendmail\sendmail.exe' -t");

                    try {
                        echo mail($to, $subject, $message, implode("\r\n", $headers));
                    } catch (Exception $e) {
                        echo 'Caught exception: ', $e->getMessage();
                    }

                    ob_get_clean();
                }
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
                    'query_table'  => $tblName,
                    'page'         => 'Leave Application',
                    'connect'      => $connect,
                ];

                if ($leaveApplicationAction == 'add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $leaveApplicationAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($leaveApplicationAction == 'edit') {
                    $log['oldval']  = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $leaveApplicationAction, (isset($returnData) ? '' : $errorMsg));
                }

                audit_log($log);
            }
            break;

        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
        default:
            break;
    }

    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo "<script>setCookie('leaveAppID','', 0);</script>";
        echo "<script>localStorage.clear();</script>";
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $actLeave . '");</script>';
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-8 col-md-6 formWidthAdjust">
                <form id="leaveApplicationApplyForm" action="" method="post" enctype="multipart/form-data" novalidate>

                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>

                    <div class="mt-5">
                        <span class="warning-msg"></span>
                        <span class="warning-msg-date"></span>

                        <div class="form-group mb-2">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label class="form-label" for="leaveType">Leave Type <span class="requiredRed">*</span></label>
                                    <select class="form-select" id="leaveType" name="leaveType" style="background-color: transparent;" required <?php if ($act == '') echo 'disabled' ?>>
                                        <?php
                                        $leaveTypeArr = $currEmpLeaveApplyDays = array();

                                        $querySumOfCurrEmpLeave =
                                            "SELECT leave_type, applicant, SUM(numOfdays) as totalDays
                                        FROM $tblName
                                        WHERE applicant = '$currEmpID' AND leave_transaction_status NOT IN ('declined', 'cancel')
                                        GROUP BY leave_type, applicant;
                                        ";

                                        $queryEmpLeave = "SHOW COLUMNS FROM " . EMPLEAVE;
                                        $resultEmpLeave_1 = mysqli_query($connect, $queryEmpLeave);
                                        $resultEmpLeave_2 = getData('*', 'employeeID="' . $currEmpID . '"', '', EMPLEAVE, $connect);
                                        $resultEmpLeave_3 = mysqli_query($connect, $querySumOfCurrEmpLeave);

                                        if (!$resultEmpLeave_1 || !$resultEmpLeave_2 || !$resultEmpLeave_3) {
                                            echo $errorRedirectLink;
                                        } else {

                                            $columns = $resultEmpLeave_1->fetch_all(MYSQLI_ASSOC);

                                            foreach ($columns as $column) {
                                                if (preg_match('/leaveType_(\d+)/', $column['Field'], $matches)) {
                                                    $extractNumber = $matches[1];
                                                    array_push($leaveTypeArr, $extractNumber);
                                                }
                                            }

                                            if ($resultEmpLeave_3->num_rows > 0) {
                                                while ($row1 = $resultEmpLeave_3->fetch_assoc()) {
                                                    $currEmpLeaveApplyDays["leaveType_" . $row1["leave_type"]] = $row1["totalDays"];
                                                }
                                            }

                                            $rowEmpLeave =  $resultEmpLeave_2->fetch_assoc();

                                            foreach ($currEmpLeaveApplyDays as $key => $value) {
                                                if (isset($rowEmpLeave[$key])) {
                                                    $rowEmpLeave[$key] -= $value;
                                                }
                                            }

                                            $empLeaveJSONArr = json_encode($rowEmpLeave);
                                        }

                                        echo "<option value disabled selected>Select Leave</option>";

                                        $leaveTypeID = implodeWithComma($leaveTypeArr);

                                        $resultLeave = getData('*', 'id IN (' . $leaveTypeID . ')', '', L_TYPE, $connect);

                                        if (!$resultLeave) {
                                            echo $errorRedirectLink;
                                        }

                                        while ($rowLeave = $resultLeave->fetch_assoc()) {
                                            if ($rowLeave['leave_status'] === 'Active') {
                                                $selected = isset($rowLeavePending['leave_type']) && $rowLeave['id'] == $rowLeavePending['leave_type'] ? "selected" : "";
                                                echo "<option value='{$rowLeave['id']}' $selected>{$rowLeave['name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group mb-2">
                                        <label class="form-label" for="fromTime">From Time <span class="requiredRed">*</span></label>
                                        <input class="form-control" type="datetime-local" step="1" name="fromTime" id="fromTime" <?php if ($act == '') echo 'readonly' ?> min='2000-01-01T00:00' max='3000-12-31T23:59' required autocomplete="off" value="<?php echo isset($rowLeavePending['from_time']) ? date('Y-m-d H:i:s', strtotime($rowLeavePending['from_time'])) : ''; ?>">
                                        <span id="fromTimeError" class="error" style="color:#ff0000"></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">

                                    <div class="form-group mb-2">
                                        <label class="form-label" for="toTime">To Time <span class="requiredRed">*</span></label>
                                        <input class="form-control" type="datetime-local" step="1" name="toTime" id="toTime" <?php if ($act == '') echo 'readonly' ?> min='2000-01-01T00:00' max='3000-12-31T23:59' required autocomplete="off" value="<?php echo isset($rowLeavePending['to_time']) ? date('Y-m-d H:i:s', strtotime($rowLeavePending['to_time'])) : ''; ?>">
                                        <span id="toTimeError" class="error" style="color:#ff0000"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-2">
                                        <label class="form-label" for="numOfdays">Number Of Days</label>
                                        <input class="form-control" type="number" step="any" name="numOfdays" id="numOfdays" readonly required autocomplete="off" value="<?php echo (isset($rowLeavePending['numOfdays'])) ? $rowLeavePending['numOfdays'] : '0' ?>">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group mb-2">
                                        <label class="form-label" for="remainingLeave">Remaining Leaves</label>
                                        <input class="form-control" type="number" step="any" name="remainingLeave" id="remainingLeave" readonly required autocomplete="off" value="<?php echo (isset($rowLeavePending['remainingLeave'])) ? $rowLeavePending['remainingLeave'] : '0' ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="remark">Remark</label>
                                <textarea class="form-control" name="remark" id="remark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($rowLeavePending['remark'])) echo $rowLeavePending['remark'] ?></textarea>
                            </div>

                            <div class="form-group mb-2">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="leaveAttachment">Attachment <span class="requiredRed">*</span></label>
                                        <input class="form-control" style="background-color: transparent;" <?php if ($act == '') echo 'disabled' ?> type="file" accept="image/*" name="leaveAttachment" id="leaveAttachment" required autocomplete="off" value="<?php echo (isset($rowLeavePending['attachment']) ? $rowLeavePending['attachment'] : ''); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex justify-content-center mt-3">

                                            <?php
                                            $attachmentPath = isset($rowLeavePending['attachment']) ? $rowLeavePending['attachment'] : '';

                                            if (!empty($attachmentPath)) {
                                                $src = $SITEURL . ATCH . '\pending_leave\\' . $attachmentPath;
                                            } else {
                                                $src = '';
                                            }
                                            ?>

                                            <img id="leaveAttachmenetImg" name="leaveAttachmenetImg" src="<?php echo $src; ?>" class="img-thumbnail">
                                            <?php if (isset($rowLeavePending['attachment'])) { ?>
                                                <input type="hidden" id="leaveAttachmenetImgValue" name="leaveAttachmenetImgValue" value="<?= $rowLeavePending['attachment'] ?>">
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <?php if ($act) { ?>
                                <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="<?= $actionBtnValue ?>">Submit</button>
                            <?php } ?>
                            <button class="btn btn-rounded btn-primary mx-2 mb-2" value="back" name="actionBtn" id="backBtn">Back</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            //Initial Page And Action Value
            var page = "<?= $pageTitle ?>";
            var action = "<?php echo isset($act) ? $act : ''; ?>";

            checkCurrentPage(page, action);
            centerAlignment("formContainer");
            setButtonColor();
            preloader(300, action);

            //Initial Value
            var leaveTypes = removeLeaveTypePrefix(<?php echo $empLeaveJSONArr; ?>);
            var leaveApplyDateArr = <?php echo $leaveApplyDateArrJSON; ?>;

            //Leave Application Form And Leave Assign
            <?php include "./js/leaveTransaction.js" ?>
        </script>
    </div>
</body>

</html>