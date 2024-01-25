<?php
$pageTitle = "Monthly Bank Transaction Backup Record";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/bank_trans_backup.php';
$result = getData('*', '', '', BANK_TRANS_BACKUP, $finance_connect);

$img_path = SITEURL . img_server . 'finance/bank_trans_backup/';
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('bank_trans_backup_table');
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
                        <?php
                        if ($result) {
                        ?>
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Transaction </a>
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

            <table class="table table-striped" id="bank_trans_backup_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col">S/N</th>
                        <th scope="col">Year</th>
                        <th scope="col">Month</th>
                        <th scope="col">Attachment</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) {
                        $numericMonth = $row['month'];
                        $fullMonthName = date('F', mktime(0, 0, 0, $numericMonth, 1));
                    ?>

                        <tr>
                            <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                            <th scope="row"><?= $num++; ?></th>
                            <td scope="row"><?= $row['year'] ?></td>
                            <td scope="row"><?= $fullMonthName ?></td>
                            <td scope="row"><a href="<?= $img_path . $row['attachment'] ?>" target="_blank"><?= $row['attachment'] ?></a></td>
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
                                                <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['year'] ?>','<?= $row['month'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/cash_on_hand_trans_table.php','D')">Delete</a>
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
                        <th scope="col">S/N</th>
                        <th scope="col">Year</th>
                        <th scope="col">Month</th>
                        <th scope="col">Attachment</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
            <?php } ?>
        </div>

    </div>

</body>
<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
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
    datatableAlignment('bank_trans_backup_table');
</script>

</html>