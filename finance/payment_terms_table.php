<?php
$pageTitle = "Payment Terms";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = FIN_PAY_TERMS;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/payment_terms.php';

$result = getData('*', '', '', $tblName, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
preloader(300);

$(document).ready(() => {
    createSortingTable('payment_terms_table');
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
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                                class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                            <?php if ($result) { ?>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                    href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                    <?php echo $pageTitle ?> </a>
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

                <table class="table table-striped" id="payment_terms_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['name'])) { ?>
                        <tr>
                            <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                            <th scope="row"><?= $num++; ?></th>
                            <td scope="row"><?= $row['name'] ?></td>
                            <td scope="row"><?= $row['description'] ?></td>
                            <td scope="row"><?= $row['remark'] ?></td>
                            <td scope="row">
                                <div class="dropdown" style="text-align:center">
                                    <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#"
                                        id="actionDropdownMenu" role="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg"
                                                id="action_menu"></i></button>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                        <li>
                                            <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                            <a class="dropdown-item"
                                                href="<?= $redirect_page . "?id=" . $row['id'] ?>">View</a>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                            <a class="dropdown-item"
                                                href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                            <a class="dropdown-item"
                                                onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>','<?= $row['description'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/payment_terms_table.php','D')">Delete</a>
                                            <?php endif; ?>
                                        </li>
                                    </ul>
                                </div>
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
                            <th scope="col">Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </tfoot>
                </table>
                <?php } ?>
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
    datatableAlignment('payment_terms_table');
    setButtonColor();
    </script>
</body>

</html>