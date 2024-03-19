<?php
$pageTitle = "Shopee Customer Record";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_cust_info.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_cust_info_table.php';
$result = getData('*', '', '', SHOPEE_CUST_INFO, $finance_connect);
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
        createSortingTable('shopee_cust_info_table');
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

<div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
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
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Record </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="shopee_cust_info_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col">S/N</th>
                        <th scope="col" id="action_col">Action</th>
                        <th scope="col">Shopee Buyer Username</th>
                        <th scope="col">Sales Person In Charge</th>
                        <th scope="col">Country</th>
                        <th scope="col">Brand</th>
                        <th scope="col">Series</th>
                        <th scope="col">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        if (isset($row['buyer_username'], $row['id']) && !empty($row['buyer_username'])) {

                            $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $row2 = $pic->fetch_assoc();

                            $country = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                            $row3 = $country->fetch_assoc();

                            $brand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $row4 = $brand->fetch_assoc();

                            $series = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                            $row5 = $series->fetch_assoc();
                    ?>

                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <th scope="row"><?= $num++; ?></th>
                                <td scope="row" class="btn-container">
                                        <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                        <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['id'] ?>"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                        <a class="btn btn-warning me-1" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                        <a class="btn btn-danger" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['buyer_username'] ?>','<?= $row3['nicename'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                        </td>
                                <td scope="row"><?= isset($row['buyer_username']) ? $row['buyer_username']  : '' ?></td>
                                <td scope="row"><?= isset($row2['name']) ? $row2['name'] : '' ?></td>
                                <td scope="row"><?= isset($row3['nicename']) ? $row3['nicename'] : '' ?></td>
                                <td scope="row"><?= isset($row4['name']) ? $row4['name'] : '' ?></td>
                                <td scope="row"><?= isset($row5['name']) ? $row5['name'] : '' ?></td>
                                <td scope="row"><?= isset($row['remark']) ? $row['remark'] : '' ?></td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col">S/N</th>
                        <th scope="col" id="action_col">Action</th>
                        <th scope="col">Shopee Buyer Username</th>
                        <th scope="col">Sales Person In Charge</th>
                        <th scope="col">Country</th>
                        <th scope="col">Brand</th>
                        <th scope="col">Series</th>
                        <th scope="col">Remark</th>
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
    datatableAlignment('shopee_cust_info_table');
    setButtonColor();
</script>

</html>