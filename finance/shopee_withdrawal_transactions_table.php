<?php
$pageTitle = "Shopee Withdrawal Transactions";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/shopee_withdrawal_transactions.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_withdrawal_transactions_table.php';
$result = getData('*', '', '', SHOPEE_WDL_TRANS, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
      <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('swt_table');
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
                            <option value="currency" selected>Currency</option>
                            <option value="person" >Person In Charge</option>
                            <option value="currencynperson">Currency Unit & Person In Charge</option>
                        </select>
                    </div>
                <table class="table table-striped" id="swt_table">
                    <thead>
                        <tr>
                        <?php if (!isset($_GET['group'])): ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Withdrawal Date</th>
                            <th scope="col">Withdrawal ID</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Withdrawal Amount</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Attachment</th>
                            <th scope="col">Remark</th> 
                            
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th id="group_header" scope="col"> 
                                <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'currency') {
                                            echo "Currency Unit";
                                        }elseif ($_GET['group'] == 'person') {
                                            echo "Person In Charge";
                                        }elseif ($_GET['group'] == 'currencynperson') {
                                            echo "Currency Unit";
                                        }
                                    }
                                ?>
                                </th>
                                <?php
                                if ($_GET['group'] == 'currencynperson') {
                                    echo '<th id="person_header" scope="col">Person In Charge</th>';
                                }else{
                                    echo '<th class="hideColumn" id="person_header" scope="col"></th>';
                                }
                                ?>
                            <th scope="col">Total Withdrawal Amount</th>
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
                            echo '<tr onclick="window.location=\'shopee_withdrawal_transactions_table_summary.php?ids=' . urlencode($id) . '\';" style="cursor:pointer;">';
                            echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                            echo '<th scope="row">' . $counters++ . '</th>';
                            foreach ($key as $k) {
                                echo '<td scope="row">' . $k . '</td>';
                            }
                            echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                            echo '</tr>';
                        }
                        
                       
                         $groupedRows = [];
                        
                        while ($row = $result->fetch_assoc()) {    
                            if (isset($row['id']) && !empty($row['id'])) {
                           $currency = getData('unit', "id='" . $row['currency_unit'] . "'", '', CUR_UNIT, $connect);
                           $row2 = $currency->fetch_assoc();

                           $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                           $usr = $pic->fetch_assoc();

                           $person = isset($usr['name']) ? $usr['name'] : '';;
                           $curr = isset($row2['unit']) ? $row2['unit'] : '';
                           $createdate = $row['date'];

                           if ($groupOption == '') {
                                echo '<tr>
                                <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                                <th scope="row">' . $num++ . '</th>
                                <td scope="row" class="btn-container">
                                <div class="d-flex align-items-center">' 
                                ?>
                                    <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess);?>
                                    <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2) ?>
                                    <?php renderDeleteButton($pinAccess, $row['id'], $row['swt_id'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage) ?>
                                <?php echo'</div>
                                </td>
                                <td scope="row">' . (isset($row['date']) ? $row['date'] : '') . '</td>
                                <td scope="row">' . (isset($row['swt_id']) ? $row['swt_id'] : '') . '</td>
                                <td scope="row">' . (isset($row2['unit']) ? $row2['unit'] : '') . '</td>
                                <td scope="row">' . (isset($row['amount']) ? $row['amount'] : '') . '</td>
                                <td scope="row">' . (isset($usr['name']) ? $usr['name'] : '') . '</td>
                                <td scope="row">' . (isset($row['attachment']) ? $row['attachment'] : '') . '</td>
                                <td scope="row">' . (isset($row['remark']) ? $row['remark'] : '') . '</td>
                               
                            </tr>';
                        
                           }
                            if ($groupOption && $groupOption3) {
                                if (($groupOption === 'person' || $groupOption === 'currency' || $groupOption === 'currencynperson') && $groupOption4 === 'daily') {
                                    $key = '';
                                    if ($groupOption === 'person') {
                                        $key = $person;
                                    } elseif ($groupOption === 'currency') {
                                        $key = $curr;
                                    } elseif ($groupOption === 'currencynperson') {
                                        $key = $person . '-' . $curr; 
                                    }
                                
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
                                
                                else if (($groupOption === 'person' || $groupOption === 'currency' || $groupOption === 'currencynperson') && $groupOption4 !== 'daily') {
                                    $dateRange = explode('to', $groupOption3);
                                    if ($groupOption4 == 'weekly') {
                                        $startDate = strtotime(trim($dateRange[0]));
                                        $endDate = strtotime(trim($dateRange[1]));
                                    } else if ($groupOption4 == 'monthly') {
                                        $startDate = strtotime(trim($dateRange[0]));
                                        $endDate = strtotime('last day of ' . trim($dateRange[1]));
                                    } else if ($groupOption4 == 'yearly') {
                                        $startDate = strtotime('first day of January ' . trim($dateRange[0]));
                                        $endDate = strtotime('last day of December ' . trim($dateRange[1]));
                                    }
                                
                                    $createdTimestamp = strtotime($createdate);
                                
                                    if ($createdTimestamp >= $startDate && $createdTimestamp <= $endDate) {
                                        $key = '';
                                        if ($groupOption === 'person') {
                                            $key = $person;
                                        } elseif ($groupOption === 'currency') {
                                            $key = $curr;
                                        } elseif ($groupOption === 'currencynperson') {
                                            $key = $person . '-' . $curr; 
                                        }
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
                                
                            }else if ($groupOption === 'currencynperson') {
                                generateTableRow($row['id'], $counters, [$currency, $person], $row['amount']);
                            }else if ($groupOption === 'person') {
                                generateTableRow($row['id'], $counters, $person, $row['amount']);
                            }else if ($groupOption === 'currency') {
                                generateTableRow($row['id'], $counters, $curr, $row['amount']);
                            }
                            }
                            foreach ($groupedRows as $key => $groupedRow) {  
                                $ids = implode(',', $groupedRow['ids']);  
                                $url = $groupOption4 == 'daily' ? "shopee_withdrawal_transactions_table_detail.php?ids=" . urlencode($ids) : "shopee_withdrawal_transactions_table_summary.php?ids=" . urlencode($ids);
                              
                                if ($groupOption === 'currencynperson') {                
                                    list($currency, $person) = explode('-', $key);
                                    echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                                    echo '<th class="hideColumn" scope="row">' . implode(',', $groupedRow['ids']) . '</th>';
                                    echo '<th scope="row">' . $counters++ . '</th>';
                                    echo '<td scope="row">' . $currency . '</td>'; 
                                    echo '<td scope="row">' . $person . '</td>';   
                                    echo '<td scope="row">' . number_format($groupedRow['totalTopupAmount'], 2, '.', '') . '</td>';
                                    echo '</tr>';
                                } elseif ($groupOption === 'person' || $groupOption === 'currency'){
                                    $ids = implode(',', $groupedRow['ids']);
                                    $url = $groupOption4 == 'daily' ? "shopee_withdrawal_transactions_table_detail.php?ids=" . urlencode($ids) : "shopee_withdrawal_transactions_table_summary.php?ids=" . urlencode($ids);
                                    echo "<tr onclick=\"window.location='$url'\" style=\"cursor:pointer;\">";
                                    echo '<th class="hideColumn" scope="row">' . $ids . '</th>';
                                    echo '<th scope="row">' . $counters++ . '</th>';
                                    echo '<td scope="row">' . $key . '</td>';
                                    echo '<td class="hideColumn" scope="row"></td>';
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
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Withdrawal Date</th>
                            <th scope="col">Withdrawal ID</th>
                            <th scope="col">Currency Unit</th>
                            <th scope="col">Withdrawal Amount</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Attachment</th>
                            <th scope="col">Remark</th> 
                           
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th id="group_header" scope="col"> 
                                <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'currency') {
                                            echo "Currency Unit";
                                        }elseif ($_GET['group'] == 'person') {
                                            echo "Person In Charge";
                                        }elseif ($_GET['group'] == 'currencynperson') {
                                            echo "Currency Unit";
                                        }
                                    }
                                ?>
                                </th>
                                <?php
                                if ($_GET['group'] == 'currencynperson') {
                                    echo '<th id="person_header" scope="col">Person In Charge</th>';
                                }else{
                                    echo '<th class="hideColumn" id="person_header" scope="col"></th>';
                                }
                                ?>
                            <th scope="col">Total Withdrawal Amount</th>
                            <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        </body>

    <script>
        <?php include "../js/fb_ads_topup_table.js" ?>
        //to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
        dropdownMenuDispFix();
        //to resize table with bootstrap 5 classes
        datatableAlignment('swt_table');
        setButtonColor();
    </script>

</body>

</html>