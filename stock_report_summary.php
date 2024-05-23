<?php
$pageTitle = "Stock Report";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = STK_REC;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/stock_list_table.php';
$deleteRedirectPage = $SITEURL . '/stock_list_table.php';

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
            <div class="col-12 col-md-11">

                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?> Summary</h2>
                            <div class="mt-auto mb-auto">
                            </div>
                        </div>
                    </div>
                </div>
            
                <table class="table table-striped" id="stock_report_table">
                    <thead>
                    <tr>
                  
                        
                        <th class="hideColumn" scope="col">ID</th>
                        <th class="text-center">
                            <input type="checkbox" class="exportAll">
                        </th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th scope="col">Stock Type</th>
                        <th scope="col">Brand</th>
                        <th scope="col">Product</th>
                        <th scope="col">Product Category</th>
                        <th scope="col">Platform</th>
                        <th scope="col">Warehouse</th>
                        <th scope="col">Stock In Person in Charges</th>
                        <th scope="col">Stock Out Person in Charges</th>
                       
                    </tr>

                    </thead>

                    <tbody>
                        <?php
                        $count = 0;
                        while ($row = $result->fetch_assoc()) {
                            if (isset($_GET['ids'])) {
                                $ids = explode(',', $_GET['ids']);
                                foreach ($ids as $id) {
                                    $decodedId = urldecode($id);
                           if (isset($row['id']) && !empty($row['id']&& $row['id'] == $id)) {
                            $count++;
                            $brand = isset($row['brand_id']) ? $row['brand_id'] : '';
                            $q1 = getData('name', "id='" . $brand . "'", '', BRAND, $connect);
                            $brd_fetch = $q1->fetch_assoc();
                            $brd_name = isset($brd_fetch['name']) ? $brd_fetch['name'] : '';

                            $product = isset($row['product_id']) ? $row['product_id'] : '';
                            $q2 = getData('name', "id='" . $product . "'", '', PROD, $connect);
                            $prod_fetch = $q2->fetch_assoc();
                            $prod_name = isset($prod_fetch['name']) ? $prod_fetch['name'] : '';

                            $product_status = isset($row['product_status_id']) ? $row['product_status_id'] : '';
                            $q3 = getData('name', "id='" . $product_status . "'", '', PROD_STATUS, $connect);
                            $prod_stat_fetch = $q3->fetch_assoc();
                            $prod_stat = isset($prod_stat_fetch['name']) ? $prod_stat_fetch['name'] : '';


                            $product_category = isset($row['product_category_id']) ? $row['product_category_id'] : '';
                            $q4 = getData('name', "id='" . $product_status . "'", '', PROD_CATEGORY, $connect);
                            $prod_cat_fetch = $q4->fetch_assoc();
                            $prod_cat = isset($prod_cat_fetch['name']) ? $prod_cat_fetch['name'] : '';

                            $platform_id = isset($row['platform_id']) ? $row['platform_id'] : '';
                            $q6 = getData('name', "id='" . $platform_id . "'", '', PLTF, $connect);
                            $plat_id_fetch = $q6->fetch_assoc();
                            $plat_name = isset($plat_id_fetch['name']) ? $plat_id_fetch['name'] : '';

                            $warehouse_id = isset($row['warehouse_id']) ? $row['warehouse_id'] : '';
                            $q7 = getData('name', "id='" . $warehouse_id . "'", '', WHSE, $connect);
                            $ware_id_fetch = $q7->fetch_assoc();
                            $ware_name = isset($ware_id_fetch['name']) ? $ware_id_fetch['name'] : '';

                            $stockInUsr = isset($row['stock_in_person_in_charges']) ? $row['stock_in_person_in_charges'] : '';
                            $q8 = getData('name', "id='" . $stockInUsr . "'", '', USR_USER, $connect);
                            $stockInUsr_fetch = $q8->fetch_assoc();
                            $stockInUsr_name = isset($stockInUsr_fetch['name']) ? $stockInUsr_fetch['name'] : '';

                            $stockOutUsr = isset($row['stock_out_person_in_charges']) ? $row['stock_out_person_in_charges'] : '';
                            $q9 = getData('name', "id='" . $stockOutUsr . "'", '', USR_USER, $connect);
                            $stockOutUsr_fetch = $q9->fetch_assoc();
                            $stockOutUsr_name = isset($stockOutUsr_fetch['name']) ? $stockOutUsr_fetch['name'] : '';
                            $stockType = empty($stockOutUsr_fetch) ? 'Stock In' : 'Stock Out';
                            $created = isset($row['create_by']) ? $row['create_by'] : '';
                            $q10 = getData('name', "id='" . $created . "'", '', USR_USER, $connect);
                            $updated = isset($row['update_by']) ? $row['update_by'] : '';
                            $q11 = getData('name', "id='" . $created . "'", '', USR_USER, $connect);
                            $created_fetch = $q10->fetch_assoc();
                            $updated_fetch = $q11->fetch_assoc();
                            $updated_name = isset($updated_fetch['name']) ? $updated_fetch['name'] : '';
                            $created_name = isset($created_fetch['name']) ? $created_fetch['name'] : '';

                            $createdate = $row['create_date'];
                            ?>
                            <tr onclick="window.location='stock_report_detail.php?ids=<?= urlencode($row['id']) ?>';" style="cursor:pointer;">
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <th class="text-center"><input type="checkbox" class="export" value="<?= $row['id'] ?>"></th>
                                <th scope="row"><?= $num++; ?></th>
                                <td scope="row"><?php if (isset($stockOutUsr_name)) { echo 'Stock Out'; } else { echo 'Stock In'; } ?></td>
                                <td scope="row"><?php if (isset($brd_name)) echo $brd_name ?></td>
                                <td scope="row"><?php if (isset($prod_name)) echo $prod_name ?></td>
                                <td scope="row"><?php if (isset($prod_cat)) echo $prod_cat ?></td>
                                <td scope="row"><?php if (isset($plat_name)) echo $plat_name ?></td>
                                <td scope="row"><?php if (isset($ware_name)) echo $ware_name ?></td>
                                <td scope="row"><?php if (isset($stockInUsr_name)) echo $stockInUsr_name ?></td>
                                <td scope="row"><?php if (isset($stockOutUsr_name)) { echo $stockOutUsr_name; } else { echo '-'; } ?></td>

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
                        <th class="text-center">
                            <input type="checkbox" class="exportAll">
                        </th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th scope="col">Stock Type</th>
                        <th scope="col">Brand</th>
                        <th scope="col">Product</th>
                        <th scope="col">Product Category</th>
                        <th scope="col">Platform</th>
                        <th scope="col">Warehouse</th>
                        <th scope="col">Stock In Person in Charges</th>
                        <th scope="col">Stock Out Person in Charges</th>
                       
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function ($) {
    $(document).on("change", ".exportAll", function (event) { //checkbox handling
        event.preventDefault();

        var isChecked = $(this).prop("checked");
        $(".export").prop("checked", isChecked);
        $(".exportAll").prop("checked", isChecked);

        updateCheckboxesOnOtherPages(isChecked);
    });

    $('a[name="exportBtn"]').on("click", function () {
        var checkboxValues = [];

        // Loop through all pages to collect checked checkboxes
        $('#stock_report').DataTable().$('tr', { "filter": "applied" }).each(function () {
            var checkbox = $(this).find('.export:checked');
            if (checkbox.length > 0) {
                checkbox.each(function () {
                    checkboxValues.push($(this).val());
                });
            }
        });

        if (checkboxValues.length > 0) {
            console.log('Checked row IDs:', checkboxValues);
            // Send checkboxValues to the server using AJAX
            setCookie('rowID', checkboxValues.join(','), 1);

            //uncheck checkboxes
            var checkboxes = document.querySelectorAll('.export');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            var selectAllCheckbox = document.querySelector('.exportAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }

            window.location.href = "stock_report.php";
        } else {
            console.log('No checkboxes are checked.');
        }
    });

    function updateCheckboxesOnOtherPages(isChecked) {
        // Get all cells in the DataTable
        var cells = $('#stock_report').DataTable().cells().nodes();

        // Check/uncheck all checkboxes in the DataTable
        $(cells).find('.export').prop('checked', isChecked);
    }
});

    <?php include "js/fb_ads_topup_table.js" ?>
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