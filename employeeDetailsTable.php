<?php
$pageTitle = "Employee Details";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/employeeDetails.php';
$result = getData('*', '', '', EMPPERSONALINFO, $connect);

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

            $oldvalarr = $chgvalarr = array();

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
                    }
                }
            }

            $query .= " update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE employeeID = " . $empArr[$i] . "";

            if ($oldvalarr && $chgvalarr) {
                try {
                    $returnData = mysqli_query($connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }

                if (isset($errorMsg)) {
                    $errorMsg = str_replace('\'', '', $errorMsg);
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
                        'act_msg'      => actMsgLog($oldvalarr, $chgvalarr, EMPLEAVE, (isset($returnData) ? '' : $errorMsg)),
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
    $(document).ready(() => {
        /**
         oufei 20231014
         common.fun.js
         function(id)
         create DataTable (sortable table)
        */
        createSortingTable('employeeDetailsTable');
    });
</script>

<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">
        <div class="col-12 col-md-8">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2><?php echo $pageTitle ?></h2>
                        <div class="mt-auto mb-auto">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap mt-2">
                            <div class="d-flex justify-content-center align-items-center mb-1">

                                <button class="btn btn-sm btn-rounded btn-primary" type="button" name="leaveAssignBtn" id="addBtn">
                                    <i class="mdi mdi-clipboard-arrow-left"></i> Leave Assign
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
                            <div class="modal" id="thirdModal" data-bs-backdrop=" static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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

                    <div class="text-center">
                        <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                            <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add <?php echo $pageTitle ?> </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>


        <table class="table table-striped" id="employeeDetailsTable">
            <thead>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th class="text-center">
                        <input type="checkbox" class="leaveAssignAll"">
                        </th>
                        <th scope=" col">S/N
                    </th>
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
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                        <th class="text-center">
                            <input type="checkbox" class="leaveAssign" value="<?= $row['id'] ?>">
                        </th>
                        <th scope=" row"><?= $num;
                                            $num++ ?>
                        </th>
                        <td scope="row"><?= $row['name'] ?></td>

                        <?php
                        $resultIDType = getData('*', 'id = ' . $row['id_type'], '', ID_TYPE, $connect);

                        while ($rowIDType = $resultIDType->fetch_assoc()) {
                            echo "<td scope='row'>" . $rowIDType['name'] . "</td>";
                        }
                        ?>

                        <td scope="row"><?= $row['id_number'] ?></td>
                        <td scope="row"><?= $row['email'] ?></td>
                        <td scope="row"><?= $row['gender'] ?></td>
                        <td scope="row"><?= $row['date_of_birth'] ?></td>

                        <?php
                        $resultRace = getData('*', 'id = ' . $row['race_id'], '', RACE, $connect);

                        while ($rowRace = $resultRace->fetch_assoc()) {
                            echo "<td scope='row'>" . $rowRace['name'] . "</td>";
                        }
                        ?>

                        <td scope="row"><?= $row['residence_status'] ?></td>

                        <?php
                        $resultNationality = getData('*', 'id = ' . $row['nationality'], '', 'countries', $connect);

                        while ($rowNationality = $resultNationality->fetch_assoc()) {
                            echo "<td scope='row'>" . $rowNationality['name'] . "</td>";
                        }
                        ?>

                        <td scope="row"><?= $row['phone_number'] ?></td>
                        <td scope="row"><?= $row['phone_number'] ?></td>
                        <td scope="row"><?= $row['address_line_1'] ?></td>
                        <td scope="row"><?= $row['address_line_2'] ?></td>
                        <td scope="row"><?= $row['city'] ?></td>
                        <td scope="row"><?= $row['state'] ?></td>
                        <td scope="row"><?= $row['postcode'] ?></td>

                        <?php

                        $resultMrtSts = getData('*', 'id = ' . $row['marital_status'], '', MRTL_STATUS, $connect);

                        while ($rowMrtSts = $resultMrtSts->fetch_assoc()) {
                            echo "<td scope='row'>" . $rowMrtSts['name'] . "</td>";
                        }
                        ?>

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
                        <input type="checkbox" class="leaveAssignAll"">
                        </th>
                    <th scope=" col">S/N
                    </th>
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
    dropdownMenuDispFix();

    datatableAlignment('employeeDetailsTable');

    $(document).ready(function($) {
        $(document).on('change', '.leaveAssignAll', function(event) {
            event.preventDefault();

            var isChecked = $(this).prop('checked');
            $('.leaveAssign').prop('checked', isChecked);
            $('.leaveAssignAll').prop('checked', isChecked);
        });
    });

    $(document).ready(function() {

        $('button[name="leaveAssignBtn"]').on('click', function() {
            var checkboxValues = [];

            $('.leaveAssign:checked').each(function() {
                checkboxValues.push($(this).val());
            });

            setCookie("employeeID", checkboxValues, 1);
            sessionStorage.setItem("leaveAssignClick", "true");
            location.reload(true);
        });

        $('#assignLeaveBtn, #unassignLeaveBtn').on('click', function() {
            var assignTypeValue = $(this).val();
            setCookie("assignType", assignTypeValue, 1);
            sessionStorage.setItem("leaveAssignType", "true");
            location.reload(true);
        });


        $('#leaveAssignCheckBtn').on('click', function() {
            var checkboxValues = [];

            $('.leaveAssignCheck:checked').each(function() {
                checkboxValues.push($(this).val());
            });

            setCookie("leaveTypeSelect", checkboxValues, 1);
            sessionStorage.setItem("leaveAssignSelect", "true");
            location.reload(true);
        });

        $('.completeLeaveAssign').on('click', function() {
            setCookie("leaveTypeSelect", '', 0);
            setCookie("employeeID", '', 0);
            setCookie("assignType", '', 0);
        });

        setTimeout(function() {
            if (sessionStorage.getItem("leaveAssignClick")) {
                $('#myModal').modal('show');
                sessionStorage.removeItem("leaveAssignClick");
            } else if (sessionStorage.getItem("leaveAssignType")) {
                $('#secondModal').modal('show');
                sessionStorage.removeItem("leaveAssignType");
            } else if (sessionStorage.getItem("leaveAssignSelect")) {
                $('#thirdModal').modal('show');
                sessionStorage.removeItem("leaveAssignSelect");
            }
        }, 50);


    });
</script>


</html>