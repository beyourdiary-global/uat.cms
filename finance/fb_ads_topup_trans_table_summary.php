<?php
$pageTitle = "Facebook Ads Top Up Transaction Summary";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/fb_ads_topup_trans.php';
$result = getData('*', '', '', FB_ADS_TOPUP, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('fb_ads_topup_trans_table');
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
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Transaction </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="fb_ads_topup_trans_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th scope="col">Meta Account</th>
                        <th scope="col">Invoice/Payment Date</th>
                        <th scope="col">Total Top-up Amount</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) {
                      if (isset($_GET['ids'])) {
                        $ids = explode(',', $_GET['ids']);
                        foreach ($ids as $id) {
                            $decodedId = urldecode($id);
                            if (isset($row['transactionID'], $row['id']) && !empty($row['transactionID']) && $row['id'] == $id) {
                            $metaQuery = getData('*', "id='" . $row['meta_acc'] . "'", '', META_ADS_ACC, $finance_connect);
                            $meta_acc = $metaQuery->fetch_assoc();
                            $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $usr = $pic->fetch_assoc();
                    ?>
                   
                   <tr onclick="window.location='fb_ads_topup_trans_table_detail.php?ids=<?= urlencode($row['id']) ?>';" style="cursor:pointer;">

                        <td class="hideColumn" scope="row"><?= $row['id'] ?></td>
                        <td scope="row"><?= $num++; ?></td>
                        <td scope="row"><?php if (isset($meta_acc['accName'])) echo $meta_acc['accName'] ?></td>
                        <td scope="row"><?php if (isset($row['payment_date'])) echo $row['payment_date'] ?></td>
                        <td scope="row"><?php if (isset($row['topup_amt'])) echo $row['topup_amt'] ?></td>
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
                        <th scope="col">Meta Account</th>
                        <th scope="col">Invoice/Payment Date</th>
                        <th scope="col">Total Top-up Amount</th>
                        
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
    datatableAlignment('fb_ads_topup_trans_table');
</script>

</html>