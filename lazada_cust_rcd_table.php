<?php
$pageTitle = "Lazada Customer Record (Deals)";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/lazada_cust_rcd.php';
$deleteRedirectPage = $SITEURL . '/lazada_cust_rcd_table.php';
$result = getData('*', '', '', LAZADA_CUST_RCD, $connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('lzd_cust_deals');
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

        <table class="table table-striped" id="lzd_cust_deals">
            <thead>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col">S/N</th>
                    <th scope="col" id="action_col">Action</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Sales Person In Charge</th>
                    <th scope="col">Country</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Series</th>
                    <th scope="col">Shipping Receiver Name</th>
                    <th scope="col">Shipping Receiver Address</th>
                    <th scope="col">Shipping Receiver Contact</th>
                    <th scope="col">Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) {
                    $q1 = getData('name', "id='" . $row['sales_pic'] . "'", '', USR_USER, $connect);
                    $pic = $q1->fetch_assoc();

                    $q2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                    $country = $q2->fetch_assoc();

                    $q3 = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                    $brand = $q3->fetch_assoc();

                    $q4 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                    $series = $q4->fetch_assoc();

                    ?>

                    <tr>
                        <th class="hideColumn" scope="row">
                            <?= $row['id'] ?>
                        </th>
                        <th scope="row">
                            <?= $num++; ?>
                        </th>
                        <td scope="row" class="btn-container">
                        <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess); ?>
                        <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2); ?>
                        <?php renderDeleteButton($pinAccess, $row['id'], $row['name'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage); ?>
                        </td>

                        <td scope="row">
                            <?= $row['lcr_id'] ?>
                        </td>

                        <td scope="row">
                            <?= $row['name'] ?>
                        </td>

                        <td scope="row">
                            <?= $row['email'] ?>
                        </td>

                        <td scope="row">
                            <?= $row['phone'] ?>
                        </td>

                        <td scope="row"><?= isset($pic['name']) ? $pic['name'] : ''  ?></td>

                        <td scope="row">
                            <?= $country['nicename'] ?>
                        </td>

                        <td scope="row"><?= isset($brand['name']) ? $brand['name'] : ''  ?></td>

                        <td scope="row"><?= isset($series['name']) ? $series['name'] : ''  ?></td>

                        <td scope="row">
                            <?= $row['ship_rec_name'] ?>
                        </td>
                        <td scope="row">
                            <?= $row['ship_rec_add'] ?>
                        </td>
                        <td scope="row">
                            <?= $row['ship_rec_contact'] ?>
                        </td>
                        <td scope="row">
                            <?= $row['remark'] ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col">S/N</th>
                    <th scope="col" id="action_col">Action</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Sales Person In Charge</th>
                    <th scope="col">Country</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Series</th>
                    <th scope="col">Shipping Receiver Name</th>
                    <th scope="col">Shipping Receiver Address</th>
                    <th scope="col">Shipping Receiver Contact</th>
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
datatableAlignment('lzd_cust_deals');
</script>

</html>