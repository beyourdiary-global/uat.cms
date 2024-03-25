<?php
ob_start();
$pageTitle = "Stripe Transaction Backup Record";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';
require_once '../header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . "_list.xlsx";
$img_path = '../' . img_server . 'finance/stripe_trans_backup/';


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
        array('S/N', 'STRIPE PAYOUT ID', 'DATE PAID', 'CURRENCY UNIT', 'AMOUNT', 'ATTACHMENT', 'CREATE BY', 'CREATE DATE', 'CREATE TIME', 'UPDATE BY', 'UPDATE DATE', 'UPDATE TIME')
    );    // Get the data from the database using the WHERE clause
    $query2 = $finance_connect->query("SELECT * FROM " . STRIPE_TRANS_BACKUP . " WHERE status = 'A' AND id IN ($checkboxValues) ORDER BY payout_id ASC, date_paid ASC, curr_unit ASC, amount ASC");
   
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
            $columnNames = array('payout_id', 'date_paid', 'curr_unit', 'amount', 'attachment', 'create_by', 'create_date', 'create_time', 'update_by', 'update_date', 'update_time');

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
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/stripe_trans_backup.php';

$result = getData('*', '', '', STRIPE_TRANS_BACKUP, $finance_connect);

$img_path = SITEURL . img_server . 'finance/stripe_trans_backup/';
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        let table = new DataTable("#stripe_trans_backup_table", {
            paging: $("#stripe_trans_backup_table tbody tr").length > 10,
            searching: $("#stripe_trans_backup_table tbody tr").length > 10,
            /* info: false, */
            order: [[1, "asc"]], // 0 = db id column; 1 = numbering column
            /* responsive: true, */
            autoWidth: false,
            "columnDefs": [
                { "orderable": false, "targets": 0 } // Disabling sorting for the first column (index 0)
            ]
        })
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
                        <?php
                        if ($result) {
                            ?>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        Transaction </a>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="exportBtn" id="addBtn" onclick="if (exportData()) { showExportNotification(); }"><i class="fa-solid fa-file-export"></i> Export</a>
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
                    <div class="col-md-3 dateFilters">
                        <label for="timeInterval" class="form-label">Filter by:</label>
                       <select class="form-select" id="timeInterval" >

                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-5 dateFilters">
                        <label for="dateFilter" class="form-label">Filter by Payment Date:</label>
                        <div class="input-group date" id="datepicker"> 
                        <input type="text" class="form-control" placeholder="Select date" >
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        <div class="input-daterange input-group" id="datepicker2" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start date"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End date"/>
                        </div>
                        <div class="input-group input-daterange" id="datepicker3" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start month"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End month"/>
                            
                            </div>
                        <div class="input-group input-daterange" id="datepicker4" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start year"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End year"/>
                            
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Group by:</label>
                        <select class="form-select" id="group">
                            <option value="currency" selected>Currency</option>
                        </select>
                    </div>
                    
        
                 
                </div>
                <table class="table table-striped" id="stripe_trans_backup_table">
                <thead>
                        <tr>
                            <?php if (!isset($_GET['group'])): ?>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Stripe Payout ID</th>
                            <th scope="col">Date paid</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Attachment</th>
                            <th scope="col" id="action_col">Action</th>
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>      
                            <th id="group_header" scope="col">
                                <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'currency') {
                                            echo "Currency Unit";
                                        }
                                    }
                                ?>
                            </th>
                            <th scope="col">Total Amount</th>
                        <?php endif; ?>
                        </tr>
                    </thead>
                    <?php 
                         $groupOption = isset($_GET['group']) ? $_GET['group'] : ''; 
                         $groupOption3 = isset($_GET['timeRange']) ? $_GET['timeRange'] : ''; 
                         $groupOption4 = isset($_GET['timeInterval']) ? $_GET['timeInterval'] : ''; 
                         $groupedRows = [];
                         $counters = 1;
         
                         function generateTableRow($id, &$counters, $key, $topupAmt) {
                             echo '<tr onclick="window.location=\'stripe_trans_backup_table_summary.php?ids=' . urlencode($id) . '\';" style="cursor:pointer;">';
                             echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $id . '"></th>';
                             echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                             echo '<th scope="row">' . $counters++ . '</th>';
                             echo '<td scope="row">' . $key . '</td>';
                             echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                             echo '</tr>';
                         }
                       
                         $groupedRows = [];
                        
                        while ($row = $result->fetch_assoc()) {
                            if (isset($row['id']) && !empty($row['id'])) {
                                $currs = getData('unit', "id='" . $row['curr_unit'] . "'", '', CUR_UNIT, $connect);
                                $row2 = $currs->fetch_assoc();
                                $curr = isset($row2['unit']) ? $row2['unit'] : '';
                                $createdate = $row['date_paid'];
                                
                                if($groupOption==''){
                                    echo '
                                    <tr>
                                        <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                                        <th class="text-center"><input type="checkbox" class="export" value="' . $row['id'] . '"></th>
                                        <th scope="row">' . $num++ . '</th>
                                        <td scope="row">' . (isset($row['payout_id']) ? $row['payout_id'] : '') . '</td>
                                        <td scope="row">' . (isset($row['date_paid']) ? $row['date_paid'] : '') . '</td>
                                        <td scope="row">' . (isset($row2['unit']) ? $row2['unit'] : '') . '</td>
                                        <td scope="row">' . (isset($row['amount']) ? $row['amount'] : '') . '</td>
                                        <td scope="row">';
                                        if (isset($row['attachment'])) {
                                            echo '<a href="' . $img_path . $row['attachment'] . '" target="_blank">' . $row['attachment'] . '</a>';
                                        }
                                        echo '</td>
                                        <td scope="row">
                                            <div class="dropdown" style="text-align:center">
                                                <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                                    <li>';
                                                    if (isActionAllowed("View", $pinAccess)) {
                                                        echo '<a class="dropdown-item" href="' . $redirect_page . "?id=" . $row['id'] . '">View</a>';
                                                    }
                                                    echo '</li>
                                                    <li>';
                                                    if (isActionAllowed("Edit", $pinAccess)) {
                                                        echo '<a class="dropdown-item" href="' . $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 . '">Edit</a>';
                                                    }
                                                    echo '</li>
                                                    <li>';
                                                    if (isActionAllowed("Delete", $pinAccess)) {
                                                        echo '<a class="dropdown-item" onclick="confirmationDialog(\'' . $row['id'] . '\',[\'' . $row['payout_id'] . '\', \'' . $row['date_paid'] . '\'],\'' . $pageTitle . '\',\'' . $redirect_page . '\',\'' . $SITEURL . '/js_trans_backup_table.php\',\'D\')">Delete</a>';
                                                    }
                                                    echo '</li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                                if ($groupOption && $groupOption3) {
                                    if (($groupOption === 'currency') && $groupOption4 === 'daily') {
                                        $key = $curr;
                                
                                        if ($groupOption3 === $createdate) {
                                        if (!isset($groupedRows[$key])) {
                                            $groupedRows[$key] = [
                                                'ids' => [$row['id']],
                                                'totalTopupAmount' => $row['amount']
                                            ];
                                        } else {
                                            $groupedRows[$key]['ids'][] = $row['id'];
                                            $groupedRows[$key]['totalTopupAmount'] += $row['amount'];
                                        }
                                    }
                                    }
                                    else if (($groupOption === 'currency') && $groupOption4 !== 'daily') {
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
                                            $key = $curr;
                                    
                                            if (!isset($groupedRows[$key])) {
                                                $groupedRows[$key] = [
                                                    'ids' => [$row['id']],
                                                    'totalTopupAmount' => $row['amount']
                                                ];
                                            } else {
                                                $groupedRows[$key]['ids'][] = $row['id'];
                                                $groupedRows[$key]['totalTopupAmount'] += $row['amount'];
                                            }
                                        }
                                    }
                                }                    
                                    
                                }else if ($groupOption === 'currency') {
                                    generateTableRow($row['id'], $counters, $curr, $row['amount']);
                                }
                                }
                                foreach ($groupedRows as $key => $groupedRow) {
                    
                                    $ids = implode(',', $groupedRow['ids']);
                                    $url = $groupOption4 == 'daily' ? "stripe_trans_backup_table_detail.php?ids=" . urlencode($ids) : "stripe_trans_backup_table_summary.php?ids=" . urlencode($ids);
                                    echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                                    echo '<th class="hideColumn" scope="row">' . $ids . '</th>'; 
                                    echo '<th scope="row">' . $counters++ . '</th>';
                                    echo '<td scope="row">' . $key . '</td>';
                                    echo '<td scope="row">' . number_format($groupedRow['totalTopupAmount'], 2, '.', '') . '</td>';
                                    echo '</tr>';
                                }   
                                ?>
                    </tbody>
                    <tfoot>
                    <tr>
                            <?php if (!isset($_GET['group'])): ?>
                            <th class="text-center">
                                <input type="checkbox" class="exportAll">
                            </th>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col">Stripe Payout ID</th>
                            <th scope="col">Date paid</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Attachment</th>
                            <th scope="col" id="action_col">Action</th>
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>      
                            <th id="group_header" scope="col">
                                <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'currency') {
                                            echo "Currency Unit";
                                        }
                                    }
                                ?>
                            </th>
                            <th scope="col">Total Amount</th>
                        <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            <?php } ?>
        </div>

    </div>

</body>
<script>
    <?php include "../js/fb_ads_topup_table.js" ?>
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
    datatableAlignment('stripe_trans_backup_table');
    <?php include '../js/stripe_trans_backup_table.js' ?>
</script>

</html>