<?php
$pageTitle = "Shopee SG Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_order_req.php';
$result = getData('*', '', '', SHOPEE_SG_ORDER_REQ, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('shopee_order_req_table');
    });
</script>

<body>

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
                        <?php if ($result) { ?>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        Request </a>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php
            if (!$result) {
                echo '<div class="text-center"><h4>No Result!</h4></div>';
            } else {
                ?>

                <table class="table table-striped" id="shopee_order_req_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Shopee Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Package</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Shopee Buyer Username</th>
                            <th scope="col">Buyer Payment Method</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Product Price</th>
                            <th scope="col">Voucher</th>
                            <th scope="col">Actual Shipping Fee</th>
                            <th scope="col">Service Fee (incl. GST)</th>
                            <th scope="col">Transaction Fee (incl. GST)</th>
                            <th scope="col">AMS Commission Fee</th>
                            <th scope="col">Fees & Charges</th>
                            <th scope="col">Final Amount</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", '', SHOPEE_ACC, $finance_connect);
                            $acc = $q1->fetch_assoc();
                            $q7 = getData('*', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                            $curr = $q7->fetch_assoc();

                            $q2 = getData('name', "id='" . $row['package'] . "'", '', PKG, $connect);
                            $pkg = $q2->fetch_assoc();

                            $q3 = getData('name', "id='" . $row['brand'] ."'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            $q4 = getData('buyer_username', "id='" . $row['buyer'] . "'", '', SHOPEE_CUST_INFO, $finance_connect);
                            $buyer = $q4->fetch_assoc();

                            $q6 = getData('*', "id='" . $row['buyer_pay_meth'] . "'", '', PAY_MTHD_SHOPEE, $finance_connect);
                            $pay = $q6->fetch_assoc();

                            $q5 = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $pic = $q5->fetch_assoc();
                            ?>

                            <tr>
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th scope="row">
                                    <?= $num++; ?>
                                </th>
                                <td scope="row">
                                    <?= $acc['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $curr['unit'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['orderID'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['date'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['time'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pkg['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $brand['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $buyer['buyer_username'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pay['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pic['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['price'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['voucher'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['act_shipping_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['service_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['trans_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['ams_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['fees'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['final_amt'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['remark'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <div class="dropdown" style="text-align:center">
                                        <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu"
                                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg"
                                                    id="action_menu"></i></button>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                            <li>
                                                <?php if (isActionAllowed("View", $pinAccess)): ?>
                                                    <a class="dropdown-item"
                                                        href="<?= $redirect_page . "?id=" . $row['id'] ?>">View</a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <?php if (isActionAllowed("Edit", $pinAccess)): ?>
                                                    <a class="dropdown-item"
                                                        href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <?php if (isActionAllowed("Delete", $pinAccess)): ?>
                                                    <a class="dropdown-item"
                                                        onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $acc['name'] ?>','<?= $row['date'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/shopee_order_req_table.php','D')">Delete</a>
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
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Shopee Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Package</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Shopee Buyer Username</th>
                            <th scope="col">Buyer Payment Method</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Product Price</th>
                            <th scope="col">Voucher</th>
                            <th scope="col">Actual Shipping Fee</th>
                            <th scope="col">Service Fee (incl. GST)</th>
                            <th scope="col">Transaction Fee (incl. GST)</th>
                            <th scope="col">AMS Commission Fee</th>
                            <th scope="col">Fees & Charges</th>
                            <th scope="col">Final Amount</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </tfoot>
                </table>
            <?php } ?>
        </div>

    </div>

</body>
<script>
    /**
  oufei 20231014
  common.fun.js
  function(void)
  to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
*/
    dropdownMenuDispFix();

    /**
      oufei 20231014
      common.fun.js
      function(id)
      to resize table with bootstrap 5 classes
    */
    datatableAlignment('shopee_order_req_table');
</script>

</html>