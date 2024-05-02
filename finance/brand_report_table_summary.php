<?php
$pageTitle = "Brand Report";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering
$deleteRedirectPage = $SITEURL . 'finance/brand_report_table.php';
$result = getData('*', '', '', LAZADA_ORDER_REQ, $connect);
$result2 = getData('*', '', '', FB_ORDER_REQ,$finance_connect
);
$result3 = getData('*', '', '', WEB_ORDER_REQ ,$finance_connect);
$result4 = getData('*', '', '', SHOPEE_SG_ORDER_REQ ,$finance_connect);
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
                <?php echo $pageTitle ?> Summary
            </p>
        </div>

        <div class="row">
            <div class="col-12 d-flex justify-content-between flex-wrap">
                <h2>
                    <?php echo $pageTitle ?> Summary
                </h2>
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
                       <select class="form-select" id="timeInterval" disabled>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 dateFilters">
                        <label for="dateFilter" class="form-label">Filter by Date:</label>
                        <div class="input-group date" id="datepicker"> 
                        <input type="text" class="form-control" placeholder="Select date" autocomplete="off" disabled>
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
                            <option value="platform" >Platform</option>
                        </select>
                        <select class="form-select" id="group2">
                            <option  value=""selected>Select a Group</option>
                            <option value="brand">Brand</option>
                            <option value="platform" >Platform</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <a id='resetButton' href="../reset.php?redirect=/finance/brand_report_table.php" class="btn btn-sm btn-rounded btn-primary"> <i class="fa fa-refresh"></i> Reset </a>
                    </div>
                </div>
        <table class="table table-striped" id="package_report_table">
            <thead>
                <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col">S/N</th>
                    <th id="group_header" scope="col">
                        <?php
                        if (isset($_GET['group'])) {
                            if ($_GET['group'] == 'brand') {
                                echo "Brand";
                            }else if ($_GET['group'] == 'platform') {
                                echo "Platform";
                            }
                        }
                        ?>
                    </th>
                    <?php
                       if (isset($_GET['group2']) && $_GET['group2'] != '') {
                        echo '<th id="group_header" scope="col">';
                        if ($_GET['group2'] == 'brand') {
                            echo "Brand";
                        }else if ($_GET['group2'] == 'platform') {
                            echo "Platform";
                        } echo '</th>';
                    }
                    ?>
                    <th scope="col">Total Amount</th>
                </tr>
            </thead>
           
            <tbody>
                <?php
                    if(isset($_GET['key'])){
                        $urlkey = $_GET['key'];
                    }
                   $groupOption = isset($_GET['group']) ? $_GET['group'] : '';
                   $groupOption2 = isset($_GET['group2']) ? $_GET['group2'] : ''; 
                   $groupOption3 = isset($_GET['timeRange']) ? $_GET['timeRange'] : ''; 
                   $groupOption4 = isset($_GET['timeInterval']) ? $_GET['timeInterval'] : ''; 
                   $groupedRows = [];
                   $counters = 1;
                  $resultSets = array();

                  if ($result) {
                      $resultSets['result'] = $result;
                  }
                  
                  if ($result2) {
                      $resultSets['result2'] = $result2;
                  }
                  
                  if ($result3) {
                      $resultSets['result3'] = $result3;
                  }
                  
                  if ($result4) {
                      $resultSets['result4'] = $result4;
                  }
                

                     // Process data from all result sets using four while loops grouped together
                     foreach ($resultSets as $resultSetKey => $resultSet) {
                        while ($row = $resultSet->fetch_assoc()) {

                            $channel = '';
                            $pic = '';
                          
                            switch ($resultSetKey) {
                                case 'result':
                                    $channel = 'Lazada'; 
                                    $tblName = LAZADA_ORDER_REQ;
                                    
                                    break;
                                case 'result2':
                                    $channel = 'Facebook'; 
                                    $tblName = FB_ORDER_REQ;
                                    break;            
                                case 'result3':
                                    $channel = 'Web'; 
                                    $tblName = WEB_ORDER_REQ;
                                    break;
                                case 'result4':
                                    $channel = 'Shopee'; 
                                    $tblName = SHOPEE_SG_ORDER_REQ;
                                    break;
                                default:
                                $channel = ''; 
                                    break;
                            }

                            $channelname = $channel;
                            
                            if (isset($row['brand'])) {
                                $brand = isset($row['brand']) ? $row['brand'] : '';
                                $q1 = getData('name', "id='" . $brand . "'", '', BRAND, $connect);
                                $brd_fetch = $q1->fetch_assoc();
                                $brd_name = isset($brd_fetch['name']) ? $brd_fetch['name'] : '';
                            } 
                            
                            if (isset($row['create_date']) || isset($row['date'] )) {
                                $createdate = isset($row['date']) ? $row['date'] : $row['create_date'];
                            }

                            if ($groupOption && $groupOption3) {
                                switch ($groupOption) {
                                    case 'brand':
                                        $key =  $brd_name;
                                        break;
                                    case 'platform':
                                        $key = $channelname;
                                        break;
                                    default:
                                        $key = $brd_name;
                                        break;
                                }
                                switch ($groupOption2) {
                                    case 'brand':
                                        $key2 =  $brd_name;
                                        break;
                                    case 'platform':
                                        $key2 = $channelname;
                                        break;
                                    default:
                                        $key2 = null;
                                        break;
                                }
                                  if (($groupOption === 'brand' || $groupOption === 'platform') && $groupOption4 === 'daily') {
                                  
                            
                                    if ($groupOption3 === $createdate) {
                                        if ((isset($_GET['ids']))) {
                                            $ids = explode(',', $_GET['ids']); 
                                        
                                                if ( $key == $urlkey) {
                                                    
                                                  if($groupOption2 == null){
                                                    $combinedKey = $key . '_' . $key2;
                                                    if (!isset($groupedRows[$key])) {
                                                        $groupedRows[$key] = [
                                                            'ids' => [$row['id']],
                                                            'totalTopupAmount' => isset($row['price']) ? $row['price'] : $row['final_income'],
                                                            'channel' => $channelname
                                                        ];
                                                    } else {
                                                        $groupedRows[$key]['ids'][] = $row['id'];
                                                        $groupedRows[$key]['totalTopupAmount'] += isset($row['price']) ? $row['price'] : $row['final_income'];
                                                        $groupedRows[$key]['channel'] = $channelname;
                                                    }
                                                    }
                                                    else if($groupOption2 != ''){
                                                     
                                                        $combinedKey = $key . '_' . $key2;
                                                      
                                                        if (!isset($groupedRows[$combinedKey])) {
                                                            $groupedRows[$combinedKey] = [
                                                                'ids' => [$row['id']],
                                                                'totalTopupAmount' => isset($row['price']) ? $row['price'] : $row['final_income'],
                                                                'channel' => $channelname
                                                            ];
                                                        } else {
                                                            $groupedRows[$combinedKey]['ids'][] = $row['id'];
                                                            $groupedRows[$combinedKey]['totalTopupAmount'] += isset($row['price']) ? $row['price'] : $row['final_income'];
                                                            $groupedRows[$combinedKey]['channel'] = $channelname;
                                                        }
        
                                                }
                                                
                                                }
                                              }
                                    }
                                }
                                else if (($groupOption === 'brand' || $groupOption === 'platform' ) && $groupOption4 !== 'daily') {
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
                                
                                        if ((isset($_GET['ids']))) {
                                            $ids = explode(',', $_GET['ids']); 
                                        
                                                if ( $key == $urlkey) {
                                                    
                                                  if($groupOption2 == null){
                                                    $combinedKey = $key . '_' . $key2;
                                                    if (!isset($groupedRows[$key])) {
                                                        $groupedRows[$key] = [
                                                            'ids' => [$row['id']],
                                                            'totalTopupAmount' => isset($row['price']) ? $row['price'] : $row['final_income'],
                                                            'channel' => $channelname
                                                        ];
                                                    } else {
                                                        $groupedRows[$key]['ids'][] = $row['id'];
                                                        $groupedRows[$key]['totalTopupAmount'] += isset($row['price']) ? $row['price'] : $row['final_income'];
                                                        $groupedRows[$key]['channel'] = $channelname;
                                                    }
                                                    }
                                                    else if($groupOption2 != ''){
                                                     
                                                        $combinedKey = $key . '_' . $key2;
                                                      
                                                        if (!isset($groupedRows[$combinedKey])) {
                                                            $groupedRows[$combinedKey] = [
                                                                'ids' => [$row['id']],
                                                                'totalTopupAmount' => isset($row['price']) ? $row['price'] : $row['final_income'],
                                                                'channel' => $channelname
                                                            ];
                                                        } else {
                                                            $groupedRows[$combinedKey]['ids'][] = $row['id'];
                                                            $groupedRows[$combinedKey]['totalTopupAmount'] += isset($row['price']) ? $row['price'] : $row['final_income'];
                                                            $groupedRows[$combinedKey]['channel'] = $channelname;
                                                        }
        
                                                }
                                                
                                                }
                                              }
                                    }
                                }
                            }                      
                            }}
                            foreach ($groupedRows as $combinedKey => $groupedRow) {
                                if($key2 != ''){
                                    list($key, $key2) = explode('_', $combinedKey);
                                }
                                if (isset($key)) {
                                    $ids = implode(',', $groupedRow['ids']);
                             
                                    if ($groupedRow['channel'] == 'Shopee'){
                                     
                                        $tblName = SHOPEE_SG_ORDER_REQ;
                                    }else if  ($groupedRow['channel'] == 'Facebook'){
                                       
                                        $tblName = FB_ORDER_REQ;
                                    }else if  ($groupedRow['channel'] == 'Web'){
                                        
                                        $tblName = WEB_ORDER_REQ;
                                    }else if  ($groupedRow['channel'] == 'Lazada'){
                                      
                                        $tblName = LAZADA_ORDER_REQ;
                                    }
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
                                    if ($groupedRow['channel'] == 'Shopee'){
                                        $url = "shopee_order_req_income_table_detail.php?ids=" . urlencode($ids);
                                        $tblName = SHOPEE_SG_ORDER_REQ;
                                    }else if  ($groupedRow['channel'] == 'Facebook'){
                                        $url = "fb_order_req_income_table_detail.php?ids=" . urlencode($ids);
                                        $tblName = FB_ORDER_REQ;
                                    }else if  ($groupedRow['channel'] == 'Web'){
                                        $url = "website_order_request_income_table_detail.php?ids=" . urlencode($ids);
                                        $tblName = WEB_ORDER_REQ;
                                    }else if  ($groupedRow['channel'] == 'Lazada'){
                                        $url = "../lazada_order_req_income_table_detail.php?ids=" . urlencode($ids);
                                        $tblName = LAZADA_ORDER_REQ;
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
                                if($key2 != $key && $key2 != null){
                                    echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                                }else if ($groupOption2 != null && $key2 == null) {
                                    echo '<tr>';
                                }
                                if (!empty($groupOption)) {
                                    $url .= "&group=" . urlencode($groupOption) ."&group2=";
                                }
                                if (!empty($groupOption3)) {
                                    $url .= "&timeRange=" . urlencode($groupOption3);
                                }
                                if (!empty($groupOption4)) {
                                    $url .= "&timeInterval=" . urlencode($groupOption4);
                                }
                              
                                echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                                echo '<th class="hideColumn" scope="row">' . $ids . '</th>'; 
                                echo '<th scope="row">' . $counters++ . '</th>';
                                if(isset($_GET['key'])){
                                    if($key != $urlkey){
                                        echo '<td scope="row">' . $urlkey . '</td>';
                                    }else{
                                        echo '<td scope="row">' . $key . '</td>';
                                    }
                                }else{
                                    echo '<td scope="row">' . $key . '</td>';
                                }
                                
                                if($key2 != $key && $key2 != null){
                                    echo '<td scope="row">' . $key2 . '</td>';
                                }else if ($groupOption2 != null && $key2 == null) {
                                    echo '<td scope="row"></td>';
                                }
                                echo '<td scope="row">' . number_format($groupedRow['totalTopupAmount'], 2, '.', '') . '</td>';
                                echo '</tr>';
                            }  
                            ?>

                        <?php } ?>
                    </tbody>
          
            <tfoot>
            <tr>
                    <th class="hideColumn" scope="col">ID</th>
                    <th scope="col">S/N</th>
                    <th id="group_header" scope="col">
                        <?php
                        if (isset($_GET['group'])) {
                            if ($_GET['group'] == 'brand') {
                                echo "Brand";
                            }else if ($_GET['group'] == 'platform') {
                                echo "Platform";
                            }
                        }
                        ?>
                    </th>
                    <?php
                       if (isset($_GET['group2']) && $_GET['group2'] != '') {
                        echo '<th id="group_header" scope="col">';
                        if ($_GET['group2'] == 'brand') {
                            echo "Brand";
                        }else if ($_GET['group2'] == 'platform') {
                            echo "Platform";
                        } echo '</th>';
                    }
                    ?>
                    <th scope="col">Total Amount</th>
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
datatableAlignment('lazada_order_req');
</script>

</html>