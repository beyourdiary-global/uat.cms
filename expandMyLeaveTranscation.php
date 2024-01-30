<?php
$pageTitle = "All Leave Transaction";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = BANK;
//$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

//Redirect Link
$redirect_page = $SITEURL . '/employeeDetails.php';
$errorRedirectLink = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script><script>location.href ='$SITEURL/dashboard.php';</script>";

//Current Employee ID 
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

//Array
$combinedLeaveArr = $balanceLeaveTransactionArr = $allLeaveTypeArr = $successLeaveTransactionArr = $pendingLeaveTransactionArr  = $leaveBalance = $leaveUsed = $arr1 = $arr2 = $arr3 = $arr4 = array();

//Employee All Leave transaction
$allLeaveTransactionResult = getData('*', 'employeeID="' . $currEmpID  . '"',  '', EMPLEAVE, $connect);

if (!$allLeaveTransactionResult) {
    echo $errorRedirectLink;
}

while ($row = $allLeaveTransactionResult->fetch_assoc()) {
    $processedRow = array();

    foreach ($row as $key => $value) {
        if (strpos($key, 'leaveType') === 0) {
            // Remove "leaveType_" prefix and keep key-value pair
            $processedRow[str_replace('leaveType_', '', $key)] = intval($value);
        }
    }
    $arr1[] = $processedRow;
}

foreach ($arr1[0] as $key => $value) {
    $newValue = floatval($value);
    $balanceLeaveTransactionArr[$key] = $newValue;
}

//All Success Leave Transaction Success Apply
$successLeaveResult = getData('leave_type,sum(numOfdays) as totalUsedDays', 'leave_transaction_status="approval" AND applicant="' . $currEmpID  . '" GROUP BY leave_type',  '', L_PENDING, $connect);

if (!$successLeaveResult) {
    echo $errorRedirectLink;
}

while ($successLeaveRow = $successLeaveResult->fetch_assoc()) {
    array_push($arr2, $successLeaveRow);
}

foreach ($arr2 as $item) {
    $leaveType = $item['leave_type'];
    $totalUsedDays = floatval($item['totalUsedDays']);

    $successLeaveTransactionArr[$leaveType] = $totalUsedDays;
}

//All Pending Leave Transaction Success Apply
$pendingLeaveResult = getData('leave_type,sum(numOfdays) as totalUsedDays', 'leave_transaction_status="pending" AND applicant="' . $currEmpID  . '" GROUP BY leave_type',  '', L_PENDING, $connect);

if (!$pendingLeaveResult) {
    echo $errorRedirectLink;
}

while ($pendingLeaveRow = $pendingLeaveResult->fetch_assoc()) {
    array_push($arr3, $pendingLeaveRow);
}

foreach ($arr3 as $item) {
    $leaveType = $item['leave_type'];
    $totalUsedDays = floatval($item['totalUsedDays']);

    $pendingLeaveTransactionArr[$leaveType] = $totalUsedDays;
}

//All Leave Type
$allLeaveTypeResult = getData('id,name', 'leave_status="Active" AND auto_assign = "yes"',  '', L_TYPE, $connect);

if (!$allLeaveTypeResult) {
    echo $errorRedirectLink;
}

while ($allLeaveTypeRow = $allLeaveTypeResult->fetch_assoc()) {
    array_push($arr4, $allLeaveTypeRow);
}

foreach ($arr4 as $item) {
    $id = $item['id'];
    $name = $item['name'];

    $allLeaveTypeArr[$id] = $name;
}

ksort($pendingLeaveTransactionArr);
ksort($successLeaveTransactionArr);
ksort($balanceLeaveTransactionArr);
ksort($allLeaveTypeArr);

foreach ($allLeaveTypeArr as $key => $value) {
    $combinedLeaveArr[$key] = [
        'type' => $value,
        'balance' => isset($balanceLeaveTransactionArr[$key]) ? $balanceLeaveTransactionArr[$key] : 0,
        'pending' => isset($pendingLeaveTransactionArr[$key]) ? $pendingLeaveTransactionArr[$key] : 0,
        'approved' => isset($successLeaveTransactionArr[$key]) ? $successLeaveTransactionArr[$key] : 0,
    ];
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

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">
            <div class="col-12 col-md-8">

                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/myLeaveTransaction.php">Leave Transaction Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                            <div class="mt-auto mb-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th class='text-nowrap' scope='col'>Leave Type</th>
                            <th class='text-nowrap' scope='col' width="200px">Balance</th>
                            <th class='text-nowrap' scope='col' width="200px">Pending Request</th>
                            <th class='text-nowrap' scope='col' width="200px">Used</th>
                        </tr>
                    </thead>
 
                    <tbody>
                        <?php
                        $x = 1;
                        foreach ($combinedLeaveArr as $key) {
                            echo "<tr>";
                            echo "<th class='hideColumn text-nowrap' scope='row'>$x</th>";
                            echo "<th class='text-nowrap' scope='row'>$x</th>";
                            echo "<td class='text-nowrap' scope='row'>" . $key['type'] . "</td>";
                            echo "<td class='text-nowrap' scope='row'>" . ($key['balance'] - $key['approved']) . " Day</td>";
                            echo "<td class='text-nowrap' scope='row'>" . $key['pending'] . " Day</td>";
                            echo "<td class='text-nowrap' scope='row'>" . $key['approved'] . " Day</td>";
                            echo "</tr>";
                            $x++;
                        }
                        ?>

                    </tbody>

                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th class='text-nowrap' scope='col'>Leave Type</th>
                            <th class='text-nowrap' scope='col'>Balance</th>
                            <th class='text-nowrap' scope='col'>Pending</th>
                            <th class='text-nowrap' scope='col'>Used</th>
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
        setButtonColor();
    </script>

</body>

</html>