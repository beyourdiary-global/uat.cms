<?php
ob_start();
$pageTitle = 'Lazada Order Request';
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

require_once 'header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . '_list.xlsx';
$img_path = '../' . img_server . 'finance/lazada_order_req/';

$tempDir = '../' . img_server . 'temp/';
$tempAttachDir = $tempDir . 'attachment/';
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
        array(
            'S/N',
            'LAZADA ACCOUNT',
            'CURRENCY UNIT',
            'LAZADA COUNTRY',
            'CUSTOMER ID',
            'CUSTOMER NAME',
            'CUSTOMER EMAIL',
            'CUSTOMER PHONE',
            'COUNTRY',
            'ORDER NUMBER',
            'SALES PERSON IN CHARGE',
            'SHIPPING RECEIVER NAME',
            'SHIPPING RECEIVER ADDRESS',
            'SHIPPING RECEIVER CONTACT',
            'BRAND',
            'SERIES',
            'PKG',
            'BARCODE SLOT',
            'ITEM PRICE CREDIT',
            'COMMISION',
            'OTHER DISCOUNT',
            'PAYMENT FEE',
            'FINAL INCOME',
            'PAYMENT METH',
            'REMARK',
            'CREATE BY',
            'CREATE DATE',
            'CREATE TIME',
            'UPDATE BY',
            'UPDATE DATE',
            'UPDATE TIME',
            'ORDER_STATUS'
        )
    );  // Get the data from the database using the WHERE clause
    $query2 = $finance_connect->query('SELECT * FROM ' . LAZADA_ORDER_REQ . " WHERE status = 'A' AND id IN ($checkboxValues) ORDER BY create_date ASC, sales_pic ASC, brand ASC, series ASC, pkg ASC, final_income ASC");

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
                'lazada_acc',
                'curr_unit',
                'lzd_country',
                'cust_id',
                'cust_name',
                'cust_email',
                'cust_phone',
                'country',
                'oder_number',
                'sales_pic',
                'ship_rec_name',
                'ship_rec_address',
                'ship_rec_contact',
                'brand',
                'series',
                'pkg',
                'barcode_slot',
                'item_price_credit',
                'commision',
                'other_discount',
                'pay_fee',
                'final_income',
                'pay_meth',
                'remark',
                'create_by',
                'create_date',
                'create_time',
                'update_by',
                'update_date',
                'update_time',
                'status',
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
            $zipFile = date('Ymd_His') . '.zip';
            $zip = new ZipArchive();

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                die('Failed to create zip file');
            }

            // Add the Excel file to the root of the zip archive
            $zip->addFile($tempExcelFilePath, basename($tempExcelFilePath));

            // Add the 'attachment' folder to the zip archive
            addDirToZip($tempAttachDir, $zip, $tempAttachDir);

            // Close the zip archive
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFile . '"');
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

function deleteDir($dirPath)
{
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
$num = 1;  // numbering
$tblName = LAZADA_ORDER_REQ;

$redirect_page = $SITEURL . '/lazada_order_req.php';
$deleteRedirectPage = $SITEURL . '/lazada_order_req_table.php';
$result = getData('*', '', '', LAZADA_ORDER_REQ, $connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('lazada_order_req');
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
                        <?php if (isActionAllowed('Add', $pinAccess)): ?>
                            <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                href="<?= $redirect_page . '?act=' . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                Record </a>
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
          
          <table class="table table-striped" id="lazada_order_req">
            <thead>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th class="text-center">
                        <input type="checkbox" class="exportAll">
                    </th>
                    <th scope="col">S/N</th>
                    <th scope="col" id="action_col">Action</th>
                    <th scope="col">Order Status</th>
                    <th scope="col">Lazada Account</th>
                    <th scope="col">Currency Unit</th>
                    <th scope="col">Country</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Country</th>
                    <th scope="col">Order Number</th>
                    <th scope="col">Sales Person In Charge</th>
                    <th scope="col">Shipping Receiver Name</th>
                    <th scope="col">Shipping Receiver Address</th>
                    <th scope="col">Shipping Receiver Contact</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Series</th>
                    <th scope="col">Package</th>
                    <th scope="col">Item Price Credit</th>
                    <th scope="col">Commision</th>
                    <th scope="col">Other Discount</th>
                    <th scope="col">Payment Fee</th>
                    <th scope="col">Final Income</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) {
                    if (isset($_GET['ids'])) {             
                    $ids = explode(',', $_GET['ids']);
                    foreach ($ids as $id) {
                    $decodedId = urldecode($id);
                    if (isset( $row['id']) && $row['id'] == $id) {
                    $q1 = getData('name', "id='" . $row['lazada_acc'] . "'", '', LAZADA_ACC, $finance_connect);
                    $lazada_acc = $q1->fetch_assoc();

                    $q2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                    $country = $q2->fetch_assoc();

                    $q3 = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                    $brand = $q3->fetch_assoc();

                    $q4 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                    $series = $q4->fetch_assoc();

                    $q5 = getData('unit', "id='" . $row['curr_unit'] . "'", '', CUR_UNIT, $connect);
                    $curr_unit = $q5->fetch_assoc();

                    $q6 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                    $series = $q6->fetch_assoc();

                    $q7 = getData('name', "id='" . $row['pay_meth'] . "'", '', FIN_PAY_METH, $finance_connect);
                    $pay_meth = $q7->fetch_assoc();

                    $q8 = getData('name', "id='" . $row['pkg'] . "'", '', PKG, $connect);
                    $package = $q8->fetch_assoc();
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
                        <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                        <a class="btn btn-danger" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['curr_unit'] ?>','<?= $row['country'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i class="fas fa-trash-alt"></i></a>
                        <?php endif; ?>
                        </td>
                        <td>
                        <?php
                            $status = $row['order_status'];
                            if ($status == 'P') {
                                $status = 'Processing';
                            }else  if ($status == 'SP') {
                                $status = 'Shipped';
                            }else  if ($status == 'WP') {
                                $status = 'Waiting Packing';
                            }
                            echo $status;
                            ?>
                        </td>
                        <td scope="row"><?= isset($lazada_acc['name']) ? $lazada_acc['name'] : ''  ?></td>
                        <td scope="row"><?= $row['curr_unit'] ?></td>
                        <td scope="row"><?= $row['country'] ?></td>
                        <td scope="row"><?= $row['cust_id'] ?></td>
                        <td scope="row"><?= $row['cust_name'] ?></td>
                        <td scope="row"><?= $row['cust_email'] ?></td>
                        <td scope="row"><?= $row['cust_phone'] ?></td>
                        <td scope="row"><?= isset($country['nicename']) ? $country['nicename'] : ''  ?></td>
                        <td scope="row"><?= $row['oder_number'] ?></td>
                        <td scope="row"><?= $row['sales_pic'] ?></td>
                        <td scope="row"><?= $row['ship_rec_name'] ?></td>
                        <td scope="row"><?= $row['ship_rec_address'] ?></td>
                        <td scope="row"><?= $row['ship_rec_contact'] ?></td>
                        <td scope="row"><?= isset($brand['name']) ? $brand['name'] : ''  ?></td>
                        <td scope="row"><?= isset($series['name']) ? $series['name'] : ''  ?></td>
                        <td scope="row"><?= isset($package['name']) ? $package['name'] : ''  ?></td>
                        <td scope="row"><?= $row['item_price_credit'] ?></td>
                        <td scope="row"><?= $row['commision'] ?></td>
                        <td scope="row"><?= $row['other_discount'] ?></td>
                        <td scope="row"><?= $row['pay_fee'] ?></td>
                        <td scope="row"><?= $row['final_income'] ?></td>
                        <td scope="row"><?= isset($pay_meth['name']) ? $pay_meth['name'] : ''  ?></td>
                        <td scope="row"><?= $row['remark'] ?></td>
                    </tr>
                <?php }}}} ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th class="text-center">
                        <input type="checkbox" class="exportAll">
                    </th>
                    <th scope="col">S/N</th>
                    <th scope="col" id="action_col">Action</th>
                    <th scope="col">Order Status</th>
                    <th scope="col">Lazada Account</th>
                    <th scope="col">Currency Unit</th>
                    <th scope="col">Country</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Country</th>
                    <th scope="col">Order Number</th>
                    <th scope="col">Sales Person In Charge</th>
                    <th scope="col">Shipping Receiver Name</th>
                    <th scope="col">Shipping Receiver Address</th>
                    <th scope="col">Shipping Receiver Contact</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Series</th>
                    <th scope="col">Package</th>
                    <th scope="col">Item Price Credit</th>
                    <th scope="col">Commision</th>
                    <th scope="col">Other Discount</th>
                    <th scope="col">Payment Fee</th>
                    <th scope="col">Final Income</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Remark</th>
                </tr>
            </tfoot>
        </table>
    <?php } ?>
</div>


</div>

</body>
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
        $('#website_order_request_table').DataTable().$('tr', { "filter": "applied" }).each(function () {
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

            window.location.href = "website_order_request_income_table.php";
        } else {
            console.log('No checkboxes are checked.');
        }
    });

    function updateCheckboxesOnOtherPages(isChecked) {
        // Get all cells in the DataTable
        var cells = $('#website_order_request_table').DataTable().cells().nodes();

        // Check/uncheck all checkboxes in the DataTable
        $(cells).find('.export').prop('checked', isChecked);
    }
});

<?php include "js/order_req.js" ?>

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
datatableAlignment('lazada_order_req');
</script>

</html>