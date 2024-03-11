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
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th id="group_header" scope="col"><?php echo isset($_GET['group']) && $_GET['group'] == 'metaaccount' ? "Meta Account" : "Invoice/Payment Date"; ?></th>
                        <th scope="col">Total Top-up Amount</th>
                        
                    </tr>
                </thead>

                 
                <tbody>
                <?php

                $groupOption = isset($_GET['group']) ? $_GET['group'] : 'metaaccount'; 
           
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
                
                    if ($groupOption && $groupOption3) {
                        if ($groupOption === 'metaaccount' && $groupOption3 === $paymentDate) {
                            if (!isset($groupedRows[$accName])) {
                                $groupedRows[$accName] = [
                                    'ids' => [$row['id']], // Store the ID in an array
                                    'totalTopupAmount' => $row['topup_amt']
                                ];
                            } else {
                                $groupedRows[$accName]['ids'][] = $row['id']; // Add ID to the array
                                $groupedRows[$accName]['totalTopupAmount'] += $row['topup_amt'];
                            }
                        }else if ($groupOption === 'invoice' && $groupOption3 === $paymentDate) {
                            if (!isset($groupedRows[$paymentDate])) {
                                $groupedRows[$paymentDate] = [
                                    'ids' => [$row['id']],
                                    'totalTopupAmount' => $row['topup_amt']
                                ];
                            } else {
                                $groupedRows[$paymentDate]['ids'][] = $row['id']; // Add ID to the array
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
                                    $groupedRows[$paymentDate]['ids'][] = $row['id']; // Add ID to the array
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
                                    $groupedRows[$accName]['ids'][] = $row['id']; // Add ID to the array
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
                                    $groupedRows[$paymentDate]['ids'][] = $row['id']; // Add ID to the array
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
                                    $groupedRows[$accName]['ids'][] = $row['id']; // Add ID to the array
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
                                    $groupedRows[$paymentDate]['ids'][] = $row['id']; // Add ID to the array
                                    $groupedRows[$paymentDate]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                        }                  
                        
                    } else {
                        generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                    }
                }
                
                // Display the grouped rows
                foreach ($groupedRows as $key => $groupedRow) {
                    $ids = implode(',', $groupedRow['ids']);
                    $url = $groupOption4 == 'daily' ? "fb_ads_topup_trans_table_detail.php?ids=$ids" : "fb_ads_topup_trans_table_summary.php?ids=$ids";
                    echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                    echo '<th class="hideColumn" scope="row">' . $ids . '</th>'; // Display IDs
                    echo '<th scope="row">' . $counters++ . '</th>';
                    echo '<td scope="row">' . $key . '</td>';
                    echo '<td scope="row">' . number_format($groupedRow['totalTopupAmount'], 2, '.', '') . '</td>';
                    echo '</tr>';
                }
                
                


                //     else if ($groupOption === 'invoice' && $groupOption2 === '' && $groupOption3 ===  $paymentDate ) {
                //         generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     }else if ($groupOption === 'invoice' && $groupOption4 === 'weekly') {
                //         $dateRange = explode('to', $groupOption3);
                //         $startDate = strtotime(trim($dateRange[0]));
                //         $endDate = strtotime(trim($dateRange[1]));
                    
                //         $paymentDateTimestamp = strtotime($paymentDate);
                    
                //         if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                //             generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //         }
                //     }else if ($groupOption === 'invoice' && $groupOption4 === 'monthly') {
                //         $dateRange = explode('to', $groupOption3);
                //         $startDate = strtotime(trim($dateRange[0] . '-01')); 
                //         $endDate = strtotime('+1 month', strtotime(trim($dateRange[1] . '-01'))) - 1; // End of the month
                    
                //         $paymentDateParts = explode('-', $paymentDate);
                //         $paymentYear = $paymentDateParts[0];
                //         $paymentMonth = $paymentDateParts[1];
                    
                    
                //         $paymentStartDate = strtotime($paymentYear . '-' . $paymentMonth . '-01');
                //         $paymentEndDate = strtotime('+1 month', $paymentStartDate) - 1;
                    
                //         if ($paymentStartDate >= $startDate && $paymentStartDate <= $endDate) {
                //             generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //         }
                //     }else if ($groupOption === 'invoice' && $groupOption4 === 'yearly') {
                //         $dateRange = explode('to', $groupOption3);
                //         $startYear = trim($dateRange[0]);
                //         $endYear = trim($dateRange[1]);
                    
                //         $paymentYear = date('Y', strtotime($paymentDate));
                    
                //         if ($paymentYear >= $startYear && $paymentYear <= $endYear) {
                //             generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //         }
                //     }
                //     else if ($groupOption === 'metaaccount' && $groupOption4 === 'weekly') {
                //         $dateRange = explode('to', $groupOption3);
                //         $startDate = strtotime(trim($dateRange[0]));
                //         $endDate = strtotime(trim($dateRange[1]));
                    
                //         $paymentDateTimestamp = strtotime($paymentDate);
                    
                //         if ($paymentDateTimestamp >= $startDate && $paymentDateTimestamp <= $endDate) {
                //             generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //             $totalTopupAmount += $row['topup_amt']; // Accumulate the topup_amt value
                //         }
                //     }else if ($groupOption === 'metaaccount' && $groupOption4 === 'monthly') {
                //         $dateRange = explode('to', $groupOption3);
                //         $startDate = strtotime(trim($dateRange[0] . '-01')); 
                //         $endDate = strtotime('+1 month', strtotime(trim($dateRange[1] . '-01'))) - 1; // End of the month
                    
                //         $paymentDateParts = explode('-', $paymentDate);
                //         $paymentYear = $paymentDateParts[0];
                //         $paymentMonth = $paymentDateParts[1];
                    
                    
                //         $paymentStartDate = strtotime($paymentYear . '-' . $paymentMonth . '-01');
                //         $paymentEndDate = strtotime('+1 month', $paymentStartDate) - 1;
                    
                //         if ($paymentStartDate >= $startDate && $paymentStartDate <= $endDate) {
                //             generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //         }
                //     }else if ($groupOption === 'metaaccount' && $groupOption4 === 'yearly') {
                //         $dateRange = explode('to', $groupOption3);
                //         $startYear = trim($dateRange[0]);
                //         $endYear = trim($dateRange[1]);
                    
                //         $paymentYear = date('Y', strtotime($paymentDate));
                    
                //         if ($paymentYear >= $startYear && $paymentYear <= $endYear) {
                //             generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //         }
                //     }
                    
                    
                // } 
                // else if($groupOption && $groupOption2 && !$groupOption3) {
                //     if ($groupOption === 'invoice' && $groupOption2 === '') {
                //          generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     } 
                //     else if ($groupOption === 'metaaccount' && $accName === $groupOption2){
                //         generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     }
                // }else if($groupOption && !$groupOption2 && $groupOption3){
                //     if  ($groupOption === 'metaaccount' && $groupOption3 === $paymentDate){
                //         generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     }
                //     else if  ($groupOption === 'invoice' && $groupOption3 === $paymentDate){
                //         generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     }
                // }else if($groupOption && !$groupOption2 && !$groupOption3){
                //     if ($groupOption === 'invoice'){
                //         generateTableRow2($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     }
                //     else if ($groupOption === 'metaaccount'){
                //         generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                //     }
                           
                // }else if(!$groupOption && !$groupOption2 && $groupOption3){
                //         generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                // }
                
                // else{
                //     generateTableRow($row['id'], $counters, $accName, $paymentDate, $row['topup_amt']);
                // }
                    
                ?>


                </tbody>
   
                <tfoot>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th id="group_header" scope="col"><?php echo isset($_GET['group']) && $_GET['group'] === 'metaaccount' ? "Meta Account" : "Invoice/Payment Date"; ?></th>
                        <th scope="col">Total Top-up Amount</th>
                        
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</body>
<script>

  

$(document).ready(function() {
  
    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    var groupParam = getParameterByName('group');
    if (groupParam) {
        $('#group').val(groupParam);
    }
  
    var timeParam3 = getParameterByName('timeInterval');
    var timeParam4 = getParameterByName('timeRange');
    var timeInterval = $('#timeInterval').val();
    if (timeParam3 == 'weekly') {
    $('#timeInterval').val(timeParam3);
    var dateRange = timeParam4.split('to');
    $('#datepicker2 input[name="start"]').val(dateRange[0]);
    $('#datepicker2 input[name="end"]').val(dateRange[1]);
    handleTimeIntervalChange();
    $('#timeRangeParam').val(timeParam4);
    $('#timeIntervalParam').val(timeParam3);
    } else if (timeParam3 == 'monthly') {
        $('#timeInterval').val(timeParam3);
        var dateRange = timeParam4.split('to');
        $('#datepicker3 input[name="start"]').val(dateRange[0]);
        $('#datepicker3 input[name="end"]').val(dateRange[1]);
        handleTimeIntervalChange();
        $('#timeRangeParam').val(timeParam4);
        $('#timeIntervalParam').val(timeParam3); 
    } else if (timeParam3 == 'yearly') {
        $('#timeInterval').val(timeParam3);
        var dateRange = timeParam4.split('to');
        $('#datepicker4 input[name="start"]').val(dateRange[0]);
        $('#datepicker4 input[name="end"]').val(dateRange[1]);
        handleTimeIntervalChange();
        $('#timeRangeParam').val(timeParam4);
        $('#timeIntervalParam').val(timeParam3);
    } else if (timeParam3 == 'daily') {
        $('#timeInterval').val('daily');
        $('#timeIntervalParam').val('daily');
        $('#timeRangeParam').val(timeParam4);
        $('#datepicker input').val(timeParam4);
        handleTimeIntervalChange();
    }
    if (timeParam3 === 'daily') {
        $('#datepicker').prop('disabled', false).show();
    } else if (timeParam3 === 'weekly') {
        $('#datepicker2').prop('disabled', false).show();
    } else if (timeParam3 === 'monthly') {
        $('#datepicker3').prop('disabled', false).show();
    } else if (timeParam3 === 'yearly') {
        $('#datepicker4').prop('disabled', false).show();
    }   

    $('#group').change(function(){
    var group = $(this).val();
   
          
          if(timeParam4){
            
              window.location.search = '?group=' + group + '&timeRange=' + timeParam4 + (timeParam3 ? '&timeInterval=' + timeParam3 : '');
          }else{
              
              window.location.search = '?group=' + group ;
          }
         
        
      
   

    });

  
   
    $('#datepicker input, #datepicker2 input, #datepicker3 input, #datepicker4 input').change(function() {
    var time =  $('#datepicker input').val();
    var timeInterval = $('#timeInterval').val();
    var startDate = $('#datepicker2 input[name="start"]').val();
    var endDate = $('#datepicker2 input[name="end"]').val();
    var startMonth = $('#datepicker3 input[name="start"]').val();
    var endMonth = $('#datepicker3 input[name="end"]').val();
    var startYear = $('#datepicker4 input[name="start"]').val();
    var endYear = $('#datepicker4 input[name="end"]').val();

    var timeRange;
    if (timeInterval === 'weekly') {
    timeRange = startDate + 'to' + endDate;
    } else if (timeInterval === 'monthly') {
        timeRange = startMonth + 'to' + endMonth;
    } else if (timeInterval === 'yearly') {
        timeRange = startYear + 'to' + endYear;
    } else if (timeInterval === 'daily') {
        timeRange = time;
    }

    

    var group = $('#group').val();


    if (group === 'metaaccount') {
      
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if (group === 'invoice'){
        
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else{
        window.location.search = (timeRange ? '?timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
    }



    });
    $('#datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 0, 
        minViewMode: 0,
        todayHighlight: true,
        toggleActive: true,
        orientation: 'bottom left',
    });


    $('#datepicker2').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 1,
        todayHighlight: true,
        toggleActive: true,
        orientation: 'bottom',
    });

        
    $('#datepicker3').datepicker({
        format: "yyyy-mm",
        minViewMode: 1,
        autoclose: true,
        orientation: 'bottom',
    });

    $('#datepicker4').datepicker({
        format: "yyyy",
        minViewMode: 2,
        autoclose: true,
        orientation: 'bottom',
    });

 
   
    function handleTimeIntervalChange() {
    var selectedOption = $('#timeInterval').val();
    $('#datepicker, #datepicker2, #datepicker3, #datepicker4').prop('disabled', true).hide();

    if (selectedOption === 'daily') {
        $('#datepicker').prop('disabled', false).show();
    } else if (selectedOption === 'weekly') {
        $('#datepicker2').prop('disabled', false).show();
    } else if (selectedOption === 'monthly') {
        $('#datepicker3').prop('disabled', false).show();
    } else if (selectedOption === 'yearly') {
        $('#datepicker4').prop('disabled', false).show();
    }
}
 
$('#timeInterval').change(handleTimeIntervalChange);
});
</script>




<script>

    
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