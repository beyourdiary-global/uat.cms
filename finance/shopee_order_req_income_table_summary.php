<?php
ob_start();
$pageTitle = "Shopee SG Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

require_once '../header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . "_list.xlsx";
$img_path = '../' . img_server . 'finance/shopee_order_req/';


$tempDir = '../' . img_server . "temp/";
$tempAttachDir = $tempDir . "attachment/";
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}
if (!file_exists($tempAttachDir)) {
    mkdir($tempAttachDir, 0777, true);
}

$checkboxValues = isset($_COOKIE['rowID']) ? $_COOKIE['rowID'] : '';

// Check if any checkboxes are checked
if (!empty($checkboxValues)) {
    setcookie('rowID', '', time() - 3600, '/');
    // Defining column names
    $excelData = array(
        array('S/N',
        'SHOPEE ACCOUNT',
    'CURRENCY',
    'ORDER ID',
    'DATE',
    'TIME',
    'PACKAGE',
    'BARCODE SLOT',
    'BRAND',
    'BUYER',
    'BUYER PAYMENT METHOD',
    'PIC',
    'PRICE',
    'VOUCHER',
    'SHIPPING FEE',
    'SERVICE FEE',
    'TRANSACTION FEE',
    'AMS FEE',
    'FEES',
    'FINAL AMT',
    'REMARK',
    'CREATE BY',
    'CREATE DATE',
    'CREATE TIME',
    'UPDATE BY',
    'UPDATE DATE',
    'UPDATE TIME',
    'ORDER STATUS')
    );    // Get the data from the database using the WHERE clause
    $query2 = $finance_connect->query("SELECT * FROM " . FB_ORDER_REQ . " WHERE status = 'A' AND id IN ($checkboxValues) ORDER BY create_date ASC, sales_pic ASC, brand ASC, series ASC, package ASC, price ASC");
   
    $excelRowNum = 1;
    if ($query2->num_rows > 0) {
        while ($row2 = $query2->fetch_assoc()) {
            // Initialize an empty array to store the row data
            $lineData = array();
            $lineData[] = $excelRowNum;

            if (isset($row2['attachment']) && !empty($row2['attachment'])) {
                $attachmentSourcePath = $img_path . $row2['attachment'];
                if (file_exists($attachmentSourcePath)) {
                    $attachmentCreationDate = strtotime($row2['create_date']);
                    $yearMonthFolder = $tempAttachDir . date('Y', $attachmentCreationDate) . '/' . date('m', $attachmentCreationDate) . '/';
                    if (!file_exists($yearMonthFolder)) {
                        mkdir($yearMonthFolder, 0777, true);
                    }
                    $attachmentDestPath = $yearMonthFolder . $row2['attachment'];
                    copy($attachmentSourcePath, $attachmentDestPath);
                }
            }


            // Define the column names in the same order as in your database query
            $columnNames = array(
                'shopee_acc',
                'currency',
                'orderID',
                'date',
                'time',
                'package',
                'barcode_slot',
                'brand',
                'buyer',
                'buyer_pay_meth',
                'pic',
                'price',
                'voucher',
                'act_shipping_fee',
                'service_fee',
                'trans_fee',
                'ams_fee',
                'fees',
                'final_amt',
                'remark',
                'create_by',
                'create_date',
                'create_time',
                'update_by',
                'update_date',
                'update_time',
                'order_status'
            );

            foreach ($columnNames as $columnName) {
                // Check if the value is null, if so, replace it with an empty string
                if ($columnName === 'create_by' || $columnName === 'update_by') {
                    $name = '';
                    $pic = getData('name', "id='" . $row2[$columnName] . "'", '', USR_USER, $connect);
                    if ($pic && $pic->num_rows > 0) {
                        $user = $pic->fetch_assoc();
                        $name = $user['name'];
                    }
                    $lineData[] = $name;
                } elseif ($columnName === 'create_date') {
                    // Modify create_date value as needed
                    $lineData[] = isset($row2[$columnName]) ? $row2[$columnName] : '';
                } else {
                    $lineData[] = isset($row2[$columnName]) ? $row2[$columnName] : '';
                }
            }
            $excelData[] = $lineData;
            $excelRowNum++;
        }
        $xlsx = CodexWorld\PhpXlsxGenerator::fromArray($excelData);
        // $xlsx->downloadAs($fileName);

        $tempExcelFilePath = $tempDir . $fileName;

        if ($tempExcelFilePath) {
            $xlsx->saveAs($tempExcelFilePath);
            $zipFile = date('Ymd_His') . ".zip";
            $zip = new ZipArchive();

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                die("Failed to create zip file");
            }

            // Add the Excel file to the root of the zip archive
            $zip->addFile($tempExcelFilePath, basename($tempExcelFilePath));

            // Add the 'attachment' folder to the zip archive
            addDirToZip($tempAttachDir, $zip, $tempAttachDir);

            // Close the zip archive
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' .$zipFile .'"');
            header('Content-Length: ' . filesize($zipFile));
            header('Pragma: no-cache');
            header('Expires: 0');
            ob_clean();
            readfile($zipFile);
            deleteDir($tempDir);
            

        }

    } else {
        echo 'Failed to create temporary Excel file';
    }
}

function addDirToZip($dir, $zip, $basePath)
{
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $filePath = $dir . $file;
        if (is_file($filePath)) {
            // Add the file to the zip archive with a relative path
            $relativePath = str_replace($basePath, '', $filePath);
            $zip->addFile($filePath, $relativePath);
        } elseif (is_dir($filePath)) {
            // Add the directory to the zip archive
            $zip->addEmptyDir(str_replace($basePath, '', $filePath));
            // Recursively add files and directories inside the current directory
            addDirToZip($filePath . '/', $zip, $basePath);
        }
    }
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) {
        return;
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['searchChk'] = '';
unset($_SESSION['resetChk']);
$_SESSION['delChk'] = '';
$num = 1;   // numbering
$tblName = SHOPEE_SG_ORDER_REQ;

$redirect_page = $SITEURL . '/finance/shopee_order_req.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_order_req_table.php';
$result = getData('*', '', '', SHOPEE_SG_ORDER_REQ, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('shopee_order_req_table');
    });
</script>



<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-11">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                            class="fa-solid fa-chevron-right fa-xs"></i>
                        <?php echo $pageTitle ?> Summary
                    </p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>
                            <?php echo $pageTitle ?> Summary
                        </h2>
                        <?php if ($result) { ?>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        Request </a>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="exportBtn" id="addBtn" onclick="captureAndExport('<?php echo $tblName; ?>')"><i class="fa-solid fa-file-export"></i> Export</a>
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
                <div class="row mb-3">
                    <div class="col-md-3 dateFilters" id='dateFilters'>
                        <label for="timeInterval" class="form-label">Filter by:</label>
                       <select class="form-select" id="timeInterval" disabled>

                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 dateFilters" id='dateFilters'>
                        <label for="dateFilter" class="form-label">Filter by Date:</label>
                        <div class="input-group date" id="datepicker" style="background-color: #f0f0f0;"> 
                        <input type="text" class="form-control" placeholder="Select date" autocomplete="off"disabled>
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        <div class="input-daterange input-group" id="datepicker2" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start date" autocomplete="off" disabled/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End date" autocomplete="off" disabled/>
                        </div>
                        <div class="input-group input-daterange" id="datepicker3" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start month" autocomplete="off" disabled/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End month" autocomplete="off" disabled/>
                            
                            </div>
                        <div class="input-group input-daterange" id="datepicker4" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start year" autocomplete="off" disabled/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End year" autocomplete="off" disabled/>
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                    <label class="form-label">Group by:</label>
                        <select class="form-select" id="group" style="display: none;">
                         <option value="brand"selected>Brand</option>
                            <option value="status" >Order Status</option>
                            <option value="shopee_acc">Shopee Account</option>
                            <option value="currency">Currency</option>
                            <option value="package">Package</option>
                            <option value="buyer">Shopee Buyer Username</option>
                            <option value="person">Person In Charge</option>
                        </select>
                        <select class="form-select" id="group2">
                        <option  selected>Select a Group</option>
                         <option value="brand">Brand</option>
                            <option value="status" >Order Status</option>
                            <option value="shopee_acc">Shopee Account</option>
                            <option value="currency">Currency</option>
                            <option value="package">Package</option>
                            <option value="buyer">Shopee Buyer Username</option>
                            <option value="person">Person In Charge</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <a id='resetButton' href="../reset.php?redirect=finance/shopee_order_req_income_table_summary.php" class="btn btn-sm btn-rounded btn-primary"> <i class="fa fa-refresh"></i> Reset </a>
                    </div>
                </div>
                <table class="table table-striped" id="shopee_order_req_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col">S/N</th>
                            <th id="group_header" scope="col">
                            <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'status') {
                                            echo "Order Status";
                                        } else if ($_GET['group'] == 'shopee_acc') {
                                            echo "Shopee Account";
                                        } else if ($_GET['group'] == 'currency') {
                                            echo "Currency";
                                        } else if ($_GET['group'] == 'package') {
                                            echo "Package";
                                        } else if ($_GET['group'] == 'brand') {
                                            echo "Brand";
                                        } else if ($_GET['group'] == 'buyer') {
                                            echo "Shopee Buyer Username";
                                        } else if ($_GET['group'] == 'person') {
                                            echo "Person In Charge";
                                        }
                                    }
                                ?>
                            </th>
                            <?php 
                                  if (isset($_GET['group2'])) {
                                    echo '<th id="group_header" scope="col">';
                                
                                    if ($_GET['group2'] == 'status') {
                                        echo "Order Status";
                                    } else if ($_GET['group2'] == 'shopee_acc') {
                                        echo "Shopee Account";
                                    } else if ($_GET['group2'] == 'currency') {
                                        echo "Currency";
                                    } else if ($_GET['group2'] == 'package') {
                                        echo "Package";
                                    } else if ($_GET['group2'] == 'brand') {
                                        echo "Brand";
                                    } else if ($_GET['group2'] == 'buyer') {
                                        echo "Shopee Buyer Username";
                                    } else if ($_GET['group2'] == 'person') {
                                        echo "Person In Charge";
                                    }
                                    echo '</th>';
                                }
                                ?>
                            <th scope="col">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                         $groupOption = isset($_GET['group']) ? $_GET['group'] : ''; 
                         $groupOption2 = isset($_GET['group2']) ? $_GET['group2'] : ''; 
                         $groupOption3 = isset($_GET['timeRange']) ? $_GET['timeRange'] : ''; 
                         $groupOption4 = isset($_GET['timeInterval']) ? $_GET['timeInterval'] : ''; 
                         $groupedRows = [];
                         $counters = 1;
                     
                        while ($row = $result->fetch_assoc()) {
                            
                            $viewActMsg = '';
                            $sql = '';
                            $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", '', SHOPEE_ACC, $finance_connect);
                            $acc = $q1->fetch_assoc();
                            $shopee =  isset($acc['name']) ? $acc['name'] : '';

                            $q8 = getData('order_status', "id='" . $row['orderID'] . "'", '', $tblName, $finance_connect);
                            $statuss = $q8->fetch_assoc();
                            $status =  isset($statuss['order_status']) ? $statuss['order_status'] : '';

                            $q7 = getData('*', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                            $cur = $q7->fetch_assoc();
                            $curr =  isset($cur['unit']) ? $cur['unit'] : '';

                            $q2 = getData('name', "id='" . $row['package'] . "'", '', PKG, $connect);
                            $pkge = $q2->fetch_assoc();
                            $pkg =  isset($pkge['name']) ? $pkge['name'] : '';
                            
                            $q3 = getData('name', "id='" . $row['brand'] ."'", '', BRAND, $connect);
                            $brands = $q3->fetch_assoc();
                            $brand =  isset($brands['name']) ? $brands['name'] : '';

                            $q4 = getData('buyer_username', "id='" . $row['buyer'] . "'", '', SHOPEE_CUST_INFO, $finance_connect);
                            $buyers = $q4->fetch_assoc();
                            $buyer = isset($buyers['buyer_username']) ? $buyers['buyer_username'] : '';

                            $q6 = getData('*', "id='" . $row['buyer_pay_meth'] . "'", '', PAY_MTHD_SHOPEE, $finance_connect);
                            $pays = $q6->fetch_assoc();
                            $pay = isset($pay['name']) ? $pay['name'] : '';

                            $q5 = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $pics = $q5->fetch_assoc();
                            $pic = isset($pics['name']) ? $pics['name'] : '';

                            $createdate = $row['date'];
                            
                            if ($groupOption && $groupOption3) {
                                switch ($groupOption) {
                                    case 'person':
                                        $key = $pic;
                                        break;
                                    case 'brand':
                                        $key = $brand;
                                        break;
                                    case 'status':
                                        $key = $status;
                                        break;
                                    case 'package':
                                        $key = $pkg;
                                        break;
                                    case 'shopee_acc':
                                        $key = $shopee;
                                        break;
                                    case 'currency':
                                        $key = $curr;
                                        break;
                                    case 'buyer':
                                        $key = $buyer;
                                        break;
                                    default:
                                        $key = $brand;
                                        break;
                                }
                                switch ($groupOption2) {
                                    case 'person':
                                        $key2 = $pic;
                                        break;
                                    case 'brand':
                                        $key2 = $brand;
                                        break;
                                    case 'status':
                                        $key2 = $status;
                                        break;
                                    case 'package':
                                        $key2 = $pkg;
                                        break;
                                    case 'shopee_acc':
                                        $key2 = $shopee;
                                        break;
                                    case 'currency':
                                        $key2 = $curr;
                                        break;
                                    case 'buyer':
                                        $key2 = $buyer;
                                        break;
                                    default:
                                        $key2 = null;
                                        break;
                                }
                                if (($groupOption === 'status' || $groupOption === 'shopee_acc' || $groupOption === 'currency' || $groupOption === 'package' || $groupOption === 'brand' || $groupOption === 'buyer' || $groupOption === 'person') && $groupOption4 === 'daily') {
                                    if ($groupOption3 === $createdate) {
                                    if ((isset($_GET['ids']))&& $groupOption2 == '') {
                                        $ids = explode(',', $_GET['ids']);
                                        
                                        foreach ($ids as $id) {
                                            $decodedId = urldecode($id);
                                            if (isset($row['id']) && $row['id'] == $decodedId) {
                                                $combinedKey = $key . '_' . $key2;
                                
                                                if (!isset($groupedRows[$combinedKey])) {
                                                    $groupedRows[$combinedKey] = [
                                                        'ids' => [$decodedId],
                                                        'totalTopupAmount' => $row['price']
                                                    ];
                                                } else {
                                                    $groupedRows[$combinedKey]['ids'][] = $decodedId;
                                                    $groupedRows[$combinedKey]['totalTopupAmount'] += $row['price'];
                                                }
                                            }
                                        }
                                    }else{
                                        $combinedKey = $key . '_' . $key2;
                                
                                        if (!isset($groupedRows[$combinedKey])) {
                                            $groupedRows[$combinedKey] = [
                                                'ids' => [$row['id']],
                                                'totalTopupAmount' => $row['price']
                                            ];
                                        } else {
                                            $groupedRows[$combinedKey]['ids'][] = $row['id'];
                                            $groupedRows[$combinedKey]['totalTopupAmount'] += $row['price'];
                                        }
                                    }
                                }
                              }
                              else if (($groupOption === 'status' || $groupOption === 'shopee_acc' || $groupOption === 'currency' || $groupOption === 'package' || $groupOption === 'brand' || $groupOption === 'buyer' || $groupOption === 'person') && $groupOption4 !== 'daily') {
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
                                   
                                    if ((isset($_GET['ids']))&& $groupOption2 == '') {
                                        $ids = explode(',', $_GET['ids']);
                                        
                                    foreach ($ids as $id) {
                                            $decodedId = urldecode($id);
                                    if (isset($acc['name'], $row['id']) && $row['id'] == $decodedId) {
                                        $combinedKey = $key . '_' . $key2;
                          
                                        if (!isset($groupedRows[$combinedKey])) {
                                            $groupedRows[$combinedKey] = [
                                                'ids' => [$decodedId],
                                                'totalTopupAmount' => $row['price']
                                            ];
                                        } else {
                                            $groupedRows[$combinedKey]['ids'][] = $decodedId;
                                            $groupedRows[$combinedKey]['totalTopupAmount'] += $row['price'];
                                        }
                                    }
                                    }
                                    }else{
                                        $combinedKey = $key . '_' . $key2;
                                
                                        if (!isset($groupedRows[$combinedKey])) {
                                            $groupedRows[$combinedKey] = [
                                                'ids' => [$row['id']],
                                                'totalTopupAmount' => $row['price']
                                            ];
                                        } else {
                                            $groupedRows[$combinedKey]['ids'][] = $row['id'];
                                            $groupedRows[$combinedKey]['totalTopupAmount'] += $row['price'];
                                        }
                                    }
                                  }
                              }
                          }                      
                          }
                          foreach ($groupedRows as $combinedKey => $groupedRow) {
                            list($key, $key2) = explode('_', $combinedKey);
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
                            
                            $url = "shopee_order_req_income_table_detail.php?ids=" . urlencode($ids);
                            
                            echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                            echo '<th class="hideColumn" scope="row">' . $ids . '</th>'; 
                            echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $ids . '"></th>';
                            echo '<th scope="row">' . $counters++ . '</th>';
                            echo '<td scope="row">' . $key . '</td>';
                            
                            if($key2 != $key && $key2 != null){
                                echo '<td scope="row">' . $key2 . '</td>';
                            }else if ($groupOption2 != null && $key2 == null) {
                                echo '<td scope="row"></td>';
                            }
                         
                            echo '<td scope="row">' . number_format($groupedRow['totalTopupAmount'], 2, '.', '') . '</td>';
                             $total += $groupedRow['totalTopupAmount'];
                            echo '</tr>';
                        }  
                            ?>

                           
                        <?php } ?>
                    </tbody>
                    <tfoot>
                    <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col">S/N</th>
                            <th id="group_header" scope="col">
                            
                            <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'status') {
                                            echo "Order Status";
                                        } else if ($_GET['group'] == 'shopee_acc') {
                                            echo "Shopee Account";
                                        } else if ($_GET['group'] == 'currency') {
                                            echo "Currency";
                                        } else if ($_GET['group'] == 'package') {
                                            echo "Package";
                                        } else if ($_GET['group'] == 'brand') {
                                            echo "Brand";
                                        } else if ($_GET['group'] == 'buyer') {
                                            echo "Shopee Buyer Username";
                                        } else if ($_GET['group'] == 'person') {
                                            echo "Person In Charge";
                                        }
                                    }
                                ?>
                            </th>
                            <?php 
                                  if (isset($_GET['group2'])) {
                                    echo '<th id="group_header" scope="col">';
                                
                                    if ($_GET['group2'] == 'status') {
                                        echo "Order Status";
                                    } else if ($_GET['group2'] == 'shopee_acc') {
                                        echo "Shopee Account";
                                    } else if ($_GET['group2'] == 'currency') {
                                        echo "Currency";
                                    } else if ($_GET['group2'] == 'package') {
                                        echo "Package";
                                    } else if ($_GET['group2'] == 'brand') {
                                        echo "Brand";
                                    } else if ($_GET['group2'] == 'buyer') {
                                        echo "Shopee Buyer Username";
                                    } else if ($_GET['group2'] == 'person') {
                                        echo "Person In Charge";
                                    }
                                    echo '</th>';
                                }
                                ?>
                            <th scope="col"><?php echo number_format($total, 2, '.', ''); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php } ?>
        </div>

    </div>

</body>
<script>
    $(document).ready(function() {
    var groupOption = '<?php echo $groupOption; ?>'; // Get the group option value from PHP

    // Loop through each option in the select dropdown
    $('#group2 option').each(function() {
        if ($(this).val() === groupOption) {
            // Hide the option if its value matches groupOption
            $(this).hide();
        }
    });
});

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
        $('#shopee_order_req_table').DataTable().$('tr', { "filter": "applied" }).each(function () {
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

            window.location.href = "shopee_order_req_income_table.php";
        } else {
            console.log('No checkboxes are checked.');
        }
    });

    function updateCheckboxesOnOtherPages(isChecked) {
        // Get all cells in the DataTable
        var cells = $('#shopee_order_req_table').DataTable().cells().nodes();

        // Check/uncheck all checkboxes in the DataTable
        $(cells).find('.export').prop('checked', isChecked);
    }
});

    <?php include "../js/order_req.js" ?>
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
    datatableAlignment('shopee_order_req_table');
</script>

</html>