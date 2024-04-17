<?php
ob_start();
$pageTitle = "Shopee SG Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

require_once '../header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . "_list.xlsx";
$img_path = '../' . img_server . 'finance/internal_consume_ticket_credit/';


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
                        <?php echo $pageTitle ?> Detail
                    </p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>
                            <?php echo $pageTitle ?> Detail
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

                <table class="table table-striped" id="shopee_order_req_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Shopee Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Package</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Shopee Buyer Username</th>
                            <th scope="col">Buyer Payment Method</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Product Price</th>
                            <th scope="col">Voucher</th>
                            <th scope="col">Actual Shipping Fee</th>
                            <th scope="col">Service Fee (incl. GST)</th>
                            <th scope="col">Transaction Fee (incl. GST)</th>
                            <th scope="col">AMS Commission Fee</th>
                            <th scope="col">Fees & Charges</th>
                            <th scope="col">Final Amount</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        
                        while ($row = $result->fetch_assoc()) {
                            if (isset($_GET['ids'])) {
                            $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", '', SHOPEE_ACC, $finance_connect);
                            $acc = $q1->fetch_assoc();
                            $ids = explode(',', $_GET['ids']);
                            foreach ($ids as $id) {
                            $decodedId = urldecode($id);
                            if (isset($acc['name'], $row['id']) && !empty($acc['name']) && $row['id'] == $id) {
                            
                            $q7 = getData('*', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                            $curr = $q7->fetch_assoc();

                            $q2 = getData('name', "id='" . $row['package'] . "'", '', PKG, $connect);
                            $pkg = $q2->fetch_assoc();

                            $q3 = getData('name', "id='" . $row['brand'] ."'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            $q4 = getData('buyer_username', "id='" . $row['buyer'] . "'", '', SHOPEE_CUST_INFO, $finance_connect);
                            $buyer = $q4->fetch_assoc();

                            $q6 = getData('*', "id='" . $row['buyer_pay_meth'] . "'", '', PAY_MTHD_SHOPEE, $finance_connect);
                            $pay = $q6->fetch_assoc();

                            $q5 = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $pic = $q5->fetch_assoc();
                            ?>

                            <tr>
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th class="text-center"><input type="checkbox" class="export" value="<?= $row['id'] ?>"></th>

                                <th scope="row">
                                    <?= $num++; ?>
                                </th>

                                <td scope="row" class="btn-container">
                                <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess); ?>
                                <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2); ?>
                                <?php renderDeleteButton($pinAccess, $row['id'], $row['orderID'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage); ?>
                                </td>
                                <td scope="row">
                                <?php
                                $status = $row['order_status'];
                                if ($status == 'P') {
                                    $status = 'Processing';
                                }else  if ($status == 'SP') {
                                    $status = 'Shipped';
                                }
                                echo $status;
                                ?>
                                </td>

                                <td scope="row">
                                    <?= $acc['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $curr['unit'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['orderID'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['date'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['time'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pkg['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $brand['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $buyer['buyer_username'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pay['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pic['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['price'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['voucher'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['act_shipping_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['service_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['trans_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['ams_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['fees'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['final_amt'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['remark'] ?? '' ?>
                                </td>
                            </tr>
                        <?php }}}} ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Shopee Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Package</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Shopee Buyer Username</th>
                            <th scope="col">Buyer Payment Method</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Product Price</th>
                            <th scope="col">Voucher</th>
                            <th scope="col">Actual Shipping Fee</th>
                            <th scope="col">Service Fee (incl. GST)</th>
                            <th scope="col">Transaction Fee (incl. GST)</th>
                            <th scope="col">AMS Commission Fee</th>
                            <th scope="col">Fees & Charges</th>
                            <th scope="col">Final Amount</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </tfoot>
                </table>
            <?php } ?>
        </div>

    </div>

</body>
<script>
        <?php include "../js/shopee_order_req_table.js" ?>
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