<?php
$pageTitle = "All Leave Transaction";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = L_PENDING;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/leaveTransaction.php';

//Current Employee ID 
$userResult = getData('name', 'id="' . USER_ID . '"', '', USR_USER, $connect);

if (!$userResult) {
    echo $errorRedirectLink;
}

$userRow = $userResult->fetch_assoc();
if (isset($userRow['name']))
    $userName = $userRow['name'];

$empResult = getData('*', 'name="' . $userName  . '"',  '', EMPPERSONALINFO, $connect);

if (!$empResult) {
    echo $errorRedirectLink;
}

$empRow = $empResult->fetch_assoc();
if (isset($empRow['id']))
    $currEmpID = $empRow['id'];

$result = getData('*', '', '', $tblName, $connect);

//All Leave Type
$allLeaveTypeResult = getData('id,name', 'leave_status="Active" AND auto_assign = "yes"',  '', L_TYPE, $connect);

if (!$allLeaveTypeResult) {
    echo $errorRedirectLink;
}

$allLeaveTypeRow = $arr = array();
while ($allLeaveTypeRow = $allLeaveTypeResult->fetch_assoc()) {
    array_push($arr, $allLeaveTypeRow);
}

foreach ($arr as $item) {
    $id = $item['id'];
    $name = $item['name'];

    $allLeaveTypeArr[$id] = $name;
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
        createSortingLeaveTransactionTable('table');
        createSortingTable('empTable');
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

                <div class="d-flex overflow-auto flex-column mb-3">
                    <div class="col-12 d-flex flex-nowrap">
                        <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                            <div class="mt-auto mb-2 me-2">
                                <a class="btn btn-sm btn-rounded btn-primary text-nowrap" name="addBtn" id="addBtn" data-bs-toggle="modal" href="#exampleModalToggle" role="button"><i class="fa-solid fa-plus"></i> Add Leave Transaction </a>
                            </div>
                        <?php endif; ?>

                        <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalToggleLabel">Choose A Employee To Add Leave Transaction</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-striped" id="empTable">
                                            <thead>
                                                <tr>
                                                    <th class="hideColumn" scope="col">ID</th>
                                                    <th scope="col" width="60px">S/N</th>
                                                    <th scope="col" class='text-nowrap'>Name</th>
                                                    <th scope="col" id="action_col" style="width: 100px;">Action</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $empNum = 1;
                                            $empResult = getData('*', '', '', EMPPERSONALINFO, $connect);
                                            while ($empRow = $empResult->fetch_assoc()) { ?>
                                                <tbody>
                                                    <tr>
                                                        <th class="hideColumn" scope="row"><?= $empRow['id'] ?></th>
                                                        <td scope="row"><?= $empNum++ ?></td>
                                                        <td scope="row">
                                                            <?php if (isset($empRow['name'])) echo $empRow['name'] ?>
                                                        </td>
                                                        <td scope="row">
                                                            <?php if (isset($empRow['id'])) { ?>
                                                                <div class="text-center">
                                                                    <button class="roundedSelectionBtn text-capitalize text-start px-3" style="font-size: 14px;">
                                                                        <a class="" href="<?= $redirect_page . "?act=" . $act_1 . "&empID=" . $empRow['id'] ?>&page=allleave">Select</span>
                                                                    </button>
                                                                </div>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            <?php } ?>

                                            <tfoot>
                                                <tr>
                                                    <th class="hideColumn" scope="col">ID</th>
                                                    <th scope="col" width="60px">S/N</th>
                                                    <th scope="col" class='text-nowrap'>Name</th>
                                                    <th scope="col" id="action_col" style="width: 100px;">Action</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto mb-2 me-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-rounded btn-primary text-nowrap dropdown-toggle" type="button" id="addBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-filter-outline"></i> Filter By
                                </button>
                                <ul class="dropdown-menu">

                                    <div class="my-3">
                                        <li>
                                            <p class="dropdown-item" style="margin-bottom: 0;font-size:11px">Status</p>
                                        </li>
                                        <li>
                                            <div class="dropdown-item d-flex align-items-center py-1">
                                                <input type="radio" id="pending" value="Status:pending" name="filterLeave" />
                                                <label for="pending" class="mb-0 ms-2">Pending</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item d-flex align-items-center py-1">
                                                <input type="radio" id="approval" value="Status:approval" name="filterLeave" />
                                                <label for="approval" class="mb-0 ms-2">Approval</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item d-flex align-items-center py-1">
                                                <input type="radio" id="declined" value="Status:declined" name="filterLeave" />
                                                <label for="declined" class="mb-0 ms-2">Declined</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item d-flex align-items-center py-1">
                                                <input type="radio" id="cancel" value="Status:cancel" name="filterLeave" />
                                                <label for="cancel" class="mb-0 ms-2">Cancel</label>
                                            </div>
                                        </li>
                                    </div>

                                    <div class="my-3 border-top">
                                        <li>
                                            <p class="dropdown-item" style="margin-bottom: 0;font-size:11px;">Leave Type</p>
                                        </li>
                                        <?php
                                        foreach ($allLeaveTypeArr as $value)
                                            echo '
                                            <li>
                                                <div class="dropdown-item d-flex align-items-center py-1">
                                                    <input type="radio" id="' . $value . '" value="Leave Type:' . $value . '" name="filterLeave" />
                                                    <label for="' . $value . '" class="mb-0 ms-2">' . $value . '</label>
                                                </div>
                                            </li>
                                                 ';
                                        ?>
                                    </div>

                                    <div class="my-3 border-top">
                                        <li>
                                            <p class="dropdown-item" style="margin-bottom: 0;font-size:11px;">Employee Name</p>
                                        </li>
                                        <?php
                                        $empNum = 1;
                                        $empResult = getData('*', '', '', EMPPERSONALINFO, $connect);
                                        while ($empRow = $empResult->fetch_assoc()) {
                                            if (isset($empRow['name']) && $empRow['name']) {
                                        ?>
                                                <li>
                                                    <div class="dropdown-item d-flex align-items-center py-1 ">
                                                        <input type="radio" id="<?= $empRow['name'] ?>" value="Applicant:<?= $empRow['name'] ?>" name="filterLeave" />
                                                        <label for="<?= $empRow['name'] ?>" class="mb-0 ms-2"><?= $empRow['name'] ?></label>
                                                    </div>
                                                </li>
                                        <?php
                                            }
                                        } ?>
                                    </div>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-auto mb-2 me-2">
                            <button class="btn btn-sm btn-rounded btn-primary text-nowrap" onclick="resetFilter()" type="button" id="addBtn">
                                <i class="mdi mdi-restore"></i> Reset Filter</a>
                            </button>
                        </div>

                    </div>
                </div>

                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" width="60px">Applicant</th>
                            <th scope="col" class='text-nowrap'>Leave Type</th>
                            <th scope="col">From Day</th>
                            <th scope="col">To Day</th>
                            <th scope="col" class='text-nowrap'>Total Day Leave</th>
                            <th scope="col">Remark</th>
                            <th scope="col" class='text-nowrap'>Pending Approval</th>
                            <th scope="col" class='text-nowrap'>Successful Aproval</th>
                            <th scope="col" class='text-nowrap'>Reject Approval</th>
                            <th scope="col">Status</th>
                            <th scope="col" id="action_col" style="width: 100px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <th scope="row"><?= $num++; ?></th>
                                <?php
                                $leaveTypeResult = getData('name', 'id="' . $row['leave_type']  . '"',  '', L_TYPE, $connect);
                                $leaveTypeRow = $leaveTypeResult->fetch_assoc();
                                ?>
                                <td scope="row" class='text-nowrap'>
                                    <?php
                                    $userResult = getData('name', 'id="' .  $row['applicant'] . '"', '', EMPPERSONALINFO, $connect);
                                    $userRow = $userResult->fetch_assoc();
                                    if (isset($userRow['name']))
                                        echo  $userRow['name'];
                                    ?>
                                </td>
                                <td scope="row" class='text-nowrap'><?php if (isset($leaveTypeRow['name'])) echo $leaveTypeRow['name'] ?></td>
                                <td scope="row" class='text-nowrap'><?php if (isset($row['from_time'])) echo $row['from_time'] ?></td>
                                <td scope="row" class='text-nowrap'><?php if (isset($row['to_time'])) echo $row['to_time'] ?></td>
                                <td scope="row" class='text-nowrap'><?php if (isset($row['numOfdays'])) echo $row['numOfdays'] ?></td>
                                <td scope="row" class='text-nowrap'><?php if (isset($row['remark'])) echo $row['remark'] ?></td>
                                <td scope="row" class='text-nowrap'>
                                    <div class="d-flex flex-nowrap">
                                        <?php
                                        if (isset($row['pending_approver']) && $row['pending_approver']) {
                                            $pendingApprover =  explode(',', $row['pending_approver']);
                                            foreach ($pendingApprover as $value) {
                                                $userResult = getData('name', 'id="' . $value . '"', '', USR_USER, $connect);
                                                $userRow = $userResult->fetch_assoc();
                                                if (isset($userRow['name']))
                                                    echo '<div class="border py-1 px-3 mb-1 me-2 rounded-pill text-center">' . $userRow['name'] . '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td scope="row" class='text-nowrap'>
                                    <div class="d-flex flex-nowrap">
                                        <?php
                                        if (isset($row['success_approver']) && $row['success_approver']) {
                                            $pendingApprover =  explode(',', $row['success_approver']);
                                            foreach ($pendingApprover as $value) {
                                                $userResult = getData('name', 'id="' . $value . '"', '', USR_USER, $connect);
                                                $userRow = $userResult->fetch_assoc();
                                                if (isset($userRow['name']))
                                                    echo '<div class="border py-1 px-3 mb-1 me-2 rounded-pill text-center">' . $userRow['name'] . '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td scope="row" class='text-nowrap'>
                                    <div class="d-flex flex-nowrap">
                                        <?php
                                        if (isset($row['reject_approver']) && $row['reject_approver']) {
                                            $pendingApprover =  explode(',', $row['reject_approver']);
                                            foreach ($pendingApprover as $value) {
                                                $userResult = getData('name', 'id="' . $value . '"', '', USR_USER, $connect);
                                                $userRow = $userResult->fetch_assoc();
                                                if (isset($userRow['name']))
                                                    echo '<div class="border py-1 px-3 mb-1 me-2 rounded-pill text-center">' . $userRow['name'] . '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td scope="row" class='text-nowrap'>
                                    <button class="roundedSelectionBtn text-capitalize text-start" style="font-size: 13px; width:88px;">
                                        <?php if (isset($row['leave_transaction_status'])) { ?>
                                            <?php
                                            if ($row['leave_transaction_status'] == 'pending')
                                                $buttonColor = 'blue';
                                            else if ($row['leave_transaction_status'] == 'declined' || $row['leave_transaction_status'] == 'cancel')
                                                $buttonColor = 'red';
                                            else if ($row['leave_transaction_status'] == 'approval')
                                                $buttonColor = 'green';
                                            ?>
                                            <span class="mdi mdi-record-circle-outline" id="leaveTransactionStatus" style="color:<?= $buttonColor ?>;"></span>&nbsp;<?= $row['leave_transaction_status'] ?>&nbsp;
                                        <?php } ?>
                                    </button>
                                </td>

                                <td scope="row">
                                    <?php if (isset($row['applicant'], $row['id'])) { ?>
                                        <div class="dropdown" style="text-align:center">
                                            <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                                <li>
                                                    <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                                        <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] . "&empID=" . $row['applicant'] ?>&page=allleave">View</a>
                                                    <?php endif; ?>
                                                </li>

                                                <?php if ($row['leave_transaction_status'] === 'pending') { ?>
                                                    <li>
                                                        <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                                            <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 . "&empID=" . $row['applicant'] ?>&page=allleave">Edit</a>
                                                        <?php endif; ?>
                                                    </li>
                                                    <li>
                                                        <?php if (isActionAllowed("Cancel", $pinAccess)) : ?>
                                                            <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>','','<?php echo $pageTitle ?>','<?= $redirect_page ?>','','LC')">Cancel</a>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }  ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" width="60px">Applicant</th>
                            <th scope="col">Leave Type</th>
                            <th scope="col">From Day</th>
                            <th scope="col">To Day</th>
                            <th scope="col">Total Day Leave</th>
                            <th scope="col">Remark</th>
                            <th scope="col">Pending Approval</th>
                            <th scope="col">Successful Aproval</th>
                            <th scope="col">Reject Approval</th>
                            <th scope="col">Status</th>
                            <th scope="col" id="action_col" style="width: 100px;">Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        //to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
        dropdownMenuDispFix();
        //to resize table with bootstrap 5 classes
        datatableAlignment('table');
        datatableAlignment('empTable');
        setButtonColor();

        function resetFilter() {
            var filterLeaveRadio = document.querySelector('input[name="filterLeave"]:checked');
            var dataTable = $('#table').DataTable();

            dataTable.search('').columns().search('').draw();

            if (filterLeaveRadio)
                filterLeaveRadio.checked = false;
        }

        function updateFilterValue() {
            var filterLeaveRadio = document.querySelector('input[name="filterLeave"]:checked');
            var filterValueDiv = document.querySelector("#table_filter input");
            var dataTable = $('#table').DataTable();

            if (filterLeaveRadio) {
                var filterValueArray = filterLeaveRadio.value.split(':');

                if (filterValueArray.length === 2) {
                    var column = filterValueArray[0].trim();
                    var value = filterValueArray[1].trim();

                    var columnIndex = dataTable.columns().header().toArray().findIndex(function(th) {
                        return th.textContent.trim() === column;
                    });

                    dataTable.search('').columns().search('');

                    if (columnIndex !== -1) {
                        dataTable.column(columnIndex).search(value).draw();
                        if (filterValueDiv)
                            filterValueDiv.value = '';
                    }
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            updateFilterValue();
            document.addEventListener("change", updateFilterValue);
        });

        window.addEventListener('beforeunload', function(event) {
            resetFilter();
        });
    </script>

</body>

</html>