<!-- courier AC
curr AC -->


<?php
$pageTitle = "Delivery Fees Claim Record Summary";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/del_fees_claim.php';
$result = getData('*', '', '', DEL_FEES_CLAIM, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('del_fees_claim_table');
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
                        <?php if ($result) { ?>
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
                <table class="table table-striped" id="del_fees_claim_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Courier</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                        if (isset($_GET['ids'])) {
                             $ids = explode(',', $_GET['ids']);
                            foreach ($ids as $id) {
                            $decodedId = urldecode($id);
                            if (isset($row['id']) && !empty($row['id']&& $row['id'] == $id)) {

                                $curr = getData('unit', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                                $row2 = $curr->fetch_assoc();

                                $courier = getData('name', "id='" . $row['courier'] . "'", '', COURIER, $connect);
                                $row3 = $courier->fetch_assoc();

                        ?>

                                <tr onclick="window.location='del_fees_claim_table_detail.php?ids=<?= urlencode($row['id']) ?>';" style="cursor:pointer;">
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <td scope="row"><?= $num++ ?></td>
                                    <td scope="row"><?php if (isset($row3['name'])) echo $row3['name'] ?></td>
                                    <td scope="row"><?php if (isset($row2['unit'])) echo $row2['unit'] ?></td>
                                    <td scope="row"><?php if (isset($row['total'])) echo  $row['total'] ?></td>
                                 
                                </tr>
                        <?php }
                                }
                            }
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Courier</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Total</th>
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
    datatableAlignment('del_fees_claim_table');
</script>

</html>