<?php
$pageTitle = "Stock Credit Top Up Record Summary";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = STK_CDT_TOPUP_RCD;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/stock_credit_top_up_request.php';
$deleteRedirectPage = $SITEURL . '/finance/stock_credit_top_up_request_table.php';

$result = getData('*', '', '', $tblName, $finance_connect);

if (!$result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
      <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('table');
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

                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Merchant</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Total Amount</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php 
                        
                        while ($row = $result->fetch_assoc()) {    
                            if (isset($_GET['ids'])) {
                                $ids = explode(',', $_GET['ids']);
                               foreach ($ids as $id) {
                                $decodedId = urldecode($id);   
                           if (isset($row['id']) && !empty($row['id']&& $row['id'] == $id)) {

                            $merchant = getData('name', "id='" . $row['merchant'] . "'", '', MERCHANT, $finance_connect);
                            $row3 = $merchant->fetch_assoc();

                            $resultBrand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $rowBrand = $resultBrand->fetch_assoc();

                            $currency = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);
                            $row2 = $currency->fetch_assoc();
                        ?>
                            <tr onclick="window.location='stock_credit_top_up_request_table_detail.php?ids=<?= urlencode($row['id']) ?>';" style="cursor:pointer;">
                            <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                            <th scope="row"><?= $num++; ?></th>
                            <td scope="row"><?php if (isset($row3['name'])) echo  $row3['name'] ?></td>
                            <td scope="row"><?php if (isset($rowBrand['name'])) echo $rowBrand['name'] ?></td>
                            <td scope="row"><?php if (isset($row2['unit'])) echo $row2['unit'] ?></td>
                            <td scope="row"><?php if (isset($row['amount'])) echo $row['amount'] ?></td>
                        </tr>
                    <?php }
                               }
                            }
                     }?>
                </tbody>
                <tfoot>
                        <tr>
                        <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Merchant</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Total Amount</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        </body>

    <script>
        //to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
        dropdownMenuDispFix();
        //to resize table with bootstrap 5 classes
        datatableAlignment('table');
        setButtonColor();
    </script>

</body>

</html>