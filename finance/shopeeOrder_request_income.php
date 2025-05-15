<?php
ob_start();
$pageTitle = "Shopee Order Report";
$isFinance = 1;
$currentPagePin = 123;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';
include_once 'submodel/filterDateFunction.php';
include_once 'submodel/convertDBnaming.php';
include_once 'submodel/generateGroupByDropdown.php';
include ROOT.'/include/access.php';


require_once '../header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . "_list.xlsx";
$img_path = '../' . img_server . 'finance/shopee_order_req/';
$total = 0;
$currentpageRedirect = SITEURL . "/finance/shopeeOrder_request_income.php";
$detailPage = SITEURL . "/finance/shopee_order_req_income_table_detail.php";

$tempDir = '../' . img_server . "temp/";
$tempAttachDir = $tempDir . "attachment/";
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}
if (!file_exists($tempAttachDir)) {
    mkdir($tempAttachDir, 0777, true);
}

$checkboxValues = isset($_COOKIE['rowID']) ? $_COOKIE['rowID'] : '';

$groupOption = isset($_GET['group']) ? input('group') : 'brand';
$groupOption2 = isset($_GET['group2']) ? input('group2') : '';
$groupOption3 = isset($_GET['timeRange']) ? input('timeRange') : date("Y-m-d");
$groupOption4 = isset($_GET['timeInterval']) ? input('timeInterval') : 'daily';
$sqlNode = 'date';

$sqlQuery = generateDateQuery($groupOption3, $groupOption4, $sqlNode);

function getGroupByValue($option)
{
    switch ($option) {
        case 'person':
            return 'pic';
        case 'brand':
            return 'brand';
        case 'status':
            return 'order_status';
        case 'package':
            return 'package';
        case 'shopee_acc':
            return 'shopee_acc';
        case 'currency':
            return 'currency';
        case 'buyer':
            return 'buyer';
        default:
            return '';
    }
}

$groupbyValue2 = getGroupByValue($groupOption2);
$groupbyValue = getGroupByValue($groupOption);

// Check if any checkboxes are checked
if (!empty($checkboxValues)) {
    setcookie('rowID', '', time() - 3600, '/');
    // Defining column names
    $excelData = array(
        array(
            'S/N',

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
            'ORDER STATUS'
        )
    );    // Get the data from the database using the WHERE clause
    $query2 = $finance_connect->query("SELECT * FROM " . SHOPEE_SG_ORDER_REQ . " WHERE status = 'A' AND id IN ($checkboxValues) ORDER BY date ASC, package ASC, brand ASC, package ASC, price ASC");

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
$num = 1;   // numbering
$tblName = SHOPEE_SG_ORDER_REQ;
// echo "<br><br><br><br><br><br>";
$redirect_page = $SITEURL . '/finance/shopee_order_req.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_order_req_table.php';

$selectFields = "$groupbyValue, SUM(price) as price, id, GROUP_CONCAT(id) AS combined_ids";

if (!empty($groupbyValue2)) {
    $selectFields = "$groupbyValue, $groupbyValue2, SUM(price) as price, id, GROUP_CONCAT(id) AS combined_ids";
    $groupByClause = "$groupbyValue, $groupbyValue2";
} else {
    $groupByClause = $groupbyValue;
}

$result = getData(
    $selectFields,
    $sqlQuery . ' GROUP BY ' . $groupByClause ,
    '',
    SHOPEE_SG_ORDER_REQ,
    $finance_connect
);?>

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
                        <?php echo $pageTitle ?>
                    </p>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>
                            <?php echo $pageTitle ?>
                        </h2>
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                    href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                    Request </a>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-rounded btn-primary" name="exportBtn" id="addBtn"
                                onclick="captureAndExport('<?php echo $tblName; ?>')"><i
                                    class="fa-solid fa-file-export"></i> Export</a>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <?php
                include_once 'submodel/filter.php';
                include_once 'submodel/filterDate.php';
                $group1DropdownOption = [
                    "brand" => "Brand",
                    "status" => "Order Status",
                    "shopee_acc" => "Shopee Account",
                    "currency" => "Currency",
                    "package" => "Package",
                    "buyer" => "Shopee Buyer Username",
                    "person" => "Person In Charge"
                ];
                echo generateGroupByDropdown('group', $group1DropdownOption, 'col-md-2', $groupOption);
                echo generateGroupByDropdown('group2', $group1DropdownOption, 'col-md-2', $groupOption2);
                ?>

                <!-- <div class="col-md-3">
                    <label class="form-label">Group by:</label>
                    <select class="form-select" id="group">
                        <option value="brand" selected>Brand</option>
                        <option value="status">Order Status</option>
                        <option value="shopee_acc">Shopee Account</option>
                        <option value="currency">Currency</option>
                        <option value="package">Package</option>

                        <option value="buyer">Shopee Buyer Username</option>
                        <option value="person">Person In Charge</option>
                    </select>
                </div> -->
                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <a id='resetButton' href="../reset.php?redirect=<?php echo $currentpageRedirect; ?>"
                        class="btn btn-sm btn-rounded btn-primary"> <i class="fa fa-refresh"></i> Reset </a>
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
                            <th scope="col">S/N</th>
                            <th id="group_header" scope="col">
                                <?php
                                if (input('group')) {
                                    echo getGroupHeader(input('group'));
                                }
                                ?>
                            </th>
                            <th id="group_header" scope="col">
                                <?php
                                if (input('group2')) {
                                    echo getGroupHeader(input('group2'));
                                }
                                ?>
                            </th>
                            <th scope="col">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counters = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr onclick=\"window.location='" . $detailPage . "?ids=" . urlencode($row['combined_ids']) . "'\" style=\"cursor:pointer;\">";
                            echo '<th class="hideColumn" scope="row">' . $row['id'] . '</th>';
                            echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $row['id'] . '">
                        </th>';
                            echo '<th scope="row">' . $counters++ . '</th>';
                            echo '<td scope="row">' . convertDbNaming($groupbyValue, $row[$groupbyValue]) . '</td>';
                            echo '<td scope="row">' . ($groupbyValue2 ? convertDbNaming($groupbyValue2, $row[$groupbyValue2]) : '') . '</td>';
                            $price = !empty($row['price']) ? (float) $row['price'] : 0;
                            echo '<td scope="row">' . $row['price'] . '</td>';

                            $total += $price;

                            echo '</tr>';
                        } ?>
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
                                if (input('group')) {
                                    echo getGroupHeader(input('group'));
                                }
                                ?>
                            </th>
                            <th id="group_header" scope="col">
                                <?php
                                if (input('group2')) {
                                    echo getGroupHeader(input('group2'));
                                }
                                ?>
                            </th>
                            <th scope="col"><?php echo number_format($total, 2, '.', ''); ?></th>
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
            $('#shopee_order_req_table').DataTable().$('tr', {
                "filter": "applied"
            }).each(function () {
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

                window.location.href = $currentpageRedirect;
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