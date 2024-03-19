<?php
$pageTitle = "Internal Consume Item";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/internal_consume_item.php';
$result = getData('*', '', '', ITL_CSM_ITEM, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('internal_consume_item_table');
    });
</script>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
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
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Item </a>
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
                            <option value="person" selected>Person-In-Charge</option>
                            <option value="brand">Brand</option>
                            <option value="package">Package</option>
                        </select>
                    </div>
                    
        
                 
                </div>
                <table class="table table-striped" id="internal_consume_item_table">
                    <thead>
                        <tr>
                        <?php if (!isset($_GET['group'])): ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">Date</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                            <th scope="col">Cost</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>                       
                            <th id="group_header" scope="col">
                                <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'person') {
                                            echo "Person-In-Charge";
                                        } elseif ($_GET['group'] == 'package') {
                                            echo "Package";
                                        } elseif ($_GET['group'] == 'brand') {
                                            echo "Brand";
                                        }
                                    }
                                ?>
                            </th>
                            <th scope="col">Total Cost</th>
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
                             echo '<tr onclick="window.location=\'internal_consume_item_table_summary.php?ids=' . urlencode($id) . '\';" style="cursor:pointer;">';
                             echo '<th class="hideColumn" scope="row">' . $id . '</th>';
                             echo '<th scope="row">' . $counters++ . '</th>';
                             echo '<td scope="row">' . $key . '</td>';
                             echo '<td scope="row">' . number_format($topupAmt, 2, '.', '') . '</td>';
                             echo '</tr>';
                         }
                       
                         $groupedRows = [];
                         while ($row = $result->fetch_assoc()) {
                            if (isset($row['id']) && !empty($row['id'])) {

                                $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                                $usr = $pic->fetch_assoc();

                                $brands = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                                $row2 = $brands->fetch_assoc();

                                $packages = getData('*', "id='" . $row['package'] . "'", '', PKG, $connect);
                                $row3 = $packages->fetch_assoc();

                                $person = isset($usr['name']) ? $usr['name'] : '';;
                                $brand = isset($row2['name']) ? $row2['name'] : '';
                                $package = isset($row3['name']) ? $row3['name'] : '';
                                $createdate = $row['date'];

                                if ($groupOption == '') {
                                    echo '<tr>
                                    <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                                    <th scope="row">' . $num++ . '</th>
                                    <td scope="row">' . (isset($row['date']) ? $row['date'] : '') . '</td>
                                    <td scope="row">' . (isset($usr['name']) ? $usr['name'] : '') . '</td>
                                    <td scope="row">' . (isset($row2['name']) ? $row2['name'] : '') . '</td>
                                    <td scope="row">' . (isset($row3['name']) ? $row3['name'] : '') . '</td>
                                    <td scope="row">' . (isset($row['cost']) ? $row['cost'] : '') . '</td>
                                    <td scope="row">' . (isset($row['remark']) ? $row['remark'] : '') . '</td>
                                    <td scope="row">
                                        <div class="dropdown" style="text-align:center">
                                            <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                                <li>' . (isActionAllowed("View", $pinAccess) ? '<a class="dropdown-item" href="' . $redirect_page . '?id=' . $row['id'] . '">View</a>' : '') . '</li>
                                                <li>' . (isActionAllowed("Edit", $pinAccess) ? '<a class="dropdown-item" href="' . $redirect_page . '?id=' . $row['id'] . '&act=' . $act_2 . '">Edit</a>' : '') . '</li>
                                                <li>' . (isActionAllowed("Delete", $pinAccess) ? '<a class="dropdown-item" onclick="confirmationDialog(\'' . $row['id'] . '\',[\'\',\'\'],\'' . $pageTitle . '\',\'' . $redirect_page . '\',\'' . $SITEURL . '/internal_consume_item_table.php\',\'D\')">Delete</a>' : '') . '</li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>';
                                
                                }
                                if ($groupOption && $groupOption3) {
                                    if (($groupOption === 'person' || $groupOption === 'brand' || $groupOption === 'package') && $groupOption4 === 'daily') {
                                        $key = $groupOption === 'person' ? $person : ($groupOption === 'brand' ? $brand : $package);
                                
                                        if ($groupOption3 === $createdate) {
                                        if (!isset($groupedRows[$key])) {
                                            $groupedRows[$key] = [
                                                'ids' => [$row['id']],
                                                'totalTopupAmount' => $row['cost']
                                            ];
                                        } else {
                                            $groupedRows[$key]['ids'][] = $row['id'];
                                            $groupedRows[$key]['totalTopupAmount'] += $row['cost'];
                                        }
                                    }
                                    }
                                    else if (($groupOption === 'person' || $groupOption === 'brand' || $groupOption === 'package') && $groupOption4 !== 'daily') {
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
                                            $key = $groupOption === 'person' ? $person : ($groupOption === 'brand' ? $brand : $package);
                                    
                                            if (!isset($groupedRows[$key])) {
                                                $groupedRows[$key] = [
                                                    'ids' => [$row['id']],
                                                    'totalTopupAmount' => $row['cost']
                                                ];
                                            } else {
                                                $groupedRows[$key]['ids'][] = $row['id'];
                                                $groupedRows[$key]['totalTopupAmount'] += $row['cost'];
                                            }
                                        }
                                    }
                                }                    
                                    
                                }else if ($groupOption === 'brand') {
                                    generateTableRow($row['id'],$counters, $brand, $row['cost']);
                                }else if ($groupOption === 'person') {
                                    generateTableRow($row['id'], $counters, $person, $row['cost']);
                                }else if ($groupOption === 'package') {
                                    generateTableRow($row['id'], $counters, $package, $row['cost']);
                                }
                                }
                                foreach ($groupedRows as $key => $groupedRow) {
                    
                                    $ids = implode(',', $groupedRow['ids']);
                                    $url = $groupOption4 == 'daily' ? "internal_consume_item_table_detail.php?ids=" . urlencode($ids) : "internal_consume_item_table_summary.php?ids=" . urlencode($ids);
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
                            <th scope="col">S/N</th>
                            <th scope="col">Date</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                            <th scope="col">Cost</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <?php else: ?>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>                       
                            <th id="group_header" scope="col">
                                <?php 
                                    if (isset($_GET['group'])) {
                                        if ($_GET['group'] == 'person') {
                                            echo "Person-In-Charge";
                                        } elseif ($_GET['group'] == 'brand') {
                                            echo "Brand";
                                        } elseif ($_GET['group'] == 'package') {
                                            echo "Package";
                                        }
                                    }
                                ?>
                            </th>
                            <th scope="col">Total Cost</th>
                        <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
<script>
<?php include "../js/fb_ads_topup_table.js" ?>


    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
    /* function(void) : to solve the issue of dropdown menu displaying inside the table when table class include table-responsive */
    dropdownMenuDispFix();
    /* function(id): to resize table with bootstrap 5 classes */
    datatableAlignment('internal_consume_item_table');
    setButtonColor();
</script>

</html>