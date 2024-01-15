<?php
$pageTitle = "Employee Details";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

//Employee details and leave application table
$tblName = EMPPERSONALINFO;
$leavePendingTblName = L_PENDING;

$num = 1;

//Get Pin Access
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Redirect Link
$redirect_page = $SITEURL . '/employeeDetails.php';
$ownRedirectPage = $SITEURL . '/employeeDetailsTable.php';
$errorRedirectLink = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script><script>location.href ='$SITEURL/dashboard.php';</script>";

//Get All Employee Result 
$result = getData('*', '', '', $tblName, $connect);

if (!$result) {
    echo $errorRedirectLink;
}

//Get Leave Application ID and action
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');

if (isset($_COOKIE['leaveAppID']))
    $dataID = $_COOKIE['leaveAppID'];

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';

//Attachment IMG
$leaveAttachmentPath = '.' . ATCH . 'pending_leave/';
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
    echo "<script>setCookie('leaveAppID','', 0);</script>";
    deleteRecord('leave_pending', $dataID, $leavePendingResult['numOfdays'], $connect, $connect, $cdate, $ctime, 'Leave Application');
    $_SESSION['delChk'] = 1;
}

$action = post('actionBtn');

if ($action) {

    $leaveType = postSpaceFilter('leaveType');
    $fromTime = formatTime(postSpaceFilter('fromTime'));
    $toTime = formatTime(postSpaceFilter('toTime'));
    $numOfdays = postSpaceFilter('numOfdays');
    $remainingLeave = postSpaceFilter('remainingLeave');
    $remark = postSpaceFilter('remark');

    if (postSpaceFilter('leaveAttachmenetImgValue')) {
        $leaveAttachment = postSpaceFilter('leaveAttachmenetImgValue');
    }

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

            if ($action == 'addLeave') {

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
                    $query = "INSERT INTO " . $leavePendingTblName . "(leave_type,from_time,to_time,numOfdays,remainingLeave,attachment,remark,create_by,create_date,create_time)VALUES('$leaveType','$fromTime','$toTime','$numOfdays','$remainingLeave','$leaveAttachment','$remark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);
                    move_uploaded_file($leaveAttachment_tmp_name, $leaveAttachmentPath . $leaveAttachment);
                    $dataID = $connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {

                $leaveApplicationAction = 'edit';
                $actLeave = 'E';

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
                        $query = "UPDATE $leavePendingTblName SET leave_type='$leaveType', from_time='$fromTime', to_time='$toTime', numOfdays='$numOfdays', remainingLeave='$remainingLeave', attachment='$leaveAttachment', remark='$remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        move_uploaded_file($leaveAttachment_tmp_name, $leaveAttachmentPath . $leaveAttachment);
                        $returnData = mysqli_query($connect, $query);
                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        $act = "F";
                    }
                } else {
                    $actLeave = 'NC';
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
                    'query_table'  => $leavePendingTblName,
                    'page'         => 'Leave Application',
                    'connect'      => $connect,
                ];

                if ($leaveApplicationAction == 'add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $leavePendingTblName, $leaveApplicationAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($leaveApplicationAction == 'edit') {
                    $log['oldval']  = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $leavePendingTblName, $leaveApplicationAction, (isset($returnData) ? '' : $errorMsg));
                }

                audit_log($log);
            }
            break;
        default:
            break;
    }


    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo "<script>setCookie('leaveAppID','', 0);</script>";
        echo "<script>localStorage.clear();</script>";
        echo '<script>confirmationDialog("","","Leave Application","","","' . $actLeave . '");</script>';
    }
}



//Current Employee ID 
$userResult = getData('name', 'id="' . USER_ID . '"', '', USR_USER, $connect);

if (!$userResult) {
    echo $errorRedirectLink;
}

$userRow = $userResult->fetch_assoc();
$userName = $userRow['name'];

$empResult = getData('id', 'name="' . $userName  . '"',  '', EMPPERSONALINFO, $connect);

if (!$empResult) {
    echo $errorRedirectLink;
}

$empRow = $empResult->fetch_assoc();
$currEmpID = $empRow['id'];


if (isset($_COOKIE['assignType'], $_COOKIE['employeeID'], $_COOKIE['leaveTypeSelect'])) {

    $assignType = $_COOKIE['assignType'];
    $employeeID = $_COOKIE['employeeID'];
    $leaveTypeSelect = $_COOKIE['leaveTypeSelect'];

    //Find All Exist Leave Type In Employee Leave Page
    $existEmpLeave = array();

    $queryEmpLeave = "SHOW COLUMNS FROM " . EMPLEAVE;
    $resultEmpLeave_1 = mysqli_query($connect, $queryEmpLeave);

    $columns = $resultEmpLeave_1->fetch_all(MYSQLI_ASSOC);

    if (!$resultEmpLeave_1) {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
    }

    foreach ($columns as $column) {
        if (preg_match('/leaveType_(\d+)/', $column['Field'], $matches)) {
            $extractNumber = $matches[1];
            array_push($existEmpLeave, $extractNumber);
        }
    }

    $allEmpLeaveTypeColumn = getData('*', '', '', EMPLEAVE, $connect);

    if (!empty($employeeID) && !empty($leaveTypeSelect)) {

        $leaveTypeArr = explode(',', $leaveTypeSelect);
        $empArr = explode(',', $employeeID);

        for ($i = 0; $i < sizeof($empArr); $i++) {

            $datafield = $oldvalarr = $chgvalarr  = array();

            $query = "UPDATE " . EMPLEAVE . " SET ";

            for ($x = 0; $x < sizeof($leaveTypeArr); $x++) {

                if (in_array($leaveTypeArr[$x], $existEmpLeave)) {

                    $empLeaveAssignColumn = "leaveType_" . $leaveTypeArr[$x];

                    $resultLeaveType = getData('num_of_days', 'id ="' . $leaveTypeArr[$x] . '"', '', L_TYPE, $connect);
                    $resultCurrentEmp = getData($empLeaveAssignColumn, 'employeeID ="' . $empArr[$i] . '"', '', EMPLEAVE, $connect);

                    if (!$resultLeaveType || !$resultCurrentEmp) {
                        echo "
                    <script>            
                    setCookie('leaveTypeSelect', '', 0);
                    setCookie('employeeID', '', 0);
                    setCookie('assignType', '', 0);
                    </script>";

                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                    }

                    $rowLeaveType = $resultLeaveType->fetch_assoc();
                    $rowCurrentEmp = $resultCurrentEmp->fetch_assoc();

                    if ($assignType == 'assign') {
                        $assignDay = $rowLeaveType['num_of_days'];
                    } else if ($assignType == 'unassign') {
                        $assignDay = 0;
                    }

                    $query .= "$empLeaveAssignColumn='" . $assignDay . "', ";

                    if ($rowCurrentEmp[$empLeaveAssignColumn] != $assignDay) {
                        array_push($oldvalarr, $rowCurrentEmp[$empLeaveAssignColumn]);
                        array_push($chgvalarr,  $assignDay);
                        array_push($datafield, $empLeaveAssignColumn);
                    }
                }
            }

            $query .= " update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE employeeID = " . $empArr[$i] . "";

            if ($oldvalarr && $chgvalarr) {
                try {
                    $returnData = mysqli_query($connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }

                if (isset($query)) {

                    $log = [
                        'log_act'      => 'edit',
                        'cdate'        => $cdate,
                        'ctime'        => $ctime,
                        'uid'          => USER_ID,
                        'cby'          => USER_ID,
                        'oldval'       => implodeWithComma($oldvalarr),
                        'changes'      => implodeWithComma($chgvalarr),
                        'act_msg'      => actMsgLog($empArr[$i], $datafield, '', $oldvalarr, $chgvalarr, EMPLEAVE, 'edit', (isset($returnData) ? '' : $errorMsg)),
                        'act_msg'      => actMsgLog($empArr[$i], $datafield, '', $oldvalarr, $chgvalarr, EMPLEAVE, 'edit', (isset($returnData) ? '' : $errorMsg)),
                        'query_rec'    => $query,
                        'query_table'  => EMPLEAVE,
                        'page'         => $pageTitle,
                        'connect'      => $connect,
                    ];

                    audit_log($log);
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
</head>

<script>
    //Wait for all css,html,js file fully loader than display a content
    preloader(500);

    $(document).ready(() => {
        createSortingTable('employeeDetailsTable');
        createSortingTable('leaveApplicationTable');
    });
</script>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">
            <div class="col-12 col-md-8">

                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column mb-1">
                    <!--Leave Assign-->
                    <div class="row">
                        <div class="col-12 d-sm-flex flex-wrap">
                            <div class="mb-sm-0 m-2">
                                <button class="btn btn-sm btn-rounded btn-primary" type="button" name="leaveAssignBtn" id="addBtn" style="width:240px">
                                    <i class="mdi mdi-book-edit-outline"></i> Leave Assign
                                </button>

                                <!-- Modal -->

                                <!-- First modal -->
                                <div class="modal" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <?php
                                            if (empty($_COOKIE['employeeID']))
                                                echo '<div class="modal-header alert-danger">';
                                            else
                                                echo '<div class="modal-header bg-info text-white">';

                                            ?>
                                            <h5 class="modal-title" id="staticBackdropLabel">Leave Assign</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <?php if (!empty($_COOKIE['employeeID'])) {
                                                $employeeArr = explode(',', $_COOKIE['employeeID']);

                                                echo "<h4>Employee Selected</h4>";

                                                for ($i = 0; $i < sizeof($employeeArr); $i++) {
                                                    $resultEmp = getData('*', "id=$employeeArr[$i]", '', EMPPERSONALINFO, $connect);

                                                    if ($resultEmp) {
                                                        $empName = $resultEmp->fetch_assoc();
                                                        echo ($i + 1) . ". " . $empName['name'] . "<br>";
                                                    }
                                                }
                                            } else {
                                                echo "<h4 class='text-center'>No Employee Selected</h4>";
                                            }
                                            ?>
                                        </div>

                                        <?php if (!empty($_COOKIE['employeeID'])) { ?>
                                            <div class="modal-footer d-flex justify-content-center">
                                                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-dismiss="modal" value="assign" id="assignLeaveBtn">Assign Leave</button>
                                                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-dismiss="modal" value="unassign" id="unassignLeaveBtn">Unassign Leave</button>
                                            </div>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>

                            <!-- Second modal -->
                            <div class="modal" id="secondModal" data-bs-backdrop=" static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title" id="staticBackdropLabel">Leave Assign</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">

                                            <?php

                                            $autoAsignType = $_COOKIE['assignType'];

                                            if (!empty($autoAsignType)) {
                                                $leaveAvailable = true;
                                                $resultLeave = getData('*', "auto_assign = 'yes' AND leave_status = 'Active'", '', L_TYPE, $connect);

                                                if ($resultLeave->num_rows != 0) {

                                                    echo '<h4 class="text-capitalize">Select a leave to ' . $autoAsignType . '</h4>';

                                                    while ($rowLeave = $resultLeave->fetch_assoc()) {
                                                        echo "<input class='leaveAssignCheck' type='checkbox' value='" . $rowLeave['id'] . "'> " . $rowLeave['name'] . "<br>";
                                                    }
                                                } else {
                                                    echo "<h4 class='text-center'>No Leave Available</h4>";
                                                }
                                            }
                                            ?>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <!-- Add a hidden input field to store the button value -->
                                            <form id="secondModalForm" method="post">
                                                <input type="hidden" name="leaveAssign" id="leaveAssignInput">
                                                <?php
                                                if (isset($leaveAvailable)) {
                                                    echo '<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-dismiss="modal" id="leaveAssignCheckBtn">Submit</button>';
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Third modal -->
                            <div class="modal" id="thirdModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">

                                    <div class="modal-content">
                                        <?php
                                        if (empty($_COOKIE['leaveTypeSelect']) || isset($errorMsg))
                                            echo '<div class="modal-header alert alert-danger">';
                                        else
                                            echo '<div class="modal-header alert alert-success">';

                                        ?>
                                        <h5 class="modal-title">Leave Assign</h5>
                                        <button class="btn-close completeLeaveAssign" type="button" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body text-center">
                                        <?php
                                        if (empty($_COOKIE['leaveTypeSelect']))
                                            echo '<p >No Leave Selected </p>';
                                        else if (isset($errorMsg))
                                            echo '<p class="text-capitalize">An error occurred please try again later</p>';
                                        else
                                            echo '<p class="text-capitalize">Successfully ' . $assignType . ' Leave To Selected Employee</p>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal -->
                    </div>
                    <!--Leave Assign-->


                    <!--Leave Application-->
                    <div class="mb-sm-0 m-2">
                        <a class="btn btn-sm btn-rounded btn-primary" style="width:240px" onclick="changeLeaveApplicationForm('addLeave')" value="addLeave" data-bs-toggle="modal" name="leaveApplicationForm" id="addBtn" href="<?php echo (isset($currEmpID) ? '#leaveApplicationModal' : '#invalidEmpIDModal') ?>" role="button">
                            <i class="mdi mdi-thermometer-plus"></i> Add Leave Application
                        </a>
                    </div>

                    <!--Invalid pop up box when employee id no exist-->
                    <div class="modal fade" id="invalidEmpIDModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="text-end">
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="text-center mt-3">
                                        <h5>Employee ID No Exist,Kindly Try Again Later After Register Account At Employee Table </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Pop up box after successfully submit leave application-->
                    <div class="modal fade" id="successLeaveApplication" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="text-end">
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="text-center mt-3">
                                        <?php
                                        if (isset($$successSubmitLeave)) {
                                            echo '<h5>Successfully Submitted Leave Application</h5>';
                                        } else {
                                            echo '<h5>Unsuccessfully Submitted Leave Application</h5>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Leave Application Table-->
                    <div class="modal fade modal-xl" id="leaveApplicationTableModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="text-end">
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div>
                                        <h4 class="text-center mb-4">Leave Pending Approved</h4>
                                    </div>

                                    <table class="table table-striped" id="leaveApplicationTable">
                                        <thead>
                                            <tr>
                                                <th class="hideColumn" scope="col">ID</th>
                                                <th scope="col">S/N</th>
                                                <th scope="col">Leave Type</th>
                                                <th scope="col">From Day</th>
                                                <th scope="col">To Day</th>
                                                <th scope="col">Day Leave</th>
                                                <th scope="col">Remark</th>
                                                <th scope="col" id="action_col">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $currentEmpLeaveApplicationResult = getData('*', '', '', $leavePendingTblName, $connect);

                                            if (!$currentEmpLeaveApplicationResult) {
                                                echo $errorRedirectLink;
                                            }

                                            $numLeave = 1;

                                            while ($rowCurrentEmpLeaveApplication = $currentEmpLeaveApplicationResult->fetch_assoc()) {

                                                $resultLeaveType = getData('name', "id='" . $rowCurrentEmpLeaveApplication['leave_type'] . "'", '', L_TYPE, $connect);

                                                if (!$resultLeaveType) {
                                                    echo $errorRedirectLink;
                                                }

                                                $rowLeaveType = $resultLeaveType->fetch_assoc();

                                                if (!empty($rowCurrentEmpLeaveApplication['leave_type'])) { ?>
                                                    <tr>
                                                        <th class="hideColumn" scope="col"><?= $rowCurrentEmpLeaveApplication['id']; ?></th>
                                                        <th scope="col"><?= $numLeave++; ?></th>
                                                        <th scope="col"><?= $rowLeaveType['name']; ?></th>
                                                        <th scope="col"><?= $rowCurrentEmpLeaveApplication['from_time']; ?></th>
                                                        <th scope="col"><?= $rowCurrentEmpLeaveApplication['to_time']; ?></th>
                                                        <th scope="col"><?= $rowCurrentEmpLeaveApplication['numOfdays']; ?></th>
                                                        <th scope="col"><?= $rowCurrentEmpLeaveApplication['remark']; ?></th>
                                                        <td scope="row">
                                                            <div class="dropdown" style="text-align:center">
                                                                <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                                                </a>
                                                                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                                                    <li>
                                                                        <a class="dropdown-item" onclick="changeLeaveApplicationForm('editLeave');postLeaveID('<?= $rowCurrentEmpLeaveApplication['id']; ?>');" value="editLeave" data-bs-toggle="modal" name="leaveApplicationForm" href="<?php echo (isset($currEmpID) ? '#leaveApplicationModal' : '#invalidEmpIDModal') ?>" role="button">
                                                                            Edit
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item" onclick="leave_application_dlt_btn();confirmationDialog('<?= $rowCurrentEmpLeaveApplication['id'] ?>',['<?= $rowLeaveType['name'] ?>','<?= $rowCurrentEmpLeaveApplication['from_time'] . ' to ' .  $rowCurrentEmpLeaveApplication['to_time'] ?>'],'Leave Application','<?= $SITEURL ?>/leaveApplicationDelete.php','','D')">
                                                                            Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            }
                                            ?>

                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th class="hideColumn" scope="col">ID</th>
                                                <th scope="col">S/N</th>
                                                <th scope="col">Leave Type</th>
                                                <th scope="col">From Day</th>
                                                <th scope="col">To Day</th>
                                                <th scope="col">Num Day </th>
                                                <th scope="col">Remark</th>
                                                <th scope="col" id="action_col">Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Leave Application Form-->
                    <div class="modal fade" id="leaveApplicationModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">

                                    <div class="text-end">
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div>
                                        <h4 class="text-center" id="leaveApplicationFormTitle">Add Leave</h4>
                                    </div>

                                    <form id="leaveApplicationApplyForm" action="" method="post" enctype="multipart/form-data" novalidate>
                                        <div class="mt-5">
                                            <div class="form-group  mb-2">
                                                <label class="form-label" for="leaveType">Leave Type <span class="requiredRed">*</span></label>
                                                <select class="form-select" id="leaveType" name="leaveType" required>
                                                    <?php
                                                    $leaveTypeArr = array();

                                                    $queryEmpLeave = "SHOW COLUMNS FROM " . EMPLEAVE;
                                                    $resultEmpLeave_1 = mysqli_query($connect, $queryEmpLeave);
                                                    $resultEmpLeave_2 = getData('*', 'employeeID="' . $currEmpID . '"', '', EMPLEAVE, $connect);

                                                    if (!$resultEmpLeave_1 || !$resultEmpLeave_2) {
                                                        echo $errorRedirectLink;
                                                    } else {

                                                        $columns = $resultEmpLeave_1->fetch_all(MYSQLI_ASSOC);

                                                        foreach ($columns as $column) {
                                                            if (preg_match('/leaveType_(\d+)/', $column['Field'], $matches)) {
                                                                $extractNumber = $matches[1];
                                                                array_push($leaveTypeArr, $extractNumber);
                                                            }
                                                        }

                                                        $rowEmpLeave =  $resultEmpLeave_2->fetch_assoc();
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

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="fromTime">From <span class="requiredRed">*</span></label>
                                                <input class="form-control" type="datetime-local" name="fromTime" id="fromTime" min='2000-01-01T00:00' max='3000-12-31T23:59' required autocomplete="off" value="<?php echo isset($rowLeavePending['from_time']) ? date('Y-m-d H:i:s', strtotime($rowLeavePending['from_time'])) : ''; ?>">
                                                <span id="fromTimeError" class="error" style="color:#ff0000"></span>
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="toTime">To <span class="requiredRed">*</span></label>
                                                <input class="form-control" type="datetime-local" name="toTime" id="toTime" min='2000-01-01T00:00' max='3000-12-31T23:59' required autocomplete="off" value="<?php echo isset($rowLeavePending['to_time']) ? date('Y-m-d H:i:s', strtotime($rowLeavePending['to_time'])) : ''; ?>">
                                                <span id="toTimeError" class="error" style="color:#ff0000"></span>
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="numOfdays">Number Of Days</label>
                                                <input class="form-control" type="number" step="any" name="numOfdays" id="numOfdays" readonly required autocomplete="off" value="<?php echo (isset($rowLeavePending['numOfdays'])) ? $rowLeavePending['numOfdays'] : '0' ?>">
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="remainingLeave">Remaining Leaves</label>
                                                <input class="form-control" type="number" step="any" name="remainingLeave" id="remainingLeave" readonly required autocomplete="off" value="<?php echo (isset($rowLeavePending['remainingLeave'])) ? $rowLeavePending['remainingLeave'] : '0' ?>">
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="form-label" for="leaveAttachment">Attachment <span class="requiredRed">*</span></label>
                                                <input class="form-control" type="file" accept="image/*" name="leaveAttachment" id="leaveAttachment" required autocomplete="off" value="<?php echo (isset($rowLeavePending['attachment']) ? $rowLeavePending['attachment'] : ''); ?>">

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

                                            <div class="form-group mb-3">
                                                <label class="form-label" for="remark">Remark</label>
                                                <textarea class="form-control" name="remark" id="remark" rows="3"><?php if (isset($rowLeavePending['remark'])) echo $rowLeavePending['remark'] ?></textarea>
                                            </div>
                                        </div>

                                        <div class="text-center mt-5">
                                            <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- Modal -->
                            </div>
                        </div>
                    </div>
                    <!--Leave Application-->


                    <!--Add New Employee-->
                    <div class="mb-sm-0 m-2">
                        <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                            <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>" style="width:240px">
                                <i class="mdi mdi-account-plus-outline"></i> Add <?php echo $pageTitle ?>
                            </a>&nbsp;
                        <?php endif; ?>

                    </div>
                    <!--Add New Employee-->
                </div>

            </div>
        </div>

        <table class="table table-striped" id="employeeDetailsTable">
            <thead>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th class="text-center">
                        <input type="checkbox" class="leaveAssignAll">
                    </th>
                    <th scope="col" width="60px">S/N</th>
                    <th scope="col">Leave application</th>
                    <th scope="col">Name</th>
                    <th scope="col">Identity Type</th>
                    <th scope="col">Identity Number</th>
                    <th scope="col">Email</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Birthday</th>
                    <th scope="col">Race</th>
                    <th scope="col">Residence </th>
                    <th scope="col">Nationality </th>
                    <th scope="col">Phone Number</th>
                    <th scope="col">Alternate Phone Number</th>
                    <th scope="col">Address Line 1</th>
                    <th scope="col">Address Line 2</th>
                    <th scope="col">City</th>
                    <th scope="col">State</th>
                    <th scope="col">Postcode</th>
                    <th scope="col">Marital status</th>
                    <th scope="col">Number of kids</th>
                    <th scope="col" id="action_col" width="100px">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                        <th class="text-center">
                            <input type="checkbox" class="leaveAssign" value="<?= $row['id'] ?>">
                        </th>
                        <th scope=" row"><?= $num++ ?></th>

                        <td scope="row" class="text-center">
                            <a class="text-reset me-3 " href="#" id="leaveStatusMenu" role="button" aria-expanded="false">
                                <button class="action_menu_btn" style="border:none;background:none;font-size:25px" value="editLeave" name="leaveApplicationForm" data-bs-toggle="modal" href="#leaveApplicationTableModal">
                                    <i class="mdi mdi-format-list-bulleted"></i>
                                </button>
                            </a>
                        </td>

                        <td scope="row"><?= $row['name'] ?></td>
                        <td scope='row'>
                            <?php
                            $resultIDType = getData('*', 'id = ' . $row['id_type'], '', ID_TYPE, $connect);

                            while ($rowIDType = $resultIDType->fetch_assoc()) {
                                echo $rowIDType['name'];
                            }
                            ?>
                        </td>
                        <td scope="row"><?= $row['id_number'] ?></td>
                        <td scope="row"><?= $row['email'] ?></td>
                        <td scope="row"><?= $row['gender'] ?></td>
                        <td scope="row"><?= $row['date_of_birth'] ?></td>
                        <td scope='row'>
                            <?php
                            $resultRace = getData('*', 'id = ' . $row['race_id'], '', RACE, $connect);

                            while ($rowRace = $resultRace->fetch_assoc()) {
                                echo $rowRace['name'];
                            }
                            ?>
                        </td>

                        <td scope="row"><?= $row['residence_status'] ?></td>
                        <td scope='row'>
                            <?php
                            $resultNationality = getData('*', 'id = ' . $row['nationality'], '', 'countries', $connect);

                            while ($rowNationality = $resultNationality->fetch_assoc()) {
                                echo $rowNationality['name'];
                            }
                            ?>
                        </td>
                        <td scope="row"><?= $row['phone_number'] ?></td>
                        <td scope="row"><?= $row['phone_number'] ?></td>
                        <td scope="row"><?= $row['address_line_1'] ?></td>
                        <td scope="row"><?= $row['address_line_2'] ?></td>
                        <td scope="row"><?= $row['city'] ?></td>
                        <td scope="row"><?= $row['state'] ?></td>
                        <td scope="row"><?= $row['postcode'] ?></td>
                        <td scope='row'>
                            <?php

                            $resultMrtSts = getData('*', 'id = ' . $row['marital_status'], '', MRTL_STATUS, $connect);

                            while ($rowMrtSts = $resultMrtSts->fetch_assoc()) {
                                echo $rowMrtSts['name'];
                            }
                            ?>
                        </td>
                        <td scope="row"><?= $row['no_of_children'] ?></td>
                        <td scope="row">
                            <div class="dropdown" style="text-align:center">
                                <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                    <li>
                                        <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                            <a class="dropdown-item" href="<?php echo $redirect_page ?>?id=<?php echo $row['id'] ?>">View</a>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                            <a class="dropdown-item" href="<?php echo $redirect_page ?>?id=<?php echo $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                            <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>','<?= $row['id_number'] ?>','<?= $row['email'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/employeeDetailsTable.php','D')">Delete</a>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th class="text-center">
                        <input type="checkbox" class="leaveAssignAll">
                    </th>
                    <th scope=" col">S/N</th>
                    <th scope="col">Leave application</th>
                    <th scope="col">Name</th>
                    <th scope="col">Identity Type</th>
                    <th scope="col">Identity Number</th>
                    <th scope="col">Email</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Birthday</th>
                    <th scope="col">Race</th>
                    <th scope="col">Residence </th>
                    <th scope="col">Nationality </th>
                    <th scope="col">Phone Number</th>
                    <th scope="col">Alternate Phone Number</th>
                    <th scope="col">Address Line 1</th>
                    <th scope="col">Address Line 2</th>
                    <th scope="col">City</th>
                    <th scope="col">State</th>
                    <th scope="col">Postcode</th>
                    <th scope="col">Marital status</th>
                    <th scope="col">Number of kids</th>
                    <th scope="col" id="action_col">Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
    </div>
</body>
<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
    setButtonColor();
    dropdownMenuDispFix();
    datatableAlignment('employeeDetailsTable');
    datatableAlignment('leaveApplicationTable');

    //Leave Application Form
    <?php include "./js/employeeDetailsTable.js" ?>

    $('#fromTime, #toTime').on('change', calculateNumberOfDays);

    var leaveTypes = removeLeaveTypePrefix(<?php echo $empLeaveJSONArr; ?>);
    var submitBtn = document.getElementById('actionBtn');

    if (!sessionStorage.getItem("leaveAppEdit"))
        submitBtn.disabled = true;

    //Assign a remaining leaves
    document.getElementById("leaveType").addEventListener("change", updateRemainingLeaves);

    function updateInitialLeave() {
        var selectedLeaveId = document.getElementById("leaveType").value;
        var remainingLeave;

        if (leaveTypes.hasOwnProperty(selectedLeaveId)) {
            remainingLeave = leaveTypes[selectedLeaveId];
        } else {
            remainingLeave = 0;
        }

        return remainingLeave;
    }

    function updateRemainingLeaves() {
        var remainingLeaveValue = updateInitialLeave();

        if (remainingLeaveValue >= 0) {
            document.getElementById("remainingLeave").value = remainingLeaveValue;
        } else {
            document.getElementById("remainingLeave").value = 0;
        }
    }

    // Validate Time Input: Check if the input is a valid future date and time
    function validateDateTime(inputId, errorId) {
        var input = document.getElementById(inputId);
        var error = document.getElementById(errorId);

        var inputTime = new Date(input.value);
        var currentTime = new Date();

        // Check if input is a valid date
        if (isNaN(inputTime)) {
            return false;
        }
        // Check if input date is in the future
        if (inputTime < currentTime) {
            error.textContent = "Date must be in the future";
            return false;
        }
        // Check for incomplete day input
        var dayIncomplete = input.value.split("T")[0].split("-").some(part => part.length < 2);

        if (dayIncomplete) {
            error.textContent = "Incomplete day";
            return false;
        }

        error.textContent = "";
        return true;
    }

    function calculateNumberOfDays() {
        var fromTime = $('#fromTime').val();
        var toTime = $('#toTime').val();
        var submitBtn = document.getElementById('actionBtn');

        var currentTime = new Date();
        var fromTimeDate = new Date(fromTime);
        var toTimeDate = new Date(toTime);

        if (fromTime && toTime && fromTimeDate > currentTime && toTimeDate > currentTime) {
            var fromDate = new Date(fromTime);
            var toDate = new Date(toTime);

            var durationInMilliseconds = toDate - fromDate;

            var durationInDays = Math.round((durationInMilliseconds / (1000 * 60 * 60 * 24)) * 2) / 2;

            var remainingLeave = parseInt(updateInitialLeave());

            if (remainingLeave > 0 && (remainingLeave - durationInDays) <= remainingLeave) {
                $('#numOfdays').val(durationInDays);
                $('#remainingLeave').val(remainingLeave - durationInDays);
                return true;
            }
        }

        return false;
    }

    function checkFormInputEmptyValue() {

        var requiredFields = document.getElementById('leaveApplicationApplyForm').querySelectorAll('[required]');

        var allFieldsFilled = Array.from(requiredFields).every(function(field) {
            return field.value.trim() !== '';
        });

        return allFieldsFilled;
    }

    var leaveApplicationForm = document.getElementById('leaveApplicationApplyForm');

    leaveApplicationForm.addEventListener("change", function() {

        var submitBtnBooleanArr = [];
        var submitBtn = document.getElementById('actionBtn');
        var leaveType = document.getElementById('leaveType');

        leaveType.addEventListener('change', calculateNumberOfDays);

        submitBtnBooleanArr.push(checkFormInputEmptyValue());
        submitBtnBooleanArr.push(validateDateTime("toTime", "toTimeError"));
        submitBtnBooleanArr.push(validateDateTime("fromTime", "fromTimeError"));
        submitBtnBooleanArr.push(calculateNumberOfDays());

        var submitButtonDisabled = submitBtnBooleanArr.every(function(condition) {
            return condition === true;
        });

        submitBtn.disabled = !submitButtonDisabled;

        console.log("HELLO HELLO");
        console.log(submitBtnBooleanArr);
    });
</script>


</html>