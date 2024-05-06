<!-- courier AC
curr AC -->


<?php
ob_start();
$pageTitle = "Delivery Fees Claim Record";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';


require_once '../header/PhpXlsxGenerator/PhpXlsxGenerator.php';
$fileName = date('Y-m-d H:i:s') . "_list.xlsx";
$img_path = '../' . img_server . 'finance/stock_credit_top_up_request/';


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
        array('S/N', 'CLAIM DATE', 'COURIER', 'SUBTOTAL', 'TAX','TOTAL','REMARK','CREATE BY', 'CREATE DATE', 'CREATE TIME', 'UPDATE BY', 'UPDATE DATE', 'UPDATE TIME')
    );    // Get the data from the database using the WHERE clause
    $query2 = $finance_connect->query("SELECT * FROM " . DEL_FEES_CLAIM . " WHERE status = 'A' AND id IN ($checkboxValues) ORDER BY claim_date ASC, courier ASC, currency ASC, subtotal ASC, tax ASC, total ASC");
   
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
            $columnNames = array('claim_date' , 'courier' , 'currency' , 'subtotal' , 'tax' , 'total' ,'remark','create_by', 'create_date', 'create_time', 'update_by', 'update_date', 'update_time');

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
$_SESSION['searchChk'] = '';
unset($_SESSION['resetChk']);
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$deleteRedirectPage = $SITEURL . '/finance/del_fees_claim_table.php';
$redirect_page = $SITEURL . '/finance/del_fees_claim.php';
$result = getData('*', '', '', DEL_FEES_CLAIM, $finance_connect);
$tblName = DEL_FEES_CLAIM;
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('del_fees_claim_table');
    });
</script>

<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-11">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2><?php echo $pageTitle ?></h2>
                        <?php if ($result) { ?>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Transaction </a>
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
                        <label for="dateFilter" class="form-label">Filter by Claim Date:</label>
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
                        <select class="form-select" id="group">
                        <option value="" selected>Select a Group</option>
                            <option value="courier" >Courier</option>
                            <option value="currency">Currency</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <a id='resetButton' href="../reset.php?redirect=finance/del_fees_claim_table.php" class="btn btn-sm btn-rounded btn-primary"> <i class="fa fa-refresh"></i> Reset </a>
                    </div>
        
                 
                </div>
            
                <input type="hidden" id="groupParam" name="group" value="">
                <input type="hidden" id="timeIntervalParam" name="timeInterval" value="">
                <input type="hidden" id="timeRangeParam" name="timeRange" value="">

                <table class="table table-striped" id="del_fees_claim_table">
                    <thead>
                        <tr>
                        <?php if (!isset($_GET['group'])): ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                            <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Claim Date</th>
                            <th scope="col">Courier</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Tax</th>
                            <th scope="col">Total</th>
                            <th scope="col">Remark</th>
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                            <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col" width="60px">S/N</th>
                            <th id="group_header" scope="col"><?php echo isset($_GET['group']) && $_GET['group'] == 'courier' ? "Courier" : "Currency"; ?></th>
                            <th scope="col">Total</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>

                        <?php 
                        $groupOption = isset($_GET['group']) ? $_GET['group'] : ''; 
                        $groupOption3 = isset($_GET['timeRange']) ? $_GET['timeRange'] : ''; 
                        $groupOption4 = isset($_GET['timeInterval']) ? $_GET['timeInterval'] : ''; 
                        $groupedRows = [];
                        $counters = 1;
        
                        function generateTableRow($id, &$counters, $courier, $createdate, $topupAmt) {
                            echo '<tr onclick="window.location=\'del_fees_claim_table_summary.php?ids=' . urlencode($id) . '\';" style="cursor:pointer;">';
                            echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                            echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $id . '"></th>';
                            echo '<th scope="row">' . $counters++ . '</th>';
                            echo '<td scope="row">' . $courier . '</td>';
                            echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                            echo '</tr>';
                        }
                        function generateTableRow2($id, &$counters, $courier, $curr, $createdate,$topupAmt) {
                            echo '<tr onclick="window.location=\'del_fees_claim_table_summary.php?ids=' . urlencode($id) . '\';" style="cursor:pointer;">';
                            echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                            echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $id . '"></th>';
                            echo '<th scope="row">' . $counters++ . '</th>';
                            echo '<td scope="row">' . $curr . '</td>';
                            echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                            echo '</tr>';
                        }
                        $groupedRows = [];
                        while ($row = $result->fetch_assoc()) {
                            $viewActMsg = '';
                            $sql = '';
                            if (isset($row['id']) && !empty($row['id'])) {

                                $currs = getData('unit', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                                $row2 = $currs->fetch_assoc();
                                
                                $couriers = getData('name', "id='" . $row['courier'] . "'", '', COURIER, $connect);
                                $row3 = $couriers->fetch_assoc();  
            
                                $courier = isset($row3['name']) ? $row3['name'] : '';;
                                $curr = isset($row2['unit']) ? $row2['unit'] : '';;

                                $createdate = $row['claim_date'];
                             }
                                if ($groupOption == '') {
                                    echo '<tr>
                                    <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                                    <th class="text-center"><input type="checkbox" class="export" value="'  . $row['id'] . '"></th>
                                    <td scope="row">' . $num++ . '</td>
                                    <td scope="row" class="btn-container">
                                    <div class="d-flex align-items-center">'    
                                    ?>
                                        <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess);?>
                                        <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2) ?>
                                        <?php renderDeleteButton($pinAccess, $row['id'],'', '', $pageTitle, $redirect_page, $deleteRedirectPage) ?>
                                    <?php echo'</div>
                                    </td>
                                    <td scope="row">' . (isset($row['claim_date']) ? $row['claim_date'] : '') . '</td>
                                    <td scope="row">' . (isset($row3['name']) ? $row3['name'] : '') . '</td>
                                    <td scope="row">' . (isset($row2['unit']) ? $row2['unit'] : '') . '</td>
                                    <td scope="row">' . (isset($row['subtotal']) ? $row['subtotal'] : '') . '</td>
                                    <td scope="row">' . (isset($row['tax']) ? $row['tax'] : '') . '</td>
                                    <td scope="row">' . (isset($row['total']) ? $row['total'] : '') . '</td>
                                    <td scope="row">' . (isset($row['remark']) ? $row['remark'] : '') . '</td>
                                   
                                </tr>';
                                
                                }  
                                if ($groupOption && $groupOption3) {
                                    if ($groupOption === 'courier' && $groupOption3 === $createdate) {
                                        if (!isset($groupedRows[$courier])) {
                                            $groupedRows[$courier] = [
                                                'ids' => [$row['id']], 
                                                'totalTopupAmount' => $row['total']
                                            ];
                                        } else {
                                            $groupedRows[$courier]['ids'][] = $row['id']; 
                                            $groupedRows[$courier]['totalTopupAmount'] += $row['total'];
                                        }
                                    }else if ($groupOption === 'currency' && $groupOption3 === $createdate) {
                                        if (!isset($groupedRows[$curr])) {
                                            $groupedRows[$curr] = [
                                                'ids' => [$row['id']],
                                                'totalTopupAmount' => $row['total']
                                            ];
                                        } else {
                                            $groupedRows[$curr]['ids'][] = $row['id']; 
                                            $groupedRows[$curr]['totalTopupAmount'] += $row['total'];
                                        }
            
                                    }else if ($groupOption === 'currency' && $groupOption4 === 'weekly') {
                                        $dateRange = explode('to', $groupOption3);
                                        $startDate = strtotime(trim($dateRange[0]));
                                        $endDate = strtotime(trim($dateRange[1]));
                                    
                                        $createdTimestamp = strtotime($createdate);
                                    
                                        if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                            if (!isset($groupedRows[$curr])) {
                                                $groupedRows[$curr] = [
                                                    'ids' => [$row['id']],
                                                    'totalTopupAmount' => $row['total']
                                                ];
                                            } else {
                                                $groupedRows[$curr]['ids'][] = $row['id']; 
                                                $groupedRows[$curr]['totalTopupAmount'] += $row['total'];
                                            }
                                        }
                                    }else if ($groupOption === 'courier' && $groupOption4 === 'weekly') {
                                        $dateRange = explode('to', $groupOption3);
                                        $startDate = strtotime(trim($dateRange[0]));
                                        $endDate = strtotime(trim($dateRange[1]));
                                    
                                        $createdTimestamp = strtotime($createdate);
                                    
                                        if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                            if (!isset($groupedRows[$courier])) {
                                                $groupedRows[$courier] = [
                                                    'ids' => [$row['id']], 
                                                    'totalTopupAmount' => $row['total']
                                                ];
                                            } else {
                                                $groupedRows[$courier]['ids'][] = $row['id']; 
                                                $groupedRows[$courier]['totalTopupAmount'] += $row['total'];
                                            }
                                        }
                                    }else if ($groupOption === 'currency' && $groupOption4 === 'monthly') {
                                        $dateRange = explode('to', $groupOption3);
                                        $startDate = strtotime(trim($dateRange[0]));
                                        $endDate = strtotime('last day of ' . trim($dateRange[1]));
                                    
                                        $createdTimestamp = strtotime($createdate);
                                    
                                        if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                            $monthYear = date('Y-m', $createdTimestamp);
                                    
                                            if (!isset($groupedRows[$curr])) {
                                                $groupedRows[$curr] = [
                                                    'ids' => [$row['id']],
                                                    'totalTopupAmount' => $row['total']
                                                ];
                                            } else {
                                                $groupedRows[$curr]['ids'][] = $row['id']; 
                                                $groupedRows[$curr]['totalTopupAmount'] += $row['total'];
                                            }
                                        }
                                    }else if ($groupOption === 'courier' && $groupOption4 === 'monthly') {
                                        $dateRange = explode('to', $groupOption3);
                                        $startDate = strtotime(trim($dateRange[0]));
                                        $endDate = strtotime('last day of ' . trim($dateRange[1]));
                                    
                                        $createdTimestamp = strtotime($createdate);
                                    
                                        if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                            $monthYear = date('Y-m', $createdTimestamp);
                                    
                                            if (!isset($groupedRows[$courier])) {
                                                $groupedRows[$courier] = [
                                                    'ids' => [$row['id']], 
                                                    'totalTopupAmount' => $row['total']
                                                ];
                                            } else {
                                                $groupedRows[$courier]['ids'][] = $row['id'];
                                                $groupedRows[$courier]['totalTopupAmount'] += $row['total'];
                                            }
                                        }
                                    }else if ($groupOption === 'currency' && $groupOption4 === 'yearly') {
                                        $dateRange = explode('to', $groupOption3);
                                        $startDate = strtotime('first day of January ' . trim($dateRange[0]));
                                        $endDate = strtotime('last day of December ' . trim($dateRange[1]));
                                    
                                        $createdTimestamp = strtotime($createdate);
                                    
                                        if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                            $year = date('Y', $createdTimestamp);
                                    
                                            if (!isset($groupedRows[$curr])) {
                                                $groupedRows[$curr] = [
                                                    'ids' => [$row['id']],
                                                    'totalTopupAmount' => $row['total']
                                                ];
                                            } else {
                                                $groupedRows[$curr]['ids'][] = $row['id']; 
                                                $groupedRows[$curr]['totalTopupAmount'] += $row['total'];
                                            }
                                        }
                                    }else if ($groupOption === 'courier' && $groupOption4 === 'yearly') {
                                        $dateRange = explode('to', $groupOption3);
                                        $startDate = strtotime('first day of January ' . trim($dateRange[0]));
                                        $endDate = strtotime('last day of December ' . trim($dateRange[1]));
                                    
                                        $createdTimestamp = strtotime($createdate);
                                    
                                        if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                            $year = date('Y', $createdTimestamp);
                                    
                                            if (!isset($groupedRows[$courier])) {
                                                $groupedRows[$courier] = [
                                                    'ids' => [$row['id']], 
                                                    'totalTopupAmount' => $row['total']
                                                ];
                                            } else {
                                                $groupedRows[$courier]['ids'][] = $row['id'];
                                                $groupedRows[$courier]['totalTopupAmount'] += $row['total'];
                                            }
                                        }
                                    }                         
                                    
                                }else if ($groupOption === 'currency') {
                                    generateTableRow2($row['id'],$counters, $courier, $curr,$createdate, $row['total']);
                                }else if ($groupOption === 'courier') {
                                    generateTableRow($row['id'], $counters, $courier, $createdate, $row['total']);
                                }
                            }
                            
                          
                            foreach ($groupedRows as $key => $groupedRow) {
                                if (isset($key)) {
                                    if($groupOption4 == 'daily') {
                                        if (!isset($groupedRow['displayed'])) {
                                            $groupedRow['displayed'] = true;
                                            $viewActMsg = USER_NAME . " searched the data [<b> ID = " . implode(', ', $groupedRow['ids']) . "</b> ] with the date <b>" . $createdate. "</b> from <b><i>$tblName Table</i></b>.";
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
                                $url = $groupOption4 == 'daily' ? "del_fees_claim_table_detail.php?ids=" . urlencode($ids) : "del_fees_claim_table_summary.php?ids=" . urlencode($ids);
                                echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                                echo ' <th class="text-center"><input type="checkbox" class="export" value="' . $ids . '"></th>';
                                echo '<th class="hideColumn" scope="row">' . $ids . '</th>'; 
                                echo '<th scope="row">' . $counters++ . '</th>';
                                echo '<td scope="row">' . $key . '</td>';
                                echo '<td scope="row">' . number_format($groupedRow['totalTopupAmount'], 2, '.', '') . '</td>';
                                echo '</tr>';
                            }
                        }
                       ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <?php if (!isset($_GET['group'])): ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                            <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Claim Date</th>
                            <th scope="col">Courier</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Tax</th>
                            <th scope="col">Total</th>
                            <th scope="col">Remark</th>
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th class="text-center">
                            <input type="checkbox" class="exportAll">
                            </th>
                            <th scope="col" width="60px">S/N</th>
                            <th id="group_header" scope="col"><?php echo isset($_GET['group']) && $_GET['group'] == 'courier' ? "Courier" : "Currency"; ?></th>
                            <th scope="col">Total</th>
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
<?php include "../js/del_fees_claim_table.js" ?>
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
    datatableAlignment('del_fees_claim_table');
</script>

</html>