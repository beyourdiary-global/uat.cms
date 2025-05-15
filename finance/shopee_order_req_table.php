<?php
$pageTitle = "Shopee Order Request";
$isFinance = 1;
$currentPagePin = 86;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';
include ROOT.'/include/access.php';

$num = $default_currency_id = 1; 
$verifyMessage = '';

if (isset($_GET['verify_id'])) {
    $orderId = intval($_GET['verify_id']);

    // Step 1: Check current order status
    $checkSql = "SELECT order_status FROM " . SHOPEE_SG_ORDER_REQ . " WHERE id = $orderId";
    $checkResult = mysqli_query($finance_connect, $checkSql);

    if ($checkResult && $row = mysqli_fetch_assoc($checkResult)) {
        if ($row['order_status'] === 'OC') {
            // Step 2: Update to 'C'
            $updateSql = "UPDATE " . SHOPEE_SG_ORDER_REQ . " SET order_status = 'C' WHERE id = $orderId";
            $updateResult = mysqli_query($finance_connect, $updateSql);

            if ($updateResult) {
                // AUDIT LOG
                $auditData = array(
                    'log_act'     => 'edit',
                    'page'        => 'Order Verification',
                    'query_rec'   => $orderId,
                    'query_table' => SHOPEE_SG_ORDER_REQ,
                    'oldval'      => 'order_status: OC',
                    'changes'     => 'order_status: C',
                    'uid'         => $_SESSION['user_id'], // Assuming user ID is stored in session
                    'act_msg'     => "Verified order #$orderId (status changed from OC to C)",
                    'cdate'       => date('Y-m-d'),
                    'ctime'       => date('H:i:s'),
                    'cby'         => $_SESSION['username'] // Assuming username is stored in session
                );
                audit_log($auditData);
                $verifyMessage = "✅ Order #$orderId has been successfully verified.";
            } else {
                $verifyMessage = "❌ Failed to update order #$orderId.";
            }
        } else {
            $verifyMessage = "⚠️ Order #$orderId is not in 'OC' status.";
        }
    } else {
        $verifyMessage = "❌ Order #$orderId not found.";
    }
}

$monthFilter = isset($_GET['month']) && $_GET['month'] !== '' 
    ? ($_GET['month'] !=='All'?$_GET['month']:"") 
    : date('Y-m');

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$brandFilter = isset($_GET['brand']) ? $_GET['brand'] : '';
$pkgFilter = isset($_GET['pkg']) ? $_GET['pkg'] : '';
$accFilter = isset($_GET['acc']) ? $_GET['acc'] : '';


$whereConditions = [];

if (!empty($monthFilter)) {
    $whereConditions[] = "DATE_FORMAT(date, '%Y-%m') = '" . mysqli_real_escape_string($finance_connect, $monthFilter) . "'";
}

if (!empty($statusFilter)) {
    $whereConditions[] = "order_status = '" . mysqli_real_escape_string($finance_connect, $statusFilter) . "'";
}
if (!empty($brandFilter)) {
    $whereConditions[] = "brand = '" . mysqli_real_escape_string($finance_connect, $brandFilter) . "'";
}

if (!empty($pkgFilter)) {
    $whereConditions[] = "package = '" . mysqli_real_escape_string($finance_connect, $pkgFilter) . "'";
}

if (!empty($accFilter)) {
    $whereConditions[] = "shopee_acc = '" . mysqli_real_escape_string($finance_connect, $accFilter) . "'";
}


// Group by selections
$monthGroup = isset($_GET['month_gb']) ? $_GET['month_gb'] : '';
$statusGroup = isset($_GET['status_gb']) ? $_GET['status_gb'] : '';
$brandGroup = isset($_GET['brand_gb']) ? $_GET['brand_gb'] : '';
$pkgGroup = isset($_GET['pkg_gb']) ? $_GET['pkg_gb'] : '';
$accGroup = isset($_GET['acc_gb']) ? $_GET['acc_gb'] : '';
$groupByFields = [];

if (!empty($monthGroup) && $monthGroup !== 'All') {
    $groupByFields[] = "DATE_FORMAT(date, '%Y-%m')";
}
if (!empty($statusGroup)) {
    $groupByFields[] = "order_status";
}
if (!empty($brandGroup)) {
    $groupByFields[] = "brand";
}
if (!empty($pkgGroup)) {
    $groupByFields[] = "package";
}
if (!empty($accGroup)) {
    $groupByFields[] = "shopee_acc";
}

$groupBySql = !empty($groupByFields) ? "GROUP BY " . implode(", ", $groupByFields) : "";

$groupByFields = [];

if (!empty($monthGroup) && $monthGroup !== 'All') {
    $groupByFields[] = "DATE_FORMAT(date, '%Y-%m')";
}
if (!empty($statusGroup)) {
    $groupByFields[] = "order_status";
}
if (!empty($brandGroup)) {
    $groupByFields[] = "brand";
}
if (!empty($pkgGroup)) {
    $groupByFields[] = "package";
}
if (!empty($accGroup)) {
    $groupByFields[] = "shopee_acc";
}

$groupBySql = !empty($groupByFields) ? "GROUP BY " . implode(", ", $groupByFields) : "";


$whereSql = implode(" AND ", $whereConditions);

$redirect_page = $SITEURL . '/finance/shopee_order_req.php';
$deleteRedirectPage = $SITEURL . '/finance/shopee_order_req_table.php';
$result = getData('*', $whereSql, $groupBySql, SHOPEE_SG_ORDER_REQ, $finance_connect);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/shopeeOrderRequest.css">
</head>
<script>
  function toggleFilters(sectionId) {
        const section = document.getElementById(sectionId);
        section.style.display = (section.style.display === 'none') ? 'flex' : 'none';
    }

    function applyFilterOrGroup(param, element) {
        const value = element.value;
        const url = new URL(window.location.href);
        url.searchParams.set(param, value);
        window.location.href = url.toString();
    }

    function autoToggleSections() {
        const urlParams = new URLSearchParams(window.location.search);
        const filterFields = ['month', 'status', 'brand', 'pkg', 'acc'];
        const groupFields = ['month_gb', 'status_gb', 'brand_gb', 'pkg_gb', 'acc_gb'];

        let filterActive = filterFields.some(key => urlParams.get(key) && urlParams.get(key) !== '' && urlParams.get(key) !== 'All');
        let groupActive = groupFields.some(key => urlParams.get(key) && urlParams.get(key) !== '');

        if (filterActive) {
            document.getElementById('filterSection').style.display = 'flex';
        }

        if (groupActive) {
            document.getElementById('groupBySection').style.display = 'flex';
        }
    }

    // Call on page load
    window.onload = autoToggleSections;
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
                        <?php if ($result) { ?>
                            <div class="mt-auto mb-auto">
                                <?php if (in_array(4, $accessActionKey)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        Request </a>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
           <div class="col-md-12 mb-3">
                <button class="btn btn-info" type="button" onclick="toggleFilters('filterSection')">Show/Hide Filters</button>
                <button class="btn btn-primary" type="button" onclick="toggleFilters('groupBySection')">Show/Hide Group By</button>
            </div>
            <div id="filterSection" class="row mb-3" style="display: none;">
                <!-- Filter by Month -->
                <div class="col-md-3">
                    <label for="monthFilter" class="form-label">Filter by Month</label>
                    <select id="monthFilter" name="month" class="form-select" onchange="applyFilterOrGroup('month', this)">
                        <option value="All" <?= ($monthFilter === 'All') ? 'selected' : '' ?>>All Months</option>
                        <?php
                        $monthSql = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') AS month_value, DATE_FORMAT(date, '%M %Y') AS month_label FROM " . SHOPEE_SG_ORDER_REQ . " ORDER BY month_value DESC";
                        $monthResult = mysqli_query($finance_connect, $monthSql);
                        while ($monthRow = mysqli_fetch_assoc($monthResult)) {
                            $monthValue = $monthRow['month_value'];
                            $monthLabel = $monthRow['month_label'];
                            $selected = ($monthFilter == $monthValue) ? "selected" : "";
                            echo "<option value='$monthValue' $selected>$monthLabel</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Filter by Order Status -->
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Filter by Order Status</label>
                    <select id="statusFilter" name="status" class="form-select" onchange="applyFilterOrGroup('status', this)">
                        <option value="">All Statuses</option>
                        <?php
                        $statusSql = "SELECT DISTINCT order_status FROM " . SHOPEE_SG_ORDER_REQ;
                        $statusResult = mysqli_query($finance_connect, $statusSql);
                        while ($statusRow = mysqli_fetch_assoc($statusResult)) {
                            $status = $statusRow['order_status'];
                            $label = getOrderStatusLabel($status);
                            $selected = ($statusFilter == $status) ? "selected" : "";
                            echo "<option value='$status' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Filter by Brand -->
                <div class="col-md-3">
                    <label for="brandFilter" class="form-label">Filter by Brand</label>
                    <select id="brandFilter" name="brand" class="form-select" onchange="applyFilterOrGroup('brand', this)">
                        <option value="">All Brands</option>
                        <?php
                        $brandSql = "SELECT id, name FROM " . BRAND . " ORDER BY name ASC";
                        $brandResult = mysqli_query($connect, $brandSql);
                        while ($brandRow = mysqli_fetch_assoc($brandResult)) {
                            $selected = ($brandFilter == $brandRow['id']) ? 'selected' : '';
                            echo "<option value='{$brandRow['id']}' $selected>{$brandRow['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Filter by Package -->
                <div class="col-md-3">
                    <label for="pkgFilter" class="form-label">Filter by Package</label>
                    <select id="pkgFilter" name="pkg" class="form-select" onchange="applyFilterOrGroup('pkg', this)">
                        <option value="">All Packages</option>
                        <?php
                        $pkgSql = "SELECT id, name FROM " . PKG . " ORDER BY name ASC";
                        $pkgResult = mysqli_query($connect, $pkgSql);
                        while ($pkgRow = mysqli_fetch_assoc($pkgResult)) {
                            $selected = ($pkgFilter == $pkgRow['id']) ? 'selected' : '';
                            echo "<option value='{$pkgRow['id']}' $selected>{$pkgRow['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Filter by Shopee Account -->
                <div class="col-md-3">
                    <label for="accFilter" class="form-label">Filter by Shopee Account</label>
                    <select id="accFilter" name="acc" class="form-select" onchange="applyFilterOrGroup('acc', this)">
                        <option value="">All Accounts</option>
                        <?php
                        $accSql = "SELECT id, name FROM " . SHOPEE_ACC . " ORDER BY name ASC";
                        $accResult = mysqli_query($finance_connect, $accSql);
                        while ($accRow = mysqli_fetch_assoc($accResult)) {
                            $selected = ($accFilter == $accRow['id']) ? 'selected' : '';
                            echo "<option value='{$accRow['id']}' $selected>{$accRow['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <div class="col-md-2">
                    <label class="form-label d-block invisible">Reset</label>
                    <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-danger filter-reset">Reset</a>
                </div>
            </div>
    
            <div id="groupBySection" class="row mb-3" style="display: none;">
                <!-- Group by Month -->
                <div class="col-md-3">
                    <label for="monthGroupBy" class="form-label">Group by Month</label>
                    <select id="monthGroupBy" name="month_gb" class="form-select" onchange="applyFilterOrGroup('month_gb', this)">
                        <option value="All" <?= ($monthGroup === 'All') ? 'selected' : '' ?>>All Months</option>
                        <?php
                        $monthResult = mysqli_query($finance_connect, $monthSql); // reused query from above
                        mysqli_data_seek($monthResult, 0); // rewind result set
                        while ($monthRow = mysqli_fetch_assoc($monthResult)) {
                            $monthValue = $monthRow['month_value'];
                            $monthLabel = $monthRow['month_label'];
                            $selected = ($monthGroup == $monthValue) ? "selected" : "";
                            echo "<option value='$monthValue' $selected>$monthLabel</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Group by Order Status -->
                <div class="col-md-3">
                    <label for="statusGroupBy" class="form-label">Group by Order Status</label>
                    <select id="statusGroupBy" name="status_gb" class="form-select" onchange="applyFilterOrGroup('status_gb', this)">
                        <option value="">All Statuses</option>
                        <?php
                        mysqli_data_seek($statusResult, 0); // reuse query
                        while ($statusRow = mysqli_fetch_assoc($statusResult)) {
                            $status = $statusRow['order_status'];
                            $label = getOrderStatusLabel($status);
                            $selected = ($statusGroup == $status) ? "selected" : "";
                            echo "<option value='$status' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Group by Brand -->
                <div class="col-md-3">
                    <label for="brandGroupBy" class="form-label">Group by Brand</label>
                    <select id="brandGroupBy" name="brand_gb" class="form-select" onchange="applyFilterOrGroup('brand_gb', this)">
                        <option value="">All Brands</option>
                        <?php
                        mysqli_data_seek($brandResult, 0); // reuse
                        while ($brandRow = mysqli_fetch_assoc($brandResult)) {
                            $selected = ($brandGroup == $brandRow['id']) ? 'selected' : '';
                            echo "<option value='{$brandRow['id']}' $selected>{$brandRow['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Group by Package -->
                <div class="col-md-3">
                    <label for="pkgGroupBy" class="form-label">Group by Package</label>
                    <select id="pkgGroupBy" name="pkg_gb" class="form-select" onchange="applyFilterOrGroup('pkg_gb', this)">
                        <option value="">All Packages</option>
                        <?php
                        mysqli_data_seek($pkgResult, 0); // reuse
                        while ($pkgRow = mysqli_fetch_assoc($pkgResult)) {
                            $selected = ($pkgGroup == $pkgRow['id']) ? 'selected' : '';
                            echo "<option value='{$pkgRow['id']}' $selected>{$pkgRow['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <!-- Group by Shopee Account -->
                <div class="col-md-3">
                    <label for="accGroupBy" class="form-label">Group by Shopee Account</label>
                    <select id="accGroupBy" name="acc_gb" class="form-select" onchange="applyFilterOrGroup('acc_gb', this)">
                        <option value="">All Accounts</option>
                        <?php
                        mysqli_data_seek($accResult, 0); // reuse
                        while ($accRow = mysqli_fetch_assoc($accResult)) {
                            $selected = ($accGroup == $accRow['id']) ? 'selected' : '';
                            echo "<option value='{$accRow['id']}' $selected>{$accRow['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            
                <div class="col-md-2">
                    <label class="form-label d-block invisible">Reset</label>
                    <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-danger filter-reset">Reset</a>
                </div>
            </div>

             <?php if (!empty($verifyMessage)): ?>
                    <div class="alert alert-info">
                        <?= $verifyMessage ?>
                    </div>
                <?php endif; ?>
            <?php
            if (!$result) {
                echo '<div class="text-center"><h4>No Result!</h4></div>';
            } else {
                ?>
                <div class="table-responsive">
                <table class="table table-striped" id="shopee_order_req_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Shopee Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Package</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Shopee Buyer Username</th>
                            <th scope="col">Buyer Payment Method</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Product Price</th>
                            <th scope="col">Voucher</th>
                            <th scope="col">Actual Shipping Fee</th>
                            <th scope="col">Service Fee (incl. GST)</th>
                            <th scope="col">Transaction Fee (incl. GST)</th>
                            <th scope="col">AMS Commission Fee</th>
                            <th scope="col">Fees & Charges</th>
                            <th scope="col">Final Amount</th>
                            <th scope="col">Remark</th>
                           <?php  if(in_array(15, $accessActionKey)){ 
                            echo "<th scope=\"col\">Agent Profit</th>";
                            echo "<th scope=\"col\">Company Profit</th>";
                           } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", '', SHOPEE_ACC, $finance_connect);
                            $acc = $q1->fetch_assoc();
                            $q7 = getData('*', "id='" . $row['currency'] . "'", '', CUR_UNIT, $connect);
                            $curr = $q7->fetch_assoc();

                            $q8 = getData('*', "default_currency_unit='" . $row['currency'] . "'", '', CURRENCIES, $connect);
                            $currExcRate = $q8->fetch_assoc();
                            
                            $q2 = getData('name, agent_cost, cost', "id='" . $row['package'] . "'", '', PKG, $connect);
                            $pkg = $q2->fetch_assoc();

                            $q3 = getData('name', "id='" . $row['brand'] ."'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            $q4 = getData('buyer_username', "id='" . $row['buyer'] . "'", '', SHOPEE_CUST_INFO, $finance_connect);
                            $buyer = $q4->fetch_assoc();

                            $q6 = getData('*', "id='" . $row['buyer_pay_meth'] . "'", '', PAY_MTHD_SHOPEE, $finance_connect);
                            $pay = $q6->fetch_assoc();

                            $q5 = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $pic = $q5->fetch_assoc();
                            
                            $price = (float) ($row['price'] ?? 0);
                            $voucher = (float) ($row['voucher'] ?? 0);
                            $shipping = (float) ($row['act_shipping_fee'] ?? 0);
                            $trans_fee = (float) ($row['trans_fee'] ?? 0);
                            $service_fee = (float) ($row['service_fee'] ?? 0);
                            $ams_fee = (float) ($row['ams_fee'] ?? 0);
                            $fees = (float) ($row['fees'] ?? 0);
                            $final_amt = (float) ($row['final_amt'] ?? 0);
                            
                            if ($row['currency'] != $default_currency_id) {
                                if (!empty($currExcRate) && $currExcRate['exchange_currency_unit'] == $default_currency_id) {
                                    $rate = (float) $currExcRate['exchange_currency_rate'];
                                    if ($rate > 0) {
                                        $final_amt = $final_amt * $rate;
                                        $final_fees =$fees * $rate;
                                        $final_ams_fee = $ams_fee *$rate;
                                        $final_trans_fee = $trans_fee * $rate;
                                        $final_shipping = $shipping * $rate;
                                        $final_voucher = $voucher * $rate;
                                        $final_price = $price * $rate;
                                        $final_service_fee = $service_fee * $rate;
                                    }
                                }
                            }else{
                                $final_amt = $final_amt;
                                $final_fees =$fees;
                                $final_ams_fee = $ams_fee;
                                $final_trans_fee = $trans_fee;
                                $final_shipping = $shipping ;
                                $final_voucher = $voucher;
                                $final_price = $price;
                                $final_service_fee = $service_fee;
                            }
                            $total_price += $final_price;
                            $total_voucher += $final_voucher;
                            $total_shipping += $final_shipping;
                            $total_trans_fee += $final_trans_fee;
                            $total_ams_fee += $final_ams_fee;
                            $total_fees += $final_fees;
                            $total_final_amt += $final_amt;
                            $total_final_service_fee += $final_service_fee;

                            ?>
                            <tr>
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th scope="row" class="sticky-action">
                                    <?= $num++; ?>
                                </th>

                                <td scope="row" class="btn-container sticky-action">
                                <?php renderViewEditButtonByPin("1", $redirect_page, $row, $accessActionKey); ?>
                                <?php renderViewEditButtonByPin("2", $redirect_page, $row, $accessActionKey, $act_2); ?>
                                <?php renderDeleteButtonByPin($accessActionKey, $row['id'], $row['orderID'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage); ?> 
                                <?php if($row['order_status'] == 'OC' && in_array(14, $accessActionKey)){ ?>
                                 <a href="?verify_id=<?= $row['id'] ?>&month=<?= urlencode($monthFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-sm btn-success btn-verified" onclick="return confirm('Mark this order as verified?')">
                                    Verified
                                </a>
                                <?php } ?>
                                </td>
                                <td scope="row">
                                 <?= getOrderStatusLabel($row['order_status']) ?>
                                </td>

                                <td scope="row">
                                    <?= $acc['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $curr['unit'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['orderID'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['date'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['time'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pkg['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $brand['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $buyer['buyer_username'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pay['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pic['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['price'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['voucher'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['act_shipping_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['service_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['trans_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['ams_fee'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['fees'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['final_amt'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $row['remark'] ?? '' ?>
                                </td>
                               <?php  
                                if (in_array(15, $accessActionKey)) { 
                                    $clear_profit = ($final_amt - $pkg['cost']);
                                    echo "<td scope=\"row\">" . ($agentCostProfit = ($clear_profit *0.4)). "</td>";
                                    
                                     $total_final_agentCostProfit += $agentCostProfit;
                                    echo "<td scope=\"row\">" . ($companyCostProfit = ($clear_profit *0.6)) . "</td>";

                                     $total_final_companyCostProfit += $companyCostProfit;
                                } 
                                ?>

                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Shopee Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Package</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Shopee Buyer Username</th>
                            <th scope="col">Buyer Payment Method</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Product Price<br><?php echo "(RM)".$total_price;?></th>
                            <th scope="col">Voucher<br><?php echo "(RM)".$total_voucher;?></th>
                            <th scope="col">Actual Shipping Fee<br><?php echo "(RM)".$total_shipping;?></th>
                            <th scope="col">Service Fee (incl. GST)<br><?php echo "(RM)".$total_final_service_fee;?></th>
                            <th scope="col">Transaction Fee (incl. GST)<br><?php echo "(RM)".$total_trans_fee;?></th>
                            <th scope="col">AMS Commission Fee<br><?php echo "(RM)".$total_ams_fee;?></th>
                            <th scope="col">Fees & Charges<br><?php echo "(RM)".$total_fees;?></th>
                            <th scope="col">Final Amount<br><?php echo "(RM)".$total_final_amt; ?></th>
                            <th scope="col">Remark</th>
                           <?php  if(in_array(15, $accessActionKey)){ 
                            echo "<th scope=\"col\">Agent Profit (".$total_final_agentCostProfit.")</th>";
                            echo "<th scope=\"col\">Company Profit (".$total_final_companyCostProfit.")</th>";
                           } ?>
                        </tr>
                    </tfoot>
                </table>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
<script>
    dropdownMenuDispFix();
    datatableAlignment('shopee_order_req_table');
</script>
</html>