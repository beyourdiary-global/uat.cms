<?php
$pageTitle = "Internal Consume Item Summary";
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
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Item </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="internal_consume_item_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                            <th scope="col">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            if (isset($_GET['ids'])) {
                                $ids = explode(',', $_GET['ids']);
                               foreach ($ids as $id) {
                                $decodedId = urldecode($id);
                            if (isset($row['id']) && !empty($row['id'])&& $row['id'] == $id) {

                                $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                                $usr = $pic->fetch_assoc();

                                $brand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                                $row2 = $brand->fetch_assoc();

                                $package = getData('*', "id='" . $row['package'] . "'", '', PKG, $connect);
                                $row3 = $package->fetch_assoc();
                        ?>

                                <tr onclick="window.location='internal_consume_item_table_detail.php?ids=<?= urlencode($row['id']) ?>';" style="cursor:pointer;">
                        
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++; ?></th>
                                    <td scope="row"><?php if (isset($usr['name'])) echo  $usr['name'] ?></td>
                                    <td scope="row"><?php if (isset($row2['name'])) echo  $row2['name'] ?></td>
                                    <td scope="row"><?php if (isset($row3['name'])) echo  $row3['name'] ?></td>
                                    <td scope="row"><?php if (isset($row['cost'])) echo  $row['cost'] ?></td>

                                </tr>
                                <?php }
                                }
                            }
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                            <th scope="col">Total Cost</th>
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