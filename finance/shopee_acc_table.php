<?php
$pageTitle = "Shopee Account";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_acc.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_acc_table.php';
$result = getData('*', '', '', SHOPEE_ACC, $finance_connect);
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
    preloader(300);
    $(document).ready(() => {
        createSortingTable('shopee_acc_table');
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
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Account </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="shopee_acc_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col" id="action_col">Action</th>
                        <th scope="col">Account Name</th>
                        <th scope="col">Country</th>
                        <th scope="col">Currency Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        if (isset($row['name'], $row['id']) && !empty($row['name'])) {

                            $currency = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);

                            $row2 = $currency->fetch_assoc();
                            $country = getData('name', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                            $row3 = $country->fetch_assoc();
                    ?>

                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <th scope="row"><?= $num++; ?></th>
                                <td scope="row" class="btn-container">
                                <div class="d-flex align-items-center">
                                <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess);?>
                                <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2) ?>
                                <?php renderDeleteButton($pinAccess, $row['id'], '', '', $pageTitle, $redirect_page, $deleteRedirectPage) ?>
                                </div>
                                </td>
                                <td scope="row"><?= isset($row['name']) ? $row['name']  : '' ?></td>
                                <td scope="row"><?= isset($row3['name']) ? $row3['name'] : '' ?></td>
                                <td scope="row"><?= isset($row2['unit']) ? $row2['unit'] : '' ?>
                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col" id="action_col">Action</th>
                        <th scope="col">Account Name</th>
                        <th scope="col">Country</th>
                        <th scope="col">Currency Unit</th>
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
    datatableAlignment('shopee_acc_table');
    setButtonColor();
</script>

</html>