<?php
$pageTitle = "Website Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/website_order_request.php';
$result = getData('*', '', '', WEB_ORDER_REQ, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('website_order_request_table');
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

                <table class="table table-striped" id="website_order_request_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Series</th>
                            <th scope="col">Package</th>
                            <th scope="col">Country</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Price</th>
                            <th scope="col">Shipping</th>
                            <th scope="col">Discount Price</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Person In Charges</th>
                            <th scope="col">Customer ID</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Customer Email</th>
                            <th scope="col">Customer Birthday</th>
                            <th scope="col">Shipping Name</th>
                            <th scope="col">Shipping Address</th>
                            <th scope="col">Shipping Contact</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            
                            $q1 = getData('unit', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                            $currency = $q1->fetch_assoc();

                            $q2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                            $country = $q2->fetch_assoc();

                            $q3 = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            $q4 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                            $series = $q4->fetch_assoc();

                            $q5 = getData('name', "id='" . $row['pkg'] . "'", '', PKG, $connect);
                            $package = $q5->fetch_assoc();

                            $q6 = getData('cust_id', "id='" . $row['cust_id'] . "'", '', WEB_CUST_RCD, $connect);
                            $cust_id = $q6->fetch_assoc();

                            $q8 = getData('name', "id='" . $row['pay_method'] . "'", '', FIN_PAY_METH, $finance_connect);
                            $pay = $q8->fetch_assoc();
                            ?>

                            <tr>
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th scope="row">
                                    <?= $num++; ?>
                                </th>
                                <td scope="row">
                                    <?= $row['order_id'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['brand'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['series'] ?? '' ?>
                                </td>
                               
                                <td scope="row">
                                    <?= $package['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $country['nicename'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $currency['unit'] ?? '' ?>
                                </td>
                              
                                <td scope="row">
                                    <?= $row['price'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['shipping'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['discount'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['total'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $pay['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['pic'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $cust_id['cust_id'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['cust_name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['cust_email'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['cust_birthday'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['shipping_name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['shipping_address'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['shipping_contact'] ?? '' ?>
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
                                                        onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['order_id'] ?>','<?= $row['brand'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/fb_order_req_table.php','D')">Delete</a>
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
                            <th scope="col">Order ID</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Series</th>
                            <th scope="col">Package</th>
                            <th scope="col">Country</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Price</th>
                            <th scope="col">Shipping</th>
                            <th scope="col">Discount Price</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Person In Charges</th>
                            <th scope="col">Customer ID</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Customer Email</th>
                            <th scope="col">Customer Birthday</th>
                            <th scope="col">Shipping Name</th>
                            <th scope="col">Shipping Address</th>
                            <th scope="col">Shipping Contact</th>
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
    datatableAlignment('website_order_request_table');
</script>

</html>