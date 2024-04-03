<?php
$pageTitle = "Cash On Hand Transaction";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/cash_on_hand_trans.php';
$result = getData('*', '', '', CAONHD, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('cash_on_hand_trans_table');
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
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        Transaction </a>
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

                <table class="table table-striped" id="cash_on_hand_trans_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Transaction ID</th>
                            <th scope="col">Type</th>
                            <th scope="col">PIC</th>
                            <th scope="col">Date</th>
                            <th scope="col">Bank</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Previous Amount Record</th>
                            <th scope="col">Final Amount Record</th>
                            <th scope="col">Description</th>
                            <th scope="col">Remark</th>
                            <th scope="col">Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            if (isset($row['transactionID'], $row['id']) && !empty($row['transactionID'])) {

                                $curr = getData('unit', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                                $row2 = $curr->fetch_assoc();

                                $bank = getData('name', "id='" . $row['bank'] . "'", '', BANK, $connect);
                                $row3 = $bank->fetch_assoc();

                                $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                                $usr = $pic->fetch_assoc();
                        ?>
                                <tr>
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++; ?></th>
                                    <td scope="row" class="btn-container">
                                    <div class="d-flex align-items-center">
                                    <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess);?>
                                    <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2) ?>
                                    <?php renderDeleteButton($pinAccess, $row['id'], $row['transactionID'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage) ?>
                                    </div>
                                    </td>
                                    <td scope="row"><?= $row['transactionID'] ?></td>
                                    <td scope="row"><?php if (isset($row['type'])) echo $row['type'] ?></td>
                                    <td scope="row"><?php if (isset($usr['name'])) echo $usr['name'] ?></td>
                                    <td scope="row"><?php if (isset($row['date'])) echo $row['date'] ?></td>
                                    <td scope="row"><?php if (isset($row3['name'])) echo $row3['name'] ?></td>
                                    <td scope="row"><?php if (isset($row2['unit'])) echo $row2['unit'] ?></td>
                                    <td scope="row"><?php if (isset($row['amount'])) echo $row['amount'] ?></td>
                                    <td scope="row"><?php if (isset($row['prev_amt'])) echo $row['prev_amt'] ?></td>
                                    <td scope="row"><?php if (isset($row['final_amt'])) echo $row['final_amt'] ?></td>
                                    <td scope="row"><?php if (isset($row['description'])) echo $row['description'] ?></td>
                                    <td scope="row"><?php if (isset($row['remark'])) echo $row['remark'] ?></td>
                                    <td scope="row"><?php if (isset($row['attachment'])) echo $row['attachment'] ?></td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Transaction ID</th>
                            <th scope="col">Type</th>
                            <th scope="col">PIC</th>
                            <th scope="col">Date</th>
                            <th scope="col">Bank</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Previous Amount Record</th>
                            <th scope="col">Final Amount Record</th>
                            <th scope="col">Description</th>
                            <th scope="col">Remark</th>
                            <th scope="col">Attachment</th>
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
    datatableAlignment('cash_on_hand_trans_table');
</script>

</html>