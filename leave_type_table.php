<?php
$pageTitle = "Leave Type";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblname = L_TYPE;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/leave_type.php';
$deleteRedirectPage = $SITEURL . '/leave_type_table.php';
$result = getData('*', '', '', $tblname, $connect);

if (post('l_status_option')) {
    $leave_type_id = post('l_type_id');
    $leave_type_status = post('l_status_option');

    echo "<script>console.log('TEST1')</script>";

    $datafield = $oldvalarr = $chgvalarr = array();

    $rst = getData('*', "id = '$leave_type_id'", '', $tblname, $connect);

    echo "<script>console.log('TEST2')</script>";

    if (!$rst) {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</>";
        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
    }

    $rowLeaveType = $rst->fetch_assoc();

    echo "<script>console.log('TEST3')</script>";

    if ($rowLeaveType['leave_status'] !== $leave_type_status) {
        array_push($oldvalarr, $rowLeaveType['leave_status']);
        array_push($chgvalarr, $leave_type_status);
        array_push($datafield, 'leave_status');
    }

    echo "<script>console.log('TEST4')</script>";

    if ($oldvalarr && $chgvalarr) {
        try {
            $query = "UPDATE " . $tblname . " SET leave_status = '$leave_type_status' WHERE id = '$leave_type_id'";
            mysqli_query($connect, $query);
            generateDBData($tblname, $connect);
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }

        // audit log
        $log = [
            'log_act'      => 'edit',
            'cdate'        => $cdate,
            'ctime'        => $ctime,
            'uid'          => USER_ID,
            'cby'          => USER_ID,
            'query_rec'    => $query,
            'query_table'  => $tblName,
            'page'         => $pageTitle,
            'connect'      => $connect,
            'oldval'       => implodeWithComma($oldvalarr),
            'changes'      => implodeWithComma($chgvalarr),
            'act_msg'      => actMsgLog($leave_type_id, $datafield, '', $oldvalarr, $chgvalarr, $tblName, 'edit', (isset($returnData) ? '' : $errorMsg))
        ];
        echo "<script>console.log('TEST5')</script>";

        audit_log($log);
    } else {
        echo "<script>console.log('TEST6')</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('table');
    });
</script>

<style>
    .btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        margin: 3px;
    }
    .btn-container {
        white-space: nowrap;
    }
</style>

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
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Name</th>
                            <th scope="col">Number Of Days</th>
                            <th scope="col">Leave Status</th>
                            <th scope="col">Auto Assign</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            if (isset($row['name'], $row['id']) && !empty($row['name'])) { ?>
                                <tr>
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++ ?></th>
                                    <td scope="row" class="btn-container">
                                        <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                        <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['id'] ?>"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                        <a class="btn btn-warning me-1" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                        <a class="btn btn-danger" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                        </td>
                                    <td scope="row"><?= $row['name'] ?></td>
                                    <td scope="row"><?php if (isset($row['num_of_days'])) echo $row['num_of_days'] . ' Days' ?></td>
                                    <td scope="row">
                                        <?php
                                        if (isset($row['leave_status'])) {
                                            $leave_status = $row['leave_status'];
                                        ?>
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
                                        <?php } ?>
                                    </td>
                                    <td scope="row" style="text-transform: uppercase;"><?php if (isset($row['auto_assign'])) echo $row['auto_assign'] ?></td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Name</th>
                            <th scope="col">Number Of Days</th>
                            <th scope="col">Leave Status</th>
                            <th scope="col">Auto Assign</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>

<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
    dropdownMenuDispFix();
    setButtonColor();
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
            success: function(data) {
                console.log('TEST7');
                window.location.href = 'leave_type_table.php';
            },
        })
    }
</script>

</html>