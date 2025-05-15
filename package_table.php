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

// Ensure the query was successful
if (!$result) {
    $result = [];
} elseif ($result->num_rows == 0) {
    $result = [];
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
            <div class="col-12 col-md-11">
                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> 
                            <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                       href="<?= $redirect_page . "?act=" . $act_1 ?>">
                                        <i class="fa-solid fa-plus"></i> Add <?php echo $pageTitle ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($result)) { ?>
                    <div class="text-center"><h4>No Result!</h4></div>
                <?php } else { ?>
                    <table class="table table-striped" id="table">
                        <thead>
                            <tr>
                                <th class="hideColumn">ID</th>
                                <th>S/N</th>
                                <th id="action_col">Action</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Brand</th>
                                <th>Cost</th>
                                <th>Agent Cost</th>
                                <th>Product Quantity</th>
                                <th>Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                if (empty($row['name']) || empty($row['id'])) {
                                    continue; // Skip invalid rows
                                }
                                ?>
                                <tr>
                                    <th class="hideColumn"><?= $row['id'] ?></th>
                                    <th><?= $num++ ?></th>
                                    <td class="btn-container">
                                        <?php if (isActionAllowed("View", $pinAccess)): ?>
                                            <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Edit", $pinAccess)): ?>
                                            <a class="btn btn-warning me-1" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Delete", $pinAccess)): ?>
                                            <a class="btn btn-danger"
                                               onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>','<?= $row['remark'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td>
                                        <?php
                                        $resultCurUnit = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);
                                        $rowCurUnit = ($resultCurUnit && $resultCurUnit->num_rows > 0) ? $resultCurUnit->fetch_assoc() : null;
                                        echo $rowCurUnit ? $rowCurUnit['unit'] . ' ' . $row['price'] : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $resultBrand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                                        $rowBrand = ($resultBrand && $resultBrand->num_rows > 0) ? $resultBrand->fetch_assoc() : null;
                                        echo $rowBrand ? $rowBrand['name'] : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $resultCurUnit2 = getData('unit', "id='" . $row['cost_curr'] . "'", '', CUR_UNIT, $connect);
                                        $rowCurUnit2 = ($resultCurUnit2 && $resultCurUnit2->num_rows > 0) ? $resultCurUnit2->fetch_assoc() : null;
                                        echo $rowCurUnit2 ? $rowCurUnit2['unit'] . ' ' . $row['cost'] : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo $row['agent_cost']?'RM'. $row['agent_cost']:'';
                                        ?>
                                    </td>
                                    <td>
                                        <?= isset($row['product']) ? count(explode(",", $row['product'])) : '0' ?>
                                    </td>
                                    <td width="25%">
                                        <?php
                                        echo $row['remark']?'RM'. $row['remark']:'';
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <th class="hideColumn">ID</th>
                                <th>S/N</th>
                                <th id="action_col">Action</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Brand</th>
                                <th>Cost</th>
                                <th>Agent Cost</th>
                                <th>Product Quantity</th>
                                <th>Remark</th>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            </div>
        </div>
    </div>

    <script>
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        checkCurrentPage(page, action);
        dropdownMenuDispFix();
        datatableAlignment('table');
        setButtonColor();
    </script>
</body>
</html>
