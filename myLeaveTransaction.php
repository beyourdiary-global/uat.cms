<?php
$pageTitle = "My Leave Transaction";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = L_PENDING;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/leaveTransaction.php';
$errorRedirectPage = $SITEURL . '/dashboard.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$redirectErrorLink = ("<script>location.href = '$errorRedirectPage';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';
$errorMsgAlert = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";

//Current Employee ID 
if (isRecordExist('employee_personal_info', 'id', USER_ID, $connect)) {
    $currEmpID = USER_ID;

    $result = getData('*', 'applicant="' . $currEmpID  . '"', '', $tblName, $connect);

    //All Leave Type
    $allLeaveTypeResult = getData('id,name', 'leave_status="Active" AND auto_assign = "yes"',  '', L_TYPE, $connect);

    if (!$allLeaveTypeResult) {
        echo $errorMsgAlert . $clearLocalStorage . $redirectErrorLink;
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
} else
    $empIDExistError = true;

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingMyLeaveTransactionTable('table');
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

                <?php if (isset($empIDExistError) && $empIDExistError) { ?>
                    <div class="alert alert-danger fade show" role="alert">
                        <p style="margin:0;text-align: justify;text-transform: capitalize;">Invalid or non-existent employee ID. Please contact the IT department for further assistance.</p>
                    </div>
                <?php } else { ?>

                    <div class="row g-6 mb-4">
                        <div class="col-xl-4 col-sm-6 col-12 my-2">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Balance Annual leaves</span>
                                            <span class="h3 font-bold mb-0">
                                                <?php
                                                $annualLeaveIDResult = getData('id', 'name like "annual leave"', '', L_TYPE, $connect);
                                                $annualLeaveIDRow = $annualLeaveIDResult->fetch_assoc();
                                                $annualLeaveID = $annualLeaveIDRow['id'];

                                                $annualLeaveBalanceResult = getData('*', 'employeeID="' . $currEmpID  . '"', '', EMPLEAVE, $connect);
                                                $annualLeaveBalanceRow = $annualLeaveBalanceResult->fetch_assoc();

                                                $annualLeave = "leaveType_" . $annualLeaveID;

                                                if (isset($annualLeaveBalanceRow[$annualLeave]))
                                                    echo $annualLeaveBalanceRow[$annualLeave] . " Day";
                                                else
                                                    echo "0 Day";

                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-tertiary text-white text-lg rounded-circle">
                                                <i class="bi bi-credit-card"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6 col-12 my-2">
                            <div class="card ">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Pending Request</span>
                                            <span class="h3 font-bold mb-0">
                                                <?php
                                                $balanceAnnualLeaveResult = getData('leave_type,sum(numOfdays) as totalDays', 'leave_type="' . $annualLeaveID . '" AND leave_transaction_status = "pending" AND applicant="' . $currEmpID  . '" GROUP BY leave_type', '', L_PENDING, $connect);
                                                $balanceAnnualLeaveRow = $balanceAnnualLeaveResult->fetch_assoc();

                                                if (isset($balanceAnnualLeaveRow['totalDays']) && $balanceAnnualLeaveRow['totalDays'])
                                                    echo $balanceAnnualLeaveRow['totalDays'] . " Day";
                                                else
                                                    echo "0 Day";
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-primary text-white text-lg rounded-circle">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6 col-12 my-2">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Used Annual Leave</span>
                                            <span class="h3 font-bold mb-0">
                                                <?php
                                                $UsedAnnualLeaveResult = getData('leave_type,sum(numOfdays) as totalDays', 'leave_type="' . $annualLeaveID . '" AND leave_transaction_status = "approval" AND applicant="' . $currEmpID  . '" GROUP BY leave_type', '', L_PENDING, $connect);
                                                $UsedAnnualLeaveRow = $UsedAnnualLeaveResult->fetch_assoc();

                                                if (isset($UsedAnnualLeaveRow['totalDays']) && $UsedAnnualLeaveRow['totalDays'])
                                                    echo $UsedAnnualLeaveRow['totalDays'] . " Day";
                                                else
                                                    echo "0 Day";
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-info text-white text-lg rounded-circle">
                                                <i class="bi bi-clock-history"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex overflow-auto flex-column mb-3">
                        <div class="col-12 d-flex flex-nowrap">
                            <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <div class="mt-auto mb-2 me-2">
                                    <a class="btn btn-sm btn-rounded btn-primary text-nowrap" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add <?php echo $pageTitle ?> </a>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto mb-2 me-2">
                                <a class="btn btn-sm btn-rounded btn-primary text-nowrap" id="addBtn" href="<?= $SITEURL ?>/expandMyLeaveTranscation.php"><i class="mdi mdi-arrow-expand"></i> Expand Leave Transaction</a>
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
                                <th scope="col" class='text-nowrap'>Leave Type</th>
                                <th scope="col">From Day</th>
                                <th scope="col">To Day</th>
                                <th scope="col" class='text-nowrap'>Total Day Leave</th>
                                <th scope="col">Remark</th>
                                <th scope="col">Status</th>
                                <th scope="col" id="action_col" style="width: 100px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                if (isset($row['applicant'], $row['id']) && !empty($row['applicant'])) { ?>

                                    <tr>
                                        <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                        <th scope="row"><?= $num++; ?></th>
                                        <?php
                                        $leaveTypeResult = getData('name', 'id="' . $row['leave_type']  . '"',  '', L_TYPE, $connect);
                                        $leaveTypeRow = $leaveTypeResult->fetch_assoc();
                                        ?>
                                        <td scope="row" class='text-nowrap'><?php if (isset($leaveTypeRow['name'])) echo $leaveTypeRow['name'] ?></td>
                                        <td scope="row" class='text-nowrap'><?php if (isset($row['from_time'])) echo $row['from_time'] ?></td>
                                        <td scope="row" class='text-nowrap'><?php if (isset($row['to_time'])) echo $row['to_time'] ?></td>
                                        <td scope="row" class='text-nowrap'><?php if (isset($row['numOfdays'])) echo $row['numOfdays'] ?></td>
                                        <td scope="row" class='text-nowrap'><?php if (isset($row['remark'])) echo $row['remark'] ?></td>
                                        <td scope="row" class='text-nowrap'>
                                            <?php if (isset($row['leave_transaction_status'])) { ?>
                                                <button class="roundedSelectionBtn text-capitalize text-start" style="font-size: 13px; width:88px;">
                                                    <?php
                                                    if ($row['leave_transaction_status'] == 'pending')
                                                        $buttonColor = 'blue';
                                                    else if ($row['leave_transaction_status'] == 'declined' || $row['leave_transaction_status'] == 'cancel')
                                                        $buttonColor = 'red';
                                                    else if ($row['leave_transaction_status'] == 'approval')
                                                        $buttonColor = 'green';
                                                    ?>
                                                    <span class="mdi mdi-record-circle-outline" style="color:<?= $buttonColor ?>;"></span>&nbsp;<?= $row['leave_transaction_status'] ?>&nbsp;
                                                </button>
                                            <?php } ?>
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
                                                                <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] ?>">View</a>
                                                            <?php endif; ?>
                                                        </li>

                                                        <?php if ($row['leave_transaction_status'] === 'pending') { ?>
                                                            <li>
                                                                <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                                                    <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">Edit</a>
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
                            <?php }
                            }  ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col" width="60px">S/N</th>
                                <th scope="col">Leave Type</th>
                                <th scope="col">From Day</th>
                                <th scope="col">To Day</th>
                                <th scope="col">Total Day Leave</th>
                                <th scope="col">Remark</th>
                                <th scope="col">Status</th>
                                <th scope="col" id="action_col" style="width: 100px;">Action</th>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
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
        setButtonColor();

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

                    if (columnIndex !== -1) {
                        dataTable.column(columnIndex).search(value).draw();
                        if (filterValueDiv)
                            filterValueDiv.value = '';
                    }
                }
            }
        }

        function resetFilter() {
            var filterLeaveRadio = document.querySelector('input[name="filterLeave"]:checked');
            var dataTable = $('#table').DataTable();

            dataTable.search('').columns().search('').draw();

            if (filterLeaveRadio)
                filterLeaveRadio.checked = false;
        }


        document.addEventListener("DOMContentLoaded", function() {
            updateFilterValue();
            document.addEventListener("change", updateFilterValue);
        });
    </script>

</body>

</html>