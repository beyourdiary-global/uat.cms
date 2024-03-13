<?php
$pageTitle = "Shopee Ads Top Up Transaction";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_ads_topup_trans.php';
$result = getData('*', '', '', SHOPEE_ADS_TOPUP, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('shopee_ads_topup_trans_table');
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
                            <option value="shopee" selected>Shopee Account</option>
                            <option value="currency">Currency</option>
                            <option value="method">Payment Method</option>
                        </select>
                    </div>
                    
        
                 
            </div>

            <table class="table table-striped" id="shopee_ads_topup_trans_table">
                <thead>
                    <tr>
                    <?php if (!isset($_GET['group'])): ?>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col">Shopee Account</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">DateTime</th>
                        <th scope="col">Currency</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">GST (%)</th>
                        <th scope="col">Payment Method</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                        <?php else: ?>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th id="group_header" scope="col">
                            <?php 
                                if (isset($_GET['group'])) {
                                    if ($_GET['group'] == 'shopee') {
                                        echo "Shopee Account";
                                    } elseif ($_GET['group'] == 'currency') {
                                        echo "Currency";
                                    } elseif ($_GET['group'] == 'method') {
                                        echo "Payment Method";
                                    }
                                }
                            ?>
                        </th>
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
     
                     function generateTableRow($id, &$counters, $key, $topupAmt) {
                         echo '<tr onclick="window.location=\'shopee_ads_topup_trans_table_summary.php?ids=' . urlencode($id) . '\';" style="cursor:pointer;">';
                         echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                         echo '<th scope="row">' . $counters++ . '</th>';
                         echo '<td scope="row">' . $key . '</td>';
                         echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                         echo '</tr>';
                     }
                   
                     $groupedRows = [];

                    while ($row = $result->fetch_assoc()) {
                        if (isset($row['orderID'], $row['id']) && !empty($row['orderID'])) {
                            $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", 'LIMIT 1', SHOPEE_ACC, $finance_connect);
                            $shopee_acc = $q1->fetch_assoc();
                            $q2 = getData('unit', "id='" . $row['currency'] . "'", 'LIMIT 1', CUR_UNIT, $connect);
                            $currs = $q2->fetch_assoc();
                            $q3 = getData('name', "id='" . $row['pay_meth'] . "'", 'LIMIT 1', FIN_PAY_METH, $finance_connect);
                            $pay = $q3->fetch_assoc();

                            $shopee = isset($shopee_acc['name']) ? $shopee_acc['name'] : '';;
                            $curr = isset($currs['unit']) ? $currs['unit'] : '';
                            $method = isset($pay['name']) ? $pay['name'] : '';
                            $paymentDate = $row['payment_date'];
                            
                        }
                        if ($groupOption == '') {
                            echo '<tr>
                        <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                        <th scope="row">' . $num++ . '</th>
                        <td scope="row">' . (isset($shopee_acc['name']) ? $shopee_acc['name'] : '') . '</td>
                        <td scope="row">' . $row['orderID'] . '</td>
                        <td scope="row">' . (isset($row['payment_date']) ? $row['payment_date'] : '') . '</td>
                        <td scope="row">' . (isset($currs['unit']) ? $currs['unit'] : '') . '</td>
                        <td scope="row">' . (isset($row['topup_amt']) ? $row['topup_amt'] : '') . '</td>
                        <td scope="row">' . (isset($row['subtotal']) ? $row['subtotal'] : '') . '</td>
                        <td scope="row">' . (isset($row['gst']) ? $row['gst'] : '') . '</td>
                        <td scope="row">' . (isset($pay['name']) ? $pay['name'] : '') . '</td>
                        <td scope="row">' . (isset($row['remark']) ? $row['remark'] : '') . '</td>
                        <td scope="row">
                            <div class="dropdown" style="text-align:center">
                                <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                    <li><?php if (isActionAllowed("View", $pinAccess)) : ?>
                                        <a class="dropdown-item" href="' . $redirect_page . '?id=' . $row['id'] . '">View</a>
                                    <?php endif; ?>
                                    </li>
                                    <li><?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                        <a class="dropdown-item" href="' . $redirect_page . '?id=' . $row['id'] . '&act=' . $act_2 . '">Edit</a>
                                    <?php endif; ?>
                                    </li>
                                    <li><?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                        <a class="dropdown-item" onclick="confirmationDialog(' . $row['id'] . ',[\'' . $row['shopee_acc'] . '\',\'' . $row['orderID'] . '\'],\'' . $pageTitle . '\',\'' . $redirect_page . '\',\'' . $SITEURL . '/shopee_ads_topup_trans_table.php\',\'D\')">Delete</a>
                                    <?php endif; ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>';
                    }
                        if ($groupOption && $groupOption3) {
                            if (($groupOption === 'shopee' || $groupOption === 'currency' || $groupOption === 'method') && $groupOption4 === 'daily') {
                                $key = $groupOption === 'shopee' ? $shopee : ($groupOption === 'currency' ? $curr : $method);
                                $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $paymentDate); 
                                $formattedDate = $dateTime->format('Y-m-d');

                                if ($groupOption3 === $formattedDate) {
                                if (!isset($groupedRows[$key])) {
                                    $groupedRows[$key] = [
                                        'ids' => [$row['id']],
                                        'totalTopupAmount' => $row['topup_amt']
                                    ];
                                } else {
                                    $groupedRows[$key]['ids'][] = $row['id'];
                                    $groupedRows[$key]['totalTopupAmount'] += $row['topup_amt'];
                                }
                            }
                            }
                            else if (($groupOption === 'shopee' || $groupOption === 'currency' || $groupOption === 'method') && $groupOption4) {
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

                               
                                $createdTimestamp = strtotime($paymentDate);
                            
                                if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                    $key = $groupOption === 'shopee' ? $shopee : ($groupOption === 'currency' ? $curr : $method);
                            
                                    if (!isset($groupedRows[$key])) {
                                        $groupedRows[$key] = [
                                            'ids' => [$row['id']],
                                            'totalTopupAmount' => $row['topup_amt']
                                        ];
                                    } else {
                                        $groupedRows[$key]['ids'][] = $row['id'];
                                        $groupedRows[$key]['totalTopupAmount'] += $row['topup_amt'];
                                    }
                                }
                            }
                                                   
                            
                        }else if ($groupOption === 'currency') {
                            generateTableRow($row['id'],$counters, $curr, $row['topup_amt']);
                        }else if ($groupOption === 'shopee') {
                            generateTableRow($row['id'], $counters, $shopee, $row['topup_amt']);
                        }else if ($groupOption === 'method') {
                            generateTableRow($row['id'], $counters, $method, $row['topup_amt']);
                        }
                        }
                        foreach ($groupedRows as $key => $groupedRow) {
            
                            $ids = implode(',', $groupedRow['ids']);
                            $url = $groupOption4 == 'daily' ? "shopee_ads_topup_trans_table_detail.php?ids=" . urlencode($ids) : "shopee_ads_topup_trans_table_summary.php?ids=" . urlencode($ids);
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
                        <th scope="col">Shopee Account</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">DateTime</th>
                        <th scope="col">Currency</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">GST (%)</th>
                        <th scope="col">Payment Method</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                        <?php else: ?>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>                       
                        <th id="group_header" scope="col">
                            <?php 
                                if (isset($_GET['group'])) {
                                    if ($_GET['group'] == 'shopee') {
                                        echo "Shopee Account";
                                    } elseif ($_GET['group'] == 'currency') {
                                        echo "Currency";
                                    } elseif ($_GET['group'] == 'method') {
                                        echo "Payment Method";
                                    }
                                }
                            ?>
                        </th>
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
    datatableAlignment('shopee_ads_topup_trans_table');
</script>

</html>