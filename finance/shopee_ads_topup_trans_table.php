<?php
$pageTitle = "Shopee Ads Top Up Transaction";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_ads_topup_trans.php';
$result = getData('*', '', '', SHOPEE_ADS_TOPUP, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('shopee_ads_topup_trans_table');
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
                        <h2><?php echo $pageTitle ?></h2>
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Transaction </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="shopee_ads_topup_trans_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col">Shopee Account</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">DateTime</th>
                        <th scope="col">Currency</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">GST (%)</th>
                        <th scope="col">Payment Method</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) {
                        if (isset($row['orderID'], $row['id']) && !empty($row['orderID'])) {
                            $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", 'LIMIT 1', SHOPEE_ACC, $finance_connect);
                            $shopee_acc = $q1->fetch_assoc();
                            $q2 = getData('unit', "id='" . $row['currency'] . "'", 'LIMIT 1', CUR_UNIT, $connect);
                            $curr = $q2->fetch_assoc();
                            $q3 = getData('name', "id='" . $row['pay_meth'] . "'", 'LIMIT 1', FIN_PAY_METH, $finance_connect);
                            $pay = $q3->fetch_assoc();
                    ?>
                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <th scope="row"><?= $num++; ?></th>
                                <td scope="row"><?php if (isset($shopee_acc['name'])) echo  $shopee_acc['name'] ?></td>
                                <td scope="row"><?= $row['orderID'] ?></td>
                                <td scope="row"><?php if (isset($row['payment_date'])) echo $row['payment_date'] ?></td>
                                <td scope="row"><?php if (isset($curr['unit'])) echo $curr['unit'] ?></td>
                                <td scope="row"><?php if (isset($row['topup_amt'])) echo  $row['topup_amt'] ?></td>
                                <td scope="row"><?php if (isset($row['subtotal'])) echo  $row['subtotal'] ?></td>
                                <td scope="row"><?php if (isset($row['gst'])) echo  $row['gst'] ?></td>
                                <td scope="row"><?php if (isset($pay['name'])) echo  $pay['name'] ?></td>
                                <td scope="row"><?php if (isset($row['remark'])) echo $row['remark'] ?></td>
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
                                                    <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['shopee_acc'] ?>','<?= $row['orderID'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/shopee_ads_topup_trans_table.php','D')">Delete</a>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col">Shopee Account</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">DateTime</th>
                        <th scope="col">Currency</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">GST (%)</th>
                        <th scope="col">Payment Method</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
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
    datatableAlignment('shopee_ads_topup_trans_table');
</script>

</html>