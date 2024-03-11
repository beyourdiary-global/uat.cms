<?php
$pageTitle = "Facebook Ads Top Up Transaction";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/fb_ads_topup_trans.php';
$result = getData('*', '', '', FB_ADS_TOPUP, $finance_connect);
$result2 = getData('*', '', '', FB_ADS_TOPUP, $finance_connect);

?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>
<script>
    $(document).ready(() => {
        createSortingTable('fb_ads_topup_trans_table');
    });
</script>

<body>

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
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Transaction </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="container">
            <div class="row mb-3">
                    <div class="col-md-4 dateFilters">
                        <label for="timeInterval" class="form-label">Filter by:</label>
                       <select class="form-select" id="timeInterval" >

                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 dateFilters">
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
                            <option value="metaaccount" selected>Meta Account</option>
                            <option value="invoice">Invoice/Payment Date</option>
                        </select>
                    </div>
                    
        
                 
                </div>
           
                <input type="hidden" id="groupParam" name="group" value="">
                <input type="hidden" id="timeIntervalParam" name="timeInterval" value="">
                <input type="hidden" id="timeRangeParam" name="timeRange" value="">
                
            
                
             
                
                
            </div>
         
            
            <table class="table table-striped" id="fb_ads_topup_trans_table">
                <thead>
                <tr>
                <?php if (!isset($_GET['group'])): ?>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col" width="60px">S/N</th>
                    <th scope="col">Meta Account</th>
                    <th scope="col">Transaction ID</th>
                    <th scope="col">Invoice/Payment Date</th>
                    <th scope="col">Person In Charge</th>
                    <th scope="col">Top-up Amount</th>
                    <th scope="col">Attachment</th>
                    <th scope="col">Remark</th>
                    <th scope="col" id="action_col">Action</th>
                    <?php else: ?>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col" width="60px">S/N</th>                       
                    <th id="group_header" scope="col"><?php echo isset($_GET['group']) && $_GET['group'] == 'metaaccount' ? "Meta Account" : "Invoice/Payment Date"; ?></th>
                    <th scope="col">Total Top-up Amount</th>
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

                function generateTableRow($id, &$counters, $accName, $paymentDate, $topupAmt) {
                    echo '<tr onclick="window.location=\'fb_ads_topup_trans_table_summary.php?ids=' . $id . '\';" style="cursor:pointer;">';
                    echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                    echo '<th scope="row">' . $counters++ . '</th>';
                    echo '<td scope="row">' . $accName . '</td>';
                    echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                    echo '</tr>';
                }
                
                $groupedRows = [];
                while ($row = $result->fetch_assoc()) {
                    $metaQuery = getData('*', "id='" . $row['meta_acc'] . "'", '', META_ADS_ACC, $finance_connect);
                    $meta_acc = $metaQuery->fetch_assoc();
                    $accName = isset($meta_acc['accName']) ? $meta_acc['accName'] : '';
                    $paymentDate = $row['payment_date'];
                    $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                    $usr = $pic->fetch_assoc();
                    if ($groupOption === '') {
                        echo '<tr>
                            <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                            <th scope="row">' . $num++ . '</th>
                            <td scope="row">' . (isset($meta_acc['accName']) ? $meta_acc['accName'] : '') . '</td>
                            <td scope="row">' . $row['transactionID'] . '</td>
                            <td scope="row">' . (isset($row['payment_date']) ? $row['payment_date'] : '') . '</td>
                            <td scope="row">' . (isset($usr['name']) ? $usr['name'] : '') . '</td>
                            <td scope="row">' . (isset($row['topup_amt']) ? $row['topup_amt'] : '') . '</td>
                            <td scope="row">' . (isset($row['attachment']) ? $row['attachment'] : '') . '</td>
                            <td scope="row">' . (isset($row['remark']) ? $row['remark'] : '') . '</td>
                            <td scope="row">
                                <div class="dropdown" style="text-align:center">
                                    <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                        <li>';
                                            if (isActionAllowed("View", $pinAccess)) {
                                                echo '<a class="dropdown-item" href="' . $redirect_page . '?id=' . $row['id'] . '">View</a>';
                                            }
                                            echo '</li>
                                        <li>';
                                            if (isActionAllowed("Edit", $pinAccess)) {
                                                echo '<a class="dropdown-item" href="' . $redirect_page . '?id=' . $row['id'] . '&act=' . $act_2 . '">Edit</a>';
                                            }
                                            echo '</li>
                                        <li>';
                                            if (isActionAllowed("Delete", $pinAccess)) {
                                                echo '<a class="dropdown-item" onclick="confirmationDialog(\'' . $row['id'] . '\',[\'' . $row['meta_acc'] . '\',\'' . $row['transactionID'] . '\'],\'' . $pageTitle . '\',\'' . $redirect_page . '\',\'' . $SITEURL . '/fb_ads_topup_trans_table.php\',\'D\')">Delete</a>';
                                            }
                                            echo '</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>';
                    }
                    if ($groupOption && $groupOption3) {
                        if ($groupOption === 'metaaccount' && $groupOption3 === $paymentDate) {
                            if (!isset($groupedRows[$accName])) {
                                $groupedRows[$accName] = [
                                    'ids' => [$row['id']], 
                                    'totalTopupAmount' => $row['topup_amt']
                                ];
                            } else {
                                $groupedRows[$accName]['ids'][] = $row['id']; 
                                $groupedRows[$accName]['totalTopupAmount'] += $row['topup_amt'];
                            }
                        }else if ($groupOption === 'invoice' && $groupOption3 === $paymentDate) {
                            if (!isset($groupedRows[$paymentDate])) {
                                $groupedRows[$paymentDate] = [
                                    'ids' => [$row['id']],
                                    'totalTopupAmount' => $row['topup_amt']
                                ];
                            } else {
                                $groupedRows[$paymentDate]['ids'][] = $row['id']; 
                                $groupedRows[$paymentDate]['totalTopupAmount'] += $row['topup_amt'];
                            }

                        }else if ($groupOption === 'invoice' && $groupOption4 === 'weekly') {
                            $dateRange = explode('to', $groupOption3);
                            $startDate = strtotime(trim($dateRange[0]));
                            $endDate = strtotime(trim($dateRange[1]));
                        
                            $paymentDateTimestamp = strtotime($paymentDate);
                        
                            if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                                if (!isset($groupedRows[$paymentDate])) {
                                    $groupedRows[$paymentDate] = [
                                        'ids' => [$row['id']],
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$paymentDate]['ids'][] = $row['id']; 
                                    $groupedRows[$paymentDate]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }else if ($groupOption === 'metaaccount' && $groupOption4 === 'weekly') {
                            $dateRange = explode('to', $groupOption3);
                            $startDate = strtotime(trim($dateRange[0]));
                            $endDate = strtotime(trim($dateRange[1]));
                        
                            $paymentDateTimestamp = strtotime($paymentDate);
                        
                            if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                                if (!isset($groupedRows[$accName])) {
                                    $groupedRows[$accName] = [
                                        'ids' => [$row['id']], 
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$accName]['ids'][] = $row['id']; 
                                    $groupedRows[$accName]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }else if ($groupOption === 'invoice' && $groupOption4 === 'monthly') {
                            $dateRange = explode('to', $groupOption3);
                            $startDate = strtotime(trim($dateRange[0]));
                            $endDate = strtotime('last day of ' . trim($dateRange[1]));
                        
                            $paymentDateTimestamp = strtotime($paymentDate);
                        
                            if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                                $monthYear = date('Y-m', $paymentDateTimestamp);
                        
                                if (!isset($groupedRows[$paymentDate])) {
                                    $groupedRows[$paymentDate] = [
                                        'ids' => [$row['id']],
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$paymentDate]['ids'][] = $row['id']; 
                                    $groupedRows[$paymentDate]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }else if ($groupOption === 'metaaccount' && $groupOption4 === 'monthly') {
                            $dateRange = explode('to', $groupOption3);
                            $startDate = strtotime(trim($dateRange[0]));
                            $endDate = strtotime('last day of ' . trim($dateRange[1]));
                        
                            $paymentDateTimestamp = strtotime($paymentDate);
                        
                            if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                                $monthYear = date('Y-m', $paymentDateTimestamp);
                        
                                if (!isset($groupedRows[$accName])) {
                                    $groupedRows[$accName] = [
                                        'ids' => [$row['id']], 
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$accName]['ids'][] = $row['id'];
                                    $groupedRows[$accName]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }else if ($groupOption === 'invoice' && $groupOption4 === 'yearly') {
                            $dateRange = explode('to', $groupOption3);
                            $startDate = strtotime('first day of January ' . trim($dateRange[0]));
                            $endDate = strtotime('last day of December ' . trim($dateRange[1]));
                        
                            $paymentDateTimestamp = strtotime($paymentDate);
                        
                            if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                                $year = date('Y', $paymentDateTimestamp);
                        
                                if (!isset($groupedRows[$paymentDate])) {
                                    $groupedRows[$paymentDate] = [
                                        'ids' => [$row['id']],
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$paymentDate]['ids'][] = $row['id']; 
                                    $groupedRows[$paymentDate]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }else if ($groupOption === 'metaaccount' && $groupOption4 === 'yearly') {
                            $dateRange = explode('to', $groupOption3);
                            $startDate = strtotime('first day of January ' . trim($dateRange[0]));
                            $endDate = strtotime('last day of December ' . trim($dateRange[1]));
                        
                            $paymentDateTimestamp = strtotime($paymentDate);
                        
                            if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                                $year = date('Y', $paymentDateTimestamp);
                        
                                if (!isset($groupedRows[$accName])) {
                                    $groupedRows[$accName] = [
                                        'ids' => [$row['id']], 
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$accName]['ids'][] = $row['id'];
                                    $groupedRows[$accName]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }                         
                        
                    }  else if ($groupOption === 'invoice') {
                        generateTableRow($row['id'],$counters, $accName, $paymentDate,  $row['topup_amt']);
                    }else if ($groupOption === 'metaaccount') {
                        generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                    }
                }
                
              
                foreach ($groupedRows as $key => $groupedRow) {

                    $ids = implode(',', $groupedRow['ids']);
                    $url = $groupOption4 == 'daily' ? "fb_ads_topup_trans_table_detail.php?ids=$ids" : "fb_ads_topup_trans_table_summary.php?ids=$ids";
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
                    <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col">Meta Account</th>
                        <th scope="col">Transaction ID</th>
                        <th scope="col">Invoice/Payment Date</th>
                        <th scope="col">Person In Charge</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Attachment</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                        <?php else: ?>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th id="group_header" scope="col"><?php echo isset($_GET['group']) && $_GET['group'] == 'metaaccount' ? "Meta Account" : "Invoice/Payment Date"; ?></th>
                        <th scope="col">Total Top-up Amount</th>
                        <?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</body>




<script>

<?php include "../js/fb_ads_topup_table.js" ?>
    
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
    datatableAlignment('fb_ads_topup_trans_table');
</script>

</html>