<?php
$pageTitle = "Internal Consume Item";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/internal_consume_item.php';
$result = getData('*', '', '', ITL_CSM_ITEM, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('internal_consume_item_table');
    });
</script>

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
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Item </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="curr_bank_trans_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                            <th scope="col">Cost</th> 
                            <th scope="col">Remark</th> 
                            <th scope="col" id="action_col" width="100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $usr = $pic->fetch_assoc();

                            $brand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $row2 = $brand->fetch_assoc();

                            $package = getData('name', "id='" . $row['package'] . "'", '', PKG, $connect);
                            $row3 = $package->fetch_assoc();
                        ?>

                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <td scope="row"><?= $row['date'] ?></td>
                                <td scope="row"><?= $usr['pic'] ?></td>
                                <td scope="row"><?= $row2['brand'] ?></td>
                                <td scope="row"><?= $row3['package'] ?></td>
                                <td scope="row"><?= $row['cost'] ?></td>
                                <td scope="row"><?= $row['remark'] ?></td>
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
                                                    <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['transactionID'] ?>','<?= $row['remark'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/internal_consume_item_table.php','D')">Delete</a>
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
                            <th scope="col">Date</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                            <th scope="col">Cost</th> 
                            <th scope="col">Remark</th> 
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
    /* function(void) : to solve the issue of dropdown menu displaying inside the table when table class include table-responsive */
    dropdownMenuDispFix();
    /* function(id): to resize table with bootstrap 5 classes */
    datatableAlignment('internal_consume_item_table');
    setButtonColor();
</script>

</html>