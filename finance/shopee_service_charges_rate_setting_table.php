<?php
$pageTitle = "Shopee Service Charges Rate Setting";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_service_charges_rate_setting.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_service_charges_rate_setting_table.php';
$result = getData('*', '', '', SHOPEE_SCR_SETT, $finance_connect);
// if (!$result) {
//     echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
//     echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
// }

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
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
            <div class="col-12 col-md-11">


                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Setting </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!$result) {
                    echo '<div class="text-center"><h4>No Result!</h4></div>';
                } else {
                ?>
                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Commission Fees Rate (%)</th>
                            <th scope="col">Service Fee Rate (%)</th>
                            <th scope="col">Transaction Fee (%)</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                         while ($row = $result->fetch_assoc()) {
                            if (isset($row['id']) && !empty($row['id'])) {
                            $curr = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);
                            $row2 = $curr->fetch_assoc();
                        ?>
    
                                <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++; ?></th>
                                    <td scope="row" class="btn-container">
                                    <div class="d-flex align-items-center">
                                    <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess);?>
                                    <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2) ?>
                                    <?php renderDeleteButton($pinAccess, $row['id'], $currencyUnit,$row['commission'], $pageTitle, $redirect_page, $deleteRedirectPage) ?>
                                    </div>
                                    </td>
                                    <td scope="row"><?php if (isset($row2['unit'])) echo $row2['unit'] ?></td>
                                    <td scope="row"><?php if (isset($row['commission'])) echo $row['commission'] ?></td>
                                    <td scope="row"><?php if (isset($row['service'])) echo $row['service'] ?></td>
                                    <td scope="row"><?php if (isset($row['transaction'])) echo $row['transaction'] ?></td>
                                </tr>
                        <?php }
                         }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Commission Fees Rate (%)</th>
                            <th scope="col">Service Fee Rate (%)</th>
                            <th scope="col">Transaction Fee (%)</th>
                        </tr>
                    </tfoot>
                </table><?php } ?>
            </div>
        </div>
    </div>

    <script>
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