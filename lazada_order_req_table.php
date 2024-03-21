<?php
$pageTitle = "Lazada Order Request";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/lazada_order_req.php';
$deleteRedirectPage = $SITEURL . '/lazada_order_req_table.php';
$result = getData('*', '', '', LAZADA_ORDER_REQ, $connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('lazada_order_req');
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

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

<div class="col-12 col-md-11">

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
                                Record </a>
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

        <table class="table table-striped" id="lazada_order_req">
            <thead>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col">S/N</th>
                    <th scope="col" id="action_col">Action</th>
                    <th scope="col">Lazada Account</th>
                    <th scope="col">Currency Unit</th>
                    <th scope="col">Country</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Country</th>
                    <th scope="col">Order Number</th>
                    <th scope="col">Sales Person In Charge</th>
                    <th scope="col">Shipping Receiver Name</th>
                    <th scope="col">Shipping Receiver Address</th>
                    <th scope="col">Shipping Receiver Contact</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Series</th>
                    <th scope="col">Package</th>
                    <th scope="col">Item Price Credit</th>
                    <th scope="col">Commision</th>
                    <th scope="col">Other Discount</th>
                    <th scope="col">Payment Fee</th>
                    <th scope="col">Final Income</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) {
                    $q1 = getData('name', "id='" . $row['lazada_acc'] . "'", '', LAZADA_ACC, $finance_connect);
                    $lazada_acc = $q1->fetch_assoc();

                    $q2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                    $country = $q2->fetch_assoc();

                    $q3 = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                    $brand = $q3->fetch_assoc();

                    $q4 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                    $series = $q4->fetch_assoc();

                    $q5 = getData('unit', "id='" . $row['curr_unit'] . "'", '', CUR_UNIT, $connect);
                    $curr_unit = $q5->fetch_assoc();

                    $q6 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                    $series = $q6->fetch_assoc();

                    $q7 = getData('name', "id='" . $row['pay_meth'] . "'", '', FIN_PAY_METH, $finance_connect);
                    $pay_meth = $q7->fetch_assoc();

                    $q8 = getData('name', "id='" . $row['pkg'] . "'", '', PKG, $connect);
                    $package = $q8->fetch_assoc();
                    ?>

                    <tr>
                        <th class="hideColumn" scope="row">
                            <?= $row['id'] ?>
                        </th>
                        <th scope="row">
                            <?= $num++; ?>
                        </th>
                        <td scope="row" class="btn-container">
                                        <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                        <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['id'] ?>"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                        <a class="btn btn-warning me-1" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                        <a class="btn btn-danger" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['curr_unit'] ?>','<?= $row['country'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                        </td>
                        <td scope="row"><?= isset($lazada_acc['name']) ? $lazada_acc['name'] : ''  ?></td>
                        <td scope="row"><?= $row['curr_unit'] ?></td>
                        <td scope="row"><?= $row['country'] ?></td>
                        <td scope="row"><?= $row['cust_id'] ?></td>
                        <td scope="row"><?= $row['cust_name'] ?></td>
                        <td scope="row"><?= $row['cust_email'] ?></td>
                        <td scope="row"><?= $row['cust_phone'] ?></td>
                        <td scope="row"><?= isset($country['nicename']) ? $country['nicename'] : ''  ?></td>
                        <td scope="row"><?= $row['oder_number'] ?></td>
                        <td scope="row"><?= $row['sales_pic'] ?></td>
                        <td scope="row"><?= $row['ship_rec_name'] ?></td>
                        <td scope="row"><?= $row['ship_rec_address'] ?></td>
                        <td scope="row"><?= $row['ship_rec_contact'] ?></td>
                        <td scope="row"><?= isset($brand['name']) ? $brand['name'] : ''  ?></td>
                        <td scope="row"><?= isset($series['name']) ? $series['name'] : ''  ?></td>
                        <td scope="row"><?= isset($package['name']) ? $package['name'] : ''  ?></td>
                        <td scope="row"><?= $row['item_price_credit'] ?></td>
                        <td scope="row"><?= $row['commision'] ?></td>
                        <td scope="row"><?= $row['other_discount'] ?></td>
                        <td scope="row"><?= $row['pay_fee'] ?></td>
                        <td scope="row"><?= $row['final_income'] ?></td>
                        <td scope="row"><?= isset($pay_meth['name']) ? $pay_meth['name'] : ''  ?></td>
                        <td scope="row"><?= $row['remark'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col">S/N</th>
                    <th scope="col" id="action_col">Action</th>
                    <th scope="col">Lazada Account</th>
                    <th scope="col">Currency Unit</th>
                    <th scope="col">Country</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Country</th>
                    <th scope="col">Order Number</th>
                    <th scope="col">Sales Person In Charge</th>
                    <th scope="col">Shipping Receiver Name</th>
                    <th scope="col">Shipping Receiver Address</th>
                    <th scope="col">Shipping Receiver Contact</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Series</th>
                    <th scope="col">Package</th>
                    <th scope="col">Item Price Credit</th>
                    <th scope="col">Commision</th>
                    <th scope="col">Other Discount</th>
                    <th scope="col">Payment Fee</th>
                    <th scope="col">Final Income</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Remark</th>
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
datatableAlignment('lazada_order_req');
</script>

</html>