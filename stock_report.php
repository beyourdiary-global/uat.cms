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
                            <h2><?php echo $pageTitle ?></h2>
                            <div class="mt-auto mb-auto">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 dateFilters">
                        <label for="timeInterval" class="form-label">Filter by:</label>
                       <select class="form-select" id="timeInterval" >

                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 dateFilters">
                        <label for="dateFilter" class="form-label">Filter by Date:</label>
                        <div class="input-group date" id="datepicker"> 
                        <input type="text" class="form-control" placeholder="Select date" autocomplete="off">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        <div class="input-daterange input-group" id="datepicker2" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start date" autocomplete="off"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End date" autocomplete="off"/>
                        </div>
                        <div class="input-group input-daterange" id="datepicker3" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start month" autocomplete="off"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End month" autocomplete="off"/>
                            
                            </div>
                        <div class="input-group input-daterange" id="datepicker4" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start year" autocomplete="off"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End year" autocomplete="off"/>
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Group by:</label>
                        <select class="form-select" id="group" placeholder="Select a Group">
                            <option value="" >Select a Group</option>
                            <option value="stock_type">Stock Type</option>
                            <option value="brand">Brand</option>
                            <option value="product">Product</option>
                            <option value="whse">Warehouse</option>
                            <option value="pdtcategory">Product Category</option>
                            <option value="platform">Platform</option>
                            <option value="stockinpic">Stock In Person In Charge</option>
                            <option value="stockoutpic">Stock Out Person In Charge</option>
                            
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <a id='resetButton' href="../reset.php?redirect=finance/shopee_ads_topup_trans_table.php" class="btn btn-sm btn-rounded btn-primary"> <i class="fa fa-refresh"></i> Reset </a>
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
                        <th id="group_header" scope="col">

                        <?php
                            if (isset($_GET['group'])) {
                            $groupLabels = [
                                'stock_type' => 'Stock Type',
                                'brand' => 'Brand',
                                'product' => 'Product',
                                'whse' => 'Warehouse',
                                'pdtcategory' => 'Product Category',
                                'platform' => 'Platform',
                                'stockinpic' => 'Stock In Person In Charge',
                                'stockoutpic' => 'Stock Out Person In Charge',
                            ];

                            if (isset($_GET['group']) && array_key_exists($_GET['group'], $groupLabels)) {
                                echo $groupLabels[$_GET['group']];
                            }
                            }
                        ?>
                        </th>
                        <th scope="col">Total record count</th>
                       
                    </tr>

                    </thead>

                    <tbody>
                        <?php
                        $groupOption = isset($_GET['group']) ? $_GET['group'] : ''; 
                        $groupOption3 = isset($_GET['timeRange']) ? $_GET['timeRange'] : ''; 
                        $groupOption4 = isset($_GET['timeInterval']) ? $_GET['timeInterval'] : ''; 
                        $groupedRows = [];
                        $counters = 1;
                        $groupedRows = [];
                        $rowCount = 1;
                        while ($row = $result->fetch_assoc()) {
                     
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
                            $stockOutUsr_name = isset($stockOutUsr_fetch['name']) ? $stockOutUsr_fetch['name'] : '-';
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
                           
                            if ($groupOption && $groupOption3) {
                                switch ($groupOption) {
                                    case 'stock_type':
                                        $key = $stockType;
                                        break;
                                    case 'brand':
                                        $key = $brd_name;
                                        break;
                                    case 'product':
                                        $key = $prod_name;
                                        break;
                                    case 'whse':
                                        $key = $ware_name;
                                        break;
                                    case 'pdtcategory':
                                        $key = $prod_cat;
                                        break;
                                    case 'platform':
                                        $key = $plat_name;
                                        break;
                                    case 'stockinpic':
                                        $key = $stockInUsr_name;
                                        break;
                                    case 'stockoutpic':
                                        $key = $stockOutUsr_name;
                                        break;
                                    default:
                                        $key = $brd_name;
                                        break;
                                }
                                  if (($groupOption === 'stock_type' || $groupOption === 'brand' || $groupOption === 'product' || $groupOption === 'whse' || $groupOption === 'pdtcategory' || $groupOption === 'platform' || $groupOption === 'stockinpic' || $groupOption === 'stockoutpic') && $groupOption4 === 'daily') {
                                  
                            
                                    if ($groupOption3 === $createdate) {
                                    if (!isset($groupedRows[$key])) {
                                        $groupedRows[$key] = [
                                            'ids' => [$row['id']],
                                            'count' => 1
                                        ];
                                    } else {
                                        $groupedRows[$key]['ids'][] = $row['id'];
                                        $groupedRows[$key]['count'] += 1;
                                    }
                                }
                                }
                              
                              else if (($groupOption === 'brand' ||
                              $groupOption === 'stock_type' || 
                              $groupOption === 'product' || 
                              $groupOption === 'whse' || 
                              $groupOption === 'pdtcategory' || 
                              $groupOption === 'platform' || 
                              $groupOption === 'stockinpic' || 
                              $groupOption === 'stockoutpic') &&  $groupOption4 != 'daily') {
                                  $dateRange = explode('to', $groupOption3);
                                  if($groupOption4 == 'weekly'){
                                      $startDate = strtotime(trim($dateRange[0]));
                                      $endDate = strtotime(trim($dateRange[1]));
                                  }else if ($groupOption4 == 'monthly'){
                                      $startDate = strtotime(trim($dateRange[0]));
                                      $endDate = strtotime('last day of ' . trim($dateRange[1]));
                                  }else if ($groupOption4 == 'yearly'){
                                      $startDate = strtotime('first day of January ' . trim($dateRange[0]));
                                      $endDate = strtotime('last day of December ' . trim($dateRange[1]));
                                  }
  
                                 
                                  $createdTimestamp = strtotime($createdate);
                              
                                  if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                   
                              
                                    if (!isset($groupedRows[$key])) {
                                        $groupedRows[$key] = [
                                            'ids' => [$row['id']],
                                            'count' => 1
                                        ];
                                    } else {
                                        $groupedRows[$key]['ids'][] = $row['id'];
                                        $groupedRows[$key]['count'] += 1; 
                                    }
                                  }
                              }
                          }}
                       
                          foreach ($groupedRows as $key => $groupedRow) {

                            if (isset($key)) {
                                if($groupOption4 == 'daily') {
                                   
                                    $nextDay = date('Y-m-d', strtotime($createdate . ' +1 day'));
                                    if (!isset($groupedRow['displayed'])) {
                                        $groupedRow['displayed'] = true;
                                        $viewActMsg = USER_NAME . " searched the data [<b> ID = " . implode(', ', $groupedRow['ids']) . "</b> ] with the date <b>" . $nextDay. "</b> from <b><i>$tblName Table</i></b>.";
                                        $idss = implode(', ', $groupedRow['ids']);
                                        $sql = "SELECT * FROM $tblName WHERE id IN ($idss)";
                                    } else {
                                        $viewActMsg = '';
                                        $sql = '';
                                    }
                                }else{
                                    if (!isset($groupedRow['displayed'])) {
                                        $groupedRow['displayed'] = true;
                                        
                                        $idss = is_array($groupedRow['ids']) ? implode(', ', $groupedRow['ids']) : $groupedRow['ids'];
                                        
                                        $viewActMsg = USER_NAME . " searched the data [ <b>ID = " . $idss . " </b>] for the period between <b> " . date('Y-m-d', ($startDate)) . " </b> and <b>" . date('Y-m-d', ($endDate)) . "</b> from <b><i>" . $tblName . "Table</i></b> .";
                                        $sql = "SELECT * FROM $tblName WHERE id IN ($idss)";
                                    
                                    } else {
                                        $viewActMsg = '';
                                        $sql = '';
                                    }
                                }
                                $log = [
                                    'log_act' => 'search',
                                    'cdate'   => $cdate,
                                    'ctime'   => $ctime,
                                    'uid'     => USER_ID,
                                    'cby'     => USER_ID,
                                    'query_rec'    => $sql,
                                    'query_table'  => $tblName,
                                    'act_msg' => $viewActMsg,
                                    'page'    => $pageTitle,
                                    'connect' => $connect,
                                ];
                                audit_log($log);
                            $ids = implode(',', $groupedRow['ids']);
                     

                            $url = $groupOption4 == 'daily' ? "stock_report_detail.php?ids=" . urlencode($ids) : "stock_report_summary.php?ids=" . urlencode($ids);
                            echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                            echo '<th class="hideColumn" scope="row">' . $ids . '</th>'; 
                            echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $ids . '"></th>';
                            echo '<th scope="row">' . $counters++ . '</th>';
                            echo '<td scope="row">' . $key . '</td>';
                            echo '<td scope="row">' . ($groupedRow['count']) . '</td>';
                            echo '</tr>';
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
                  <th id="group_header" scope="col">

                  <?php
                      if (isset($_GET['group'])) {
                      $groupLabels = [
                          'stock_type' => 'Stock Type',
                          'brand' => 'Brand',
                          'product' => 'Product',
                          'whse' => 'Warehouse',
                          'pdtcategory' => 'Product Category',
                          'platform' => 'Platform',
                          'stockinpic' => 'Stock In Person In Charge',
                          'stockoutpic' => 'Stock Out Person In Charge',
                      ];

                      if (isset($_GET['group']) && array_key_exists($_GET['group'], $groupLabels)) {
                          echo $groupLabels[$_GET['group']];
                      }
                      }
                  ?>
                  </th>
                  <th scope="col">Total record count</th>
                 
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