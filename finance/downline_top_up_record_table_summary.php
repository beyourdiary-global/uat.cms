<?php
$pageTitle = "Downline Top Up Record Summary";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/downline_top_up_record.php';
$deleteRedirectPage = $SITEURL . '/finance/downline_top_up_record_table.php';
$result = getData('*', '', '', DW_TOP_UP_RECORD, $finance_connect);

?>

<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="../css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('table');
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
                            <th scope="col">Agent</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Total Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            if (isset($_GET['ids'])) {
                                $ids = explode(',', $_GET['ids']);
                                foreach ($ids as $id) {
                                $decodedId = urldecode($id);  
                                if (isset($row['id']) && !empty($row['id']&& $row['id'] == $id)) {

                                $agent = getData('name', "id='" . $row['agent'] . "'", '', AGENT, $finance_connect);
                                $row3 = $agent->fetch_assoc();

                                $brand = getData('name', "id='" . $row['brand'] . "'", 'LIMIT 1', BRAND, $connect);
                                $row4 = $brand->fetch_assoc();

                                $currResult = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);
                            $currRow = $currResult->fetch_assoc();
                        ?>
                                <tr onclick="window.location='downline_top_up_record_table_detail.php?ids=<?= urlencode($row['id']) ?>';" style="cursor:pointer;">
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <td scope="row"><?= $num++ ?></td>
                                    <td scope="row"><?php if (isset($row3['name'])) echo  $row3['name'] ?></td>
                                    <td scope="row"><?php if (isset($row4['name'])) echo  $row4['name'] ?></td>
                                    <td scope="row"><?php if (isset($currRow['unit'])) echo $currRow['unit'] ?></td>
                                    <td scope="row"><?php if (isset($row['amount'])) echo  $row['amount'] ?></td>
                                </tr>
                        <?php
                            }
                        }
                    }
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">Agent</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Total Amount</th>
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