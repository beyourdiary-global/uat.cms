<?php
$pageTitle = "Package";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = PKG;
$pinAccess = checkCurrentPin($connect, $pageTitle);


$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/package.php';
$deleteRedirectPage = $SITEURL . '/package_table.php';

$result = getData('*', '', '', $tblName, $connect);

if (!$result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('table');
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
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add <?php echo $pageTitle ?> </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Cost</th>
                            <th scope="col">Product Quantity</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            if (isset($row['name'], $row['id']) && !empty($row['name'])) { ?>
                                <tr>
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++ ?></th>
                                    <td scope="row" class="btn-container">
                                        <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                        <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['id'] ?>"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                        <a class="btn btn-warning me-1" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                        <a class="btn btn-danger" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>','<?= $row['remark'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                        </td>
                                    <td scope="row"><?= $row['name'] ?></td>
                                    <td scope="row">
                                        <?php
                                        $resultCurUnit = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);

                                        if (!$resultCurUnit) {
                                            echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                            echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                        }
                                        $rowCurUnit = $resultCurUnit->fetch_assoc();

                                        echo $rowCurUnit['unit'] . ' ' . $row['price'];
                                        ?>
                                    </td>
                                    <td scope="row">
                                        <?php
                                        if (!empty($row['brand']) && isset($row['brand'])) {

                                            $resultBrand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);

                                            if (!$resultBrand) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }
                                            $rowBrand = $resultBrand->fetch_assoc();

                                            echo $rowBrand['name'];
                                        }
                                        ?>
                                    </td>
                                    <td scope="row">
                                        <?php
                                        if (!empty($row['cost_curr']) && isset($row['cost_curr'])) {
                                            $resultCurUnit2 = getData('unit', "id='" . $row['cost_curr'] . "'", '', CUR_UNIT, $connect);

                                            if (!$resultCurUnit2) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }
                                            $rowCurUnit2 = $resultCurUnit2->fetch_assoc();

                                            echo $rowCurUnit2['unit'] . ' ' . $row['cost'];
                                        }
                                        ?>
                                    </td>
                                    <td scope="row">
                                        <?php
                                        if (isset($row['product'])) {
                                            $prod_list = explode(",", $row['product']);
                                            echo sizeOf($prod_list);
                                        }
                                        ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Cost</th>
                            <th scope="col">Product Quantity</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        //Initial Page And Action Value
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