<?php
$pageTitle = "Leave Type";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/leave_type.php';
$result = getData('*', '', L_TYPE, $connect);

// change status
/* $l_status = post('l_status_option'); */

if (post('l_status_option')) {
    $leave_type_id = post('l_type_id');
    $leave_type_status = post('l_status_option');

    $rst = getData('*', "id = '$leave_type_id'", L_TYPE, $connect);

    if ($rst != false) {
        $dataExisted = 1;
        $rowLeaveType = $rst->fetch_assoc();
    }

    $oldvalarr = $chgvalarr = array();

    echo $$rowLeaveType['leave_status'] ." ". $leave_type_status;

    if ($rowLeaveType['leave_status'] !== $leave_type_status) {
        array_push($oldvalarr, $rowLeaveType['leave_status']);
        array_push($chgvalarr, $leave_type_status);
    }

    $oldval = implode(",", $oldvalarr);
    $chgval = implode(",", $chgvalarr);

    if ($oldval && $chgval) {
        $query = "UPDATE " . L_TYPE . " SET leave_status = '$leave_type_status' WHERE id = '$leave_type_id'";
        mysqli_query($connect, $query);
        generateDBData(L_TYPE, $connect);

        // audit log
        $log = array();
        $log['log_act'] = 'edit';
        $log['cdate'] = $cdate;
        $log['ctime'] = $ctime;
        $log['uid'] = $log['cby'] = USER_ID;

        $log['act_msg'] = USER_NAME . " edited the data";
        for ($i = 0; $i < sizeof($oldvalarr); $i++) {
            if ($i == 0)
                $log['act_msg'] .= " from <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
            else
                $log['act_msg'] .= ", <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
        }
        $log['act_msg'] .= " from <b><i>$pageTitle Table</i></b>.";

        $log['query_rec'] = $query;
        $log['query_table'] = $tblname;
        $log['page'] = $pageTitle;
        $log['oldval'] = $oldval;
        $log['changes'] = $chgval;
        $log['connect'] = $connect;
        audit_log($log);
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
        createSortingTable('leave_type_table');
    });
</script>

<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2> <?php echo $pageTitle ?></h2>
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add <?php echo $pageTitle ?> </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="leave_type_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Number Of Days</th>
                        <th scope="col">Leave Status</th>
                        <th scope="col">Auto Assign</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) {  ?>
                        <?php $leave_status = $row['leave_status']; ?>
                        <tr>
                            <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                            <th scope="row"><?= $num;
                                            $num++ ?></th>
                            <td scope="row"><?= $row['name'] ?></td>
                            <td scope="row"><?= $row['num_of_days'] . ' Days' ?></td>
                            <td scope="row">
                                <div class="dropdown">
                                    <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="leaveStatusMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <button class="roundedSelectionBtn">
                                            <span class="mdi mdi-record-circle-outline" style="<?php echo ($leave_status == 'Active') ? 'color:#008000;' : 'color:#ff0000;'; ?>"></span>
                                            <?php
                                            switch ($leave_status) {
                                                case 'Active':
                                                    echo 'Active';
                                                    break;
                                                case 'Inactive':
                                                    echo 'Inactive';
                                                    break;
                                                default:
                                                    echo 'Error';
                                            }
                                            ?>
                                        </button>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="leaveStatusMenu">
                                        <li>
                                            <a class="dropdown-item" id="activeOption" href="" onclick="updateLeaveStatus(<?= $row['id'] ?>,'Active')"><span class="mdi mdi-record-circle-outline" style="color:#008000"></span> Active</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" id="inactiveOption" href="" onclick="updateLeaveStatus(<?= $row['id'] ?>,'Inactive')"><span class="mdi mdi-record-circle-outline" style="color:#ff0000"></span> Inactive</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td scope="row" style="text-transform: uppercase;"><?= $row['auto_assign'] ?></td>
                            <td scope="row">
                                <div class="dropdown" style="text-align:center">
                                    <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                        <li>
                                            <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                                <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] ?>">View</a>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                                <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                                <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/leave_type_table.php','D')">Delete</a>
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
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Number Of Days</th>
                        <th scope="col">Leave Status</th>
                        <th scope="col">Auto Assign</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</body>
<script>
    /*
  oufei 20231014
  common.fun.js
  function(title, subtitle, page name, ajax url path, redirect path, action)
  to show action dialog after finish certain action (eg. edit)
*/
    dropdownMenuDispFix();

    /**
      oufei 20231014
      common.fun.js
      function(id)
      to resize form with "centered" class
    */
    datatableAlignment('leave_type_table');

    var activeElem = $('#activeOption');
    var inactiveElem = $('#inactiveOption');

    function updateLeaveStatus(id, status) {
        $.ajax({
            url: 'leave_type_table.php',
            type: 'post',
            data: {
                l_type_id: id,
                l_status_option: status,
            },
            success: (result) => {
                location.reload();
            }
        })
    }
</script>

</html>