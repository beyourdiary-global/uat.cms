<?php
ob_start();
$pageTitle = "Website Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';
require_once '../header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . "_list.xlsx";
$img_path = '../' . img_server . 'finance/website_order_request/';


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
        array(
            'S/N',
            'ORDER ID',
            'BRAND',
            'SERIES',
            'PKG',
            'BARCODE SLOT',
            'COUNTRY',
            'CURRENCY',
            'PRICE',
            'SHIPPING',
            'DISCOUNT',
            'TOTAL',
            'PAYMENT METHOD',
            'PERSON IN CHARGE',
            'CUSTOMER ID',
            'CUSTOMER NAME',
            'CUSTOMER EMAIL',
            'CUSTOMER BIRTHDAY',
            'SHIPPING_NAME',
            'SHIPPING_ADDRESS',
            'SHIPPING_CONTACT',
            'REMARK',
            'CREATE BY',
            'CREATE DATE',
            'CREATE TIME',
            'UPDATE BY',
            'UPDATE DATE',
            'UPDATE TIME',
            'ORDER STATUS'
        )
    );    // Get the data from the database using the WHERE clause
    $query2 = $finance_connect->query("SELECT * FROM " . WEB_ORDER_REQ . " WHERE status = 'A' AND id IN ($checkboxValues) ORDER BY create_date ASC, pic ASC, brand ASC, series ASC, pkg ASC, price ASC");
   
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
                'order_id',
                'brand',
                'series',
                'pkg',
                'barcode_slot',
                'country',
                'currency',
                'price',
                'shipping',
                'discount',
                'total',
                'pay_method',
                'pic',
                'cust_id',
                'cust_name',
                'cust_email',
                'cust_birthday',
                'shipping_name',
                'shipping_address',
                'shipping_contact',
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
$tblName = WEB_ORDER_REQ;
$redirect_page = $SITEURL . '/finance/website_order_request.php';
$deleteRedirectPage = $SITEURL . '/finance/website_order_request_table.php';
$result = getData('*', '', '', WEB_ORDER_REQ, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('website_order_request_table');
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

                    <table class="table table-striped" id="website_order_request_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Series</th>
                            <th scope="col">Package</th>
                            <th scope="col">Country</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Price</th>
                            <th scope="col">Shipping</th>
                            <th scope="col">Discount Price</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Person In Charges</th>
                            <th scope="col">Customer ID</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Customer Email</th>
                            <th scope="col">Customer Birthday</th>
                            <th scope="col">Shipping Name</th>
                            <th scope="col">Shipping Address</th>
                            <th scope="col">Shipping Contact</th>
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
                            $q1 = getData('unit', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                            $currency = $q1->fetch_assoc();

                            $q2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                            $country = $q2->fetch_assoc();

                            $q3 = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            $q4 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                            $series = $q4->fetch_assoc();

                            $q5 = getData('name', "id='" . $row['pkg'] . "'", '', PKG, $connect);
                            $package = $q5->fetch_assoc();

                            $q6 = getData('cust_id', "id='" . $row['cust_id'] . "'", '', WEB_CUST_RCD, $connect);
                            $cust_id = $q6->fetch_assoc();

                            $q8 = getData('name', "id='" . $row['pay_method'] . "'", '', FIN_PAY_METH, $finance_connect);
                            $pay = $q8->fetch_assoc();
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
                                <?php renderDeleteButton($pinAccess, $row['id'], $row['order_id'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage); ?>
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
                                    <?= $row['order_id'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['brand'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['series'] ?? '' ?>
                                </td>
                               
                                <td scope="row">
                                    <?= $package['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $country['nicename'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $currency['unit'] ?? '' ?>
                                </td>
                              
                                <td scope="row">
                                    <?= $row['price'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['shipping'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['discount'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['total'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $pay['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['pic'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $cust_id['cust_id'] ?? '' ?>
                                </td>

                                <td scope="row">
                                    <?= $row['cust_name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['cust_email'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['cust_birthday'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['shipping_name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['shipping_address'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['shipping_contact'] ?? '' ?>
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
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Series</th>
                            <th scope="col">Package</th>
                            <th scope="col">Country</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Price</th>
                            <th scope="col">Shipping</th>
                            <th scope="col">Discount Price</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Person In Charges</th>
                            <th scope="col">Customer ID</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Customer Email</th>
                            <th scope="col">Customer Birthday</th>
                            <th scope="col">Shipping Name</th>
                            <th scope="col">Shipping Address</th>
                            <th scope="col">Shipping Contact</th>
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
    datatableAlignment('website_order_request_table');
</script>

</html>