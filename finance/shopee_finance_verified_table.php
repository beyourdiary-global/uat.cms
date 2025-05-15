<?php
$pageTitle = "Shopee Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering


$verifyMessage = ''; // declare message holder

if (isset($_GET['verify_id'])) {
    $orderId = intval($_GET['verify_id']);

    // Step 1: Check current order status
    $checkSql = "SELECT order_status FROM " . SHOPEE_SG_ORDER_REQ . " WHERE id = $orderId";
    $checkResult = mysqli_query($finance_connect, $checkSql);

    if ($checkResult && $row = mysqli_fetch_assoc($checkResult)) {
        if ($row['order_status'] === 'OC') {
            // Step 2: Update to 'C'
            $updateSql = "UPDATE " . SHOPEE_SG_ORDER_REQ . " SET order_status = 'C' WHERE id = $orderId";
            $updateResult = mysqli_query($finance_connect, $updateSql);

            if ($updateResult) {
                $verifyMessage = "✅ Order #$orderId has been successfully verified.";
            } else {
                $verifyMessage = "❌ Failed to update order #$orderId.";
            }
        } else {
            $verifyMessage = "⚠️ Order #$orderId is not in 'OC' status.";
        }
    } else {
        $verifyMessage = "❌ Order #$orderId not found.";
    }
}




$redirect_page = $SITEURL . '/finance/shopee_order_req.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_finance_verified_table.php';
$result = getData('*', 'order_status="OC"', '', SHOPEE_SG_ORDER_REQ, $finance_connect);

?>

    <!DOCTYPE html>
    <html>

    <head>
        <link rel="stylesheet" href="../css/main.css">
    </head>

    <script>
        $(document).ready(() => {
            createSortingTable('shopee_finance_verified_table');
        });
    </script>

    <style>
        .btn-verified {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #fff;
            background-color: #0d6efd;
            border: 1px solid #0d6efd;
            border-radius: 0.25rem;
            text-decoration: none;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
    
        .btn-verified:hover {
            background-color: #0b5ed7;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
    
        .btn-verified:active {
            background-color: #0a58ca;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
        }
    </style>


    <body>
<?php if (!empty($verifyMessage)): ?>
    <div class="alert alert-info">
        <?= $verifyMessage ?>
    </div>
<?php endif; ?>

        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

            <div class="col-12 col-md-11">

                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i>
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
                                        <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . " ?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
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
                <div class="table-responsive-sm">
                    <table class="table table-striped" id="shopee_order_req_table">
                        <thead>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col" width="60px">S/N</th>
                                <th scope="col" id="action_col" width="100px">Action</th>
                                <th scope="col">Order Status</th>
                                <th scope="col">Shopee Account</th>
                                <th scope="col">Currency</th>
                                <th scope="col">Order ID</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Package</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Shopee Buyer Username</th>
                                <th scope="col">Buyer Payment Method</th>
                                <th scope="col">Product Price</th>
                                <th scope="col">Voucher</th>
                                <th scope="col">Actual Shipping Fee</th>
                                <th scope="col">Service Fee (incl. GST)</th>
                                <th scope="col">Transaction Fee (incl. GST)</th>
                                <th scope="col">AMS Commission Fee</th>
                                <th scope="col">Fees & Charges</th>
                                <th scope="col">Final Amount</th>
                                <th scope="col">Remark</th>
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

                                    <td scope="row" class="btn-container">
                                        <a href="?verify_id=<?= $row['id'] ?>" class="btn btn-sm btn-success btn-verified" onclick="return confirm('Mark this order as verified?')">
                                            Verified
                                        </a>

                                    </td>
                                    <td scope="row">
                                        <?= getOrderStatusLabel($row['order_status']) ?>
                                    </td>

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
                                </tr>
                                <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col" width="60px">S/N</th>
                                <th scope="col" id="action_col" width="100px">Action</th>
                                <th scope="col">Order Status</th>
                                <th scope="col">Shopee Account</th>
                                <th scope="col">Currency</th>
                                <th scope="col">Order ID</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Package</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Shopee Buyer Username</th>
                                <th scope="col">Buyer Payment Method</th>
                                <th scope="col">Product Price</th>
                                <th scope="col">Voucher</th>
                                <th scope="col">Actual Shipping Fee</th>
                                <th scope="col">Service Fee (incl. GST)</th>
                                <th scope="col">Transaction Fee (incl. GST)</th>
                                <th scope="col">AMS Commission Fee</th>
                                <th scope="col">Fees & Charges</th>
                                <th scope="col">Final Amount</th>
                                <th scope="col">Remark</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                    <?php } ?>
            </div>

        </div>

    </body>

    <script>
     $('#shopee_order_req_table').DataTable({
        responsive: true
    });
    
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