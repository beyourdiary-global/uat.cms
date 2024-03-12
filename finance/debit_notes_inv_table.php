<?php
$pageTitle = "Debit Notes (Invoice)";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = DEBIT_NOTES_INV;
$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/debit_notes_inv.php';
$result = getData('*', '', '', $tblName, $finance_connect);

if (post('pay_status_option')) {
    $inv_id = post('inv_id');
    $payment_status = post('pay_status_option');

    $datafield = $oldvalarr = $chgvalarr = array();

    $rst = getData('*', "id = '$inv_id'", '', $tblName, $finance_connect);

    echo "<script>console.log('TEST2')</script>";

    if (!$rst) {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</>";
        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
    }

    $rowInv = $rst->fetch_assoc();

    echo "<script>console.log('TEST3')</script>";

    if ($rowInv['payment_status'] !== $payment_status) {
        array_push($oldvalarr, $rowInv['payment_status']);
        array_push($chgvalarr, $payment_status);
        array_push($datafield, 'payment_status');
    }

    echo "<script>console.log('TEST4')</script>";

    if ($oldvalarr && $chgvalarr) {
        try {
            $query = "UPDATE " . $tblName . " SET payment_status = '$payment_status' WHERE id = '$inv_id'";
            mysqli_query($finance_connect, $query);
            generateDBData($tblName, $finance_connect);
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }

        // audit log
        $log = [
            'log_act' => 'edit',
            'cdate' => $cdate,
            'ctime' => $ctime,
            'uid' => USER_ID,
            'cby' => USER_ID,
            'query_rec' => $query,
            'query_table' => $tblName,
            'page' => $pageTitle,
            'connect' => $connect,
            'oldval' => implodeWithComma($oldvalarr),
            'changes' => implodeWithComma($chgvalarr),
            'act_msg' => actMsgLog($payment_status, $datafield, '', $oldvalarr, $chgvalarr, $tblName, 'edit', (isset($returnData) ? '' : $errorMsg))
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
        createSortingTable('debit_notes_inv_table');
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
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                                class="fa-solid fa-chevron-right fa-xs"></i>
                            <?php echo $pageTitle ?>
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2>
                                <?php echo $pageTitle ?>
                            </h2>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        <?php echo $pageTitle ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="debit_notes_inv_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Merchant</th>
                            <th scope="col">Invoice ID</th>
                            <th scope="col">Issued Date</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Due Date</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            $mrcht = getData('name', "id='" . $row['bill_nameID'] . "'", '', MERCHANT, $finance_connect);
                            $row2 = $mrcht->fetch_assoc();
                            ?>
                            <?php $payment_status = $row['payment_status']; ?>
                            <tr>
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th scope="row">
                                    <?= $num;
                                    $num++ ?>
                                </th>
                                <td scope="row">
                                    <?php if (isset($row2['name']))
                                        echo $row2['name'] ?>
                                    </td>
                                    <td scope="row">
                                    <?= $row['invoice'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['date'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['total'] ?>
                                </td>
                                <td scope="row">
                                    <div class="dropdown">
                                        <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#"
                                            id="paymentStatusMenu" role="button" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <button class="roundedSelectionBtn">
                                                <span class="mdi mdi-record-circle-outline" style="<?php
                                                if ($payment_status == 'Paid') {
                                                    echo 'color:#008000;';
                                                } else if ($payment_status == 'Cancelled') {
                                                    echo 'color:#ff0000;';
                                                } else {
                                                    echo 'color:#F17FB5;';
                                                } ?>"></span>
                                                <?php
                                                switch ($payment_status) {
                                                    case 'Paid':
                                                        echo 'Paid';
                                                        break;
                                                    case 'Cancelled':
                                                        echo 'Cancelled';
                                                        break;
                                                    default:
                                                        echo 'Pending';
                                                }
                                                ?>
                                            </button>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="payStatusMenu">
                                            <li>
                                                <a class="dropdown-item" id="pendingOption" href=""
                                                    onclick="updatepayStatus(<?= $row['id'] ?>,'Pending')"><span
                                                        class="mdi mdi-record-circle-outline" style="color:#F17FB5"></span>
                                                    Pending</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" id="paidOption" href=""
                                                    onclick="updatepayStatus(<?= $row['id'] ?>,'Paid')"><span
                                                        class="mdi mdi-record-circle-outline" style="color:#008000"></span>
                                                    Paid</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" id="cancelledOption" href=""
                                                    onclick="updatepayStatus(<?= $row['id'] ?>,'Cancelled')"><span
                                                        class="mdi mdi-record-circle-outline" style="color:#ff0000"></span>
                                                    Cancelled</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td scope="row">
                                    <?= $row['due_date'] ?>
                                </td>
                                <td scope="row">
                                    <div style="text-align: center;">
                                        <?php if (isActionAllowed("View", $pinAccess)): ?>
                                            <a class="icon-buttons rounded p-1 bg-label-success"
                                                href="<?= $redirect_page . "?id=" . $row['id'] ?>" title="View"><i
                                                    class="fas fa-eye bg-label-success"></i></a>
                                        <?php endif; ?>

                                        <?php if (isActionAllowed("Edit", $pinAccess)): ?>
                                            <a class="icon-buttons rounded p-1 bg-label-warning"
                                                href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>"
                                                title="Edit"><i class="fas fa-edit bg-label-warning"></i></a>
                                        <?php endif; ?>

                                        <a class="icon-buttons rounded p-1 bg-label-info" target="_blank" href="generate_pdf.php?id=<?= $row['id'] . '&act=' . $act_2  . '&isDebit=1' ?>"
                                            title="Download"><i class="fas fa-download bg-label-info"></i></a>

                                        <?php if (isActionAllowed("Delete", $pinAccess)): ?>
                                            <a class="icon-buttons rounded p-1 bg-label-danger"
                                                onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['invoice'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/debit_notes_inv_table.php','D')"
                                                title="Delete"><i class="fas fa-trash-alt bg-label-danger"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Merchant</th>
                            <th scope="col">Invoice ID</th>
                            <th scope="col">Issued Date</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Due Date</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
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
    datatableAlignment('debit_notes_inv_table');

    var pendingElem = $('#pendingOption');
    var paidElem = $('#paidOption');
    var cancelledElem = $('#cancelledOption');

    function updatepayStatus(id, status) {
        // Log data being sent
        console.log('Data sent:', { inv_id: id, pay_status_option: status });

        $.ajax({
            url: 'debit_notes_inv_table.php',
            type: 'post',
            data: {
                inv_id: id,
                pay_status_option: status,
            },
            success: function (data) {
                console.log('AJAX Success:', data);
                // Reload the page after successful update
                window.location.href = 'debit_notes_inv_table.php';
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error:', error);
                // Handle error gracefully
                // For example, display an alert message
                alert('An error occurred while updating payment status. Please try again later.');
            }
        });
    }


</script>

</html>