<?php
$pageTitle = 'Dashboard';
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$currency = '';


$chart1 = 'Dashboard Chart - Total Sales';
$chart2 = 'Dashboard Chart - Total Order';
$chart3 = 'Dashboard Chart - Total Shopee Sales';
$chart4 = 'Dashboard Chart - Total Shopee Order';
$chart5 = 'Dashboard Chart - Total Web Sales';
$chart6 = 'Dashboard Chart - Total Web Order';
$chart7 = 'Dashboard Chart - Total Facebook Sales';
$chart8 = 'Dashboard Chart - Total Facebook Order';
$chart9 = 'Dashboard Chart - Total Lazada Sales';
$chart10 = 'Dashboard Chart - Total Lazada Order';
$chart11 = 'Dashboard Chart - Total Facebook Ads Sales';
$chart12 = 'Dashboard Chart - Total Delivery Sales';
$chart13 = 'Dashboard Chart - Total Shopee Ads Sales';
$chart14 = 'Dashboard Chart - Total Shopee Withdrawal Sales';

$pinAccess1 = checkPin($connect, $chart1);
$pinAccess2 = checkPin($connect, $chart2);
$pinAccess3 = checkPin($connect, $chart3);
$pinAccess4 = checkPin($connect, $chart4);
$pinAccess5 = checkPin($connect, $chart5);
$pinAccess6 = checkPin($connect, $chart6);
$pinAccess7 = checkPin($connect, $chart7);
$pinAccess8 = checkPin($connect, $chart8);
$pinAccess9 = checkPin($connect, $chart9);
$pinAccess10 = checkPin($connect, $chart10);
$pinAccess11 = checkPin($connect, $chart11);
$pinAccess12 = checkPin($connect, $chart12);
$pinAccess13 = checkPin($connect, $chart13);
$pinAccess14 = checkPin($connect, $chart14);

$result = getData('*', '', '', LAZADA_ORDER_REQ, $connect);
$result2 = getData('*', '', '', FB_ORDER_REQ, $finance_connect);
$result3 = getData('*', '', '', WEB_ORDER_REQ, $finance_connect);
$result4 = getData('*', '', '', SHOPEE_SG_ORDER_REQ, $finance_connect); 
$result5 = getData('*', '', '', FB_ADS_TOPUP, $finance_connect); //this
$result6 = getData('*', '', '', SHOPEE_ADS_TOPUP, $finance_connect); //this
$result7 = getData('*', '', '', DEL_FEES_CLAIM, $finance_connect); //this
$result8 = getData('*', '', '', SHOPEE_WDL_TRANS, $finance_connect);
$result9 = getData('*', '', '', LAZADA_ORDER_REQ, $connect);
$result10 = getData('*', '', '', FB_ORDER_REQ, $finance_connect);
$result11 = getData('*', '', '', WEB_ORDER_REQ, $finance_connect);
$result12 = getData('*', '', '', SHOPEE_SG_ORDER_REQ, $finance_connect);   
$result13 = getData('date,amount', '', '', INTERNAL_CONSUME, $finance_connect);   
$result14 = getData('date,cost', '', '', ITL_CSM_ITEM, $finance_connect);
$result15 = getData('create_date,amount', '', '', STK_CDT_TOPUP_RCD, $finance_connect);
$result16 = getData('payment_date, topup_amt', '', '', SHOPEE_ADS_TOPUP, $finance_connect);
$result17 = getData('payment_date, topup_amt', '', '', FB_ADS_TOPUP, $finance_connect);
$result7 = getData('create_date,total', '', '', DEL_FEES_CLAIM, $finance_connect); //this
function compareTime($a, $b) {
    return strtotime($a) - strtotime($b);
}

// Sort the $xValue array using the custom comparison function

function extractXY($result1, $connect, $finance_connect, $result2 = null, $result3 = null, $result4 = null)
{
    $data2 = [];

    $results = [$result1, $result2, $result3, $result4];

    foreach ($results as $result) {
        if ($result !== null) {
            while ($row2 = mysqli_fetch_assoc($result)) {
                $date = isset($row2['date']) ? $row2['date'] : $row2['create_date'];
                $accName = null; // Initialize $accName to null
                if (isset($row2['meta_acc'])) {
                    $metaQuery = getData('*', "id='" . $row2['meta_acc'] . "'", '', META_ADS_ACC, $finance_connect);
                    $meta_acc = $metaQuery->fetch_assoc();
                    $accName = isset($meta_acc['accName']) ? $meta_acc['accName'] : null; // Set $accName if meta_acc exists
                }
                $courier_name = null; // Initialize $courier_name to null
                if (isset($row2['courier'])) {
                    $couriers = getData('name', "id='" . $row2['courier'] . "'", '', COURIER, $connect);
                    $courier_row = $couriers->fetch_assoc();  
                    $courier_name = isset($courier_row['name']) ? $courier_row['name'] : null; // Set $courier_name if courier exists
                }
                $shopee_acc_name = null; // Initialize $shopee_acc_name to null
                if (isset($row2['shopee_acc'])) {
                    $q1 = getData('*', "id='" . $row2['shopee_acc'] . "'", '', SHOPEE_ACC, $finance_connect);
                    $shopee_acc = $q1->fetch_assoc();
                    $shopee_acc_name = isset($shopee_acc['name']) ? $shopee_acc['name'] : null; // Set $shopee_acc_name if shopee_acc exists
                }
                $price = isset($row2['final_income']) ? $row2['final_income'] : (isset($row2['total']) ? $row2['total'] : (isset($row2['amount']) ? $row2['amount'] : (isset($row2['topup_amt']) ? $row2['topup_amt'] : $row2['price'])));
                $time = isset($row2['create_time']) ? $row2['create_time'] : '';
                $currency = isset($row2['curr_unit']) ? $row2['curr_unit'] : 
            (isset($row2['currency_unit']) ? $row2['currency_unit'] : 
            (isset($row2['currency']) ? $row2['currency'] : 'default_value'));
                $q1 = getData('unit', "id='" . $currency . "'", '', CUR_UNIT, $connect);
                $q2 = getData('*', "default_currency_unit='" . $currency . "'", '', CURRENCIES, $connect);
                $unit = $q1->fetch_assoc();
                $currencies = $q2->fetch_assoc();
                $curr = isset($unit['unit']) ? $unit['unit'] : '';

                $exchange_rate = isset($currencies['exchange_currency_rate']) ? $currencies['exchange_currency_rate'] : 1.0;
                $default_currency_unit = isset($currencies['default_currency_unit']) ? $currencies['default_currency_unit'] : '';
                $exchange_currency_unit = isset($currencies['exchange_currency_unit']) ? $currencies['exchange_currency_unit'] : '';
                $q3 = getData('unit', "id='" . $default_currency_unit . "'", '', CUR_UNIT, $connect);
                $q4 = getData('unit', "id='" . $exchange_currency_unit . "'", '', CUR_UNIT, $connect);
                $unitq3 = $q3->fetch_assoc();
                $unitq4 = $q4->fetch_assoc();
                $currq3 = isset($unitq3['unit']) ? $unitq3['unit'] : '';
                $currq4 = isset($unitq4['unit']) ? $unitq4['unit'] : '';

                if ($curr !== 'MYR') {
                    if ($currq3 === $curr && $currq4 === 'MYR') {
                        $price *= $exchange_rate; // Multiply by exchange rate if default currency is not MYR and exchange currency is MYR
                    }
                }

                // Check if the date, accName, and time already exist in the data array
                $existingIndex = array_search([$date, $accName, $time], array_map(function ($item) {
                    return [$item['date'], $item['accname'], $item['time']];
                }, $data2));

                if ($existingIndex !== false) {
                    // Date, accName, and time exist, update the price and increment order count
                    $data2[$existingIndex]['price'] += $price;
                    $data2[$existingIndex]['orderCount']++;
                } else {
                    // Date, accName, and time don't exist, add a new entry
                    $data2[] = ['date' => $date, 'price' => $price, 'time' => $time, 'accname' => $accName, 'courier_name' => $courier_name, 'shopee_acc_name' => $shopee_acc_name, 'orderCount' => 1];
                }
            }
        }
       
    }

    // Sort data by date and time
    usort($data2, function($a, $b) {
        return strcmp($a['date'], $b['date']) ?: strcmp($a['time'], $b['time']);
    });

    $legend = array_column($data2, 'date');
    $yValue = array_map(function ($item) {
        return $item['price']; // Return the price directly
    }, $data2);
    $accNames = array_column($data2, 'accname');
    $courierNames = array_column($data2, 'courier_name');
    $shopeeAccNames = array_column($data2, 'shopee_acc_name');
   
    $xValue = array_map(function ($item) {
        return $item['time']; // Return the time directly
    }, $data2);

    return ['xValues' => $xValue, 'yValues' => $yValue, 'legend' => $legend, 'accNames' => $accNames, 'courierNames' => $courierNames, 'shopeeAccNames' => $shopeeAccNames, 'orderCounts' => array_column($data2, 'orderCount')];
}



$data2 = extractXY($result4,$connect,$finance_connect,null,null,null);
$data3 = extractXY($result3,$connect,$finance_connect,null,null,null);
$data4 = extractXY($result2,$connect,$finance_connect,null,null,null);
$data5 = extractXY($result,$connect,$finance_connect,null,null,null);
$data6 = extractXY($result5,$connect,$finance_connect,null,null,null);
$data7 = extractXY($result6,$connect,$finance_connect,null,null,null);
$data8 = extractXY($result7,$connect,$finance_connect,null,null,null);
$data9 = extractXY($result8,$connect,$finance_connect,null,null,null);
$data1 = extractXY($result9,$connect,$finance_connect,$result10, $result11, $result12);

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <style>
    .chart-container {
        padding: 5px;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;

    }

    canvas {
        background-color: #fff;
        border-radius: 4px;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="row mb-3">
            <div class="col-md-3 dateFilters">
                <br>
                <select class="form-select" id="timeInterval">
                    <option value="daily">Today</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div class="col-md-6 dateFilters">
                <br>
                <div class="input-group date " id="datepicker">
                    <input type="text" class="form-control" placeholder="Select date" autocomplete="off">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-th"></span>
                    </div>
                </div>
                <div class="input-daterange input-group" id="datepicker2" style="display: none;">
                    <input type="text" class="input form-control" name="start" placeholder="Start date"
                        autocomplete="off" />
                    <span class="input-group-addon date-separator"> to </span>
                    <input type="text" class="input-sm form-control" name="end" placeholder="End date"
                        autocomplete="off" />
                </div>
                <div class="input-group input-daterange" id="datepicker3" style="display: none;">
                    <input type="text" class="input form-control" name="start" placeholder="Start month"
                        autocomplete="off" />
                    <span class="input-group-addon date-separator"> to </span>
                    <input type="text" class="input-sm form-control" name="end" placeholder="End month"
                        autocomplete="off" />

                </div>
                <div class="input-group input-daterange" id="datepicker4" style="display: none;">
                    <input type="text" class="input form-control" name="start" placeholder="Start year"
                        autocomplete="off" />
                    <span class="input-group-addon date-separator"> to </span>
                    <input type="text" class="input-sm form-control" name="end" placeholder="End year"
                        autocomplete="off" />

                </div>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess1)) : ?>
                <h2>Total Sales (MYR)</h2>
                <div class="chart-container">
                    <canvas id="myChart" height="325" style="max-width:100%;"></canvas>
                </div>

                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess2)) : ?>
                <h2>Total Order</h2>
                <div class="chart-container">
                    <canvas id="myChart2" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess3)) : ?>
                <h2>Total Shopee Sales (MYR)</h2>
                <div class="chart-container">
                    <canvas id="myChart3" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess4)) : ?>
                <h2>Total Shopee Order</h2>
                <div class="chart-container">
                    <canvas id="myChart4" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess5)) : ?>
                <h2>Total Web Sales</h2>
                <div class="chart-container">
                    <canvas id="myChart5" height="325" style="max-width:100%"></canvas>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess6)) : ?>
                <h2>Total Web Order</h2>
                <div class="chart-container">
                    <canvas id="myChart6" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess7)) : ?>
                <h2>Total Facebook Sales (MYR)</h2>
                <div class="chart-container">
                    <canvas id="myChart7" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess8)) : ?>
                <h2>Total Facebook Order</h2>
                <div class="chart-container">
                    <canvas id="myChart8" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess9)) : ?>
                <h2>Total Lazada Sales (MYR)</h2>
                <div class="chart-container">
                    <canvas id="myChart9" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess10)) : ?>
                <h2>Total Lazada Order</h2>
                <div class="chart-container">
                    <canvas id="myChart10" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess11)) : ?>
                <h2>Total Facebook Ads Transaction Sales</h2>
                <div class="chart-container">
                    <canvas id="myChart11" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess12)) : ?>
                <h2>Total Delivery Sales</h2>
                <div class="chart-container">
                    <canvas id="myChart12" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if (isActionAllowed("View", $pinAccess13)) : ?>
                <h2>Total Shopee Ads Transaction Sales</h2>
                <div class="chart-container">
                    <canvas id="myChart13" height="325" style="max-width:100%"></canvas>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <?php if (isActionAllowed("View", $pinAccess14)) : ?>
            <h2>Total Shopee Withdrawal Amount</h2>
            <div class="chart-container">
                <canvas id="myChart14" height="325" style="max-width:100%"></canvas>
            </div>

            <?php endif; ?>
        </div>
    </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-html-plugin"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
    </script>
    </script>
    <script>
    <?php include "./js/dashboard.js" ?>
    const xValues = <?php echo json_encode($data1['xValues']); ?>;
    const yValues = <?php echo json_encode($data1['yValues']); ?>;
    const legend = <?php echo json_encode($data1['legend']); ?>;
    const date = <?php echo json_encode($data1['legend']); ?>;
    const order = <?php echo json_encode($data1['orderCounts']); ?>;
    const legend_shp = <?php echo json_encode($data2['legend']); ?>;
    const xValues_shopee = <?php echo json_encode($data2['xValues']); ?>;
    const yValues_shopee = <?php echo json_encode($data2['yValues']); ?>;
    const order_shopee = <?php echo json_encode($data2['orderCounts']); ?>;
    const legend_web = <?php echo json_encode($data3['legend']); ?>;
    const xValues_web = <?php echo json_encode($data3['xValues']); ?>;
    const yValues_web = <?php echo json_encode($data3['yValues']); ?>;
    const order_web = <?php echo json_encode($data3['orderCounts']); ?>;
    const legend_fb = <?php echo json_encode($data4['legend']); ?>;
    const xValues_fb = <?php echo json_encode($data4['xValues']); ?>;
    const yValues_fb = <?php echo json_encode($data4['yValues']); ?>;
    const order_fb = <?php echo json_encode($data4['orderCounts']); ?>;
    const legend_lzd = <?php echo json_encode($data5['legend']); ?>;
    const xValues_lzd = <?php echo json_encode($data5['xValues']); ?>;
    const yValues_lzd = <?php echo json_encode($data5['yValues']); ?>;
    const order_lzd = <?php echo json_encode($data5['orderCounts']); ?>;
    const legend_fb_ads = <?php echo json_encode($data6['legend']); ?>;
    const xValues_fb_ads = <?php echo json_encode($data6['xValues']); ?>;
    const yValues_fb_ads = <?php echo json_encode($data6['yValues']); ?>;
    const accName_fb_ads = <?php echo json_encode($data6['accNames']); ?>;
    const legend_del = <?php echo json_encode($data8['legend']); ?>;
    const xValues_del = <?php echo json_encode($data8['xValues']); ?>;
    const yValues_del = <?php echo json_encode($data8['yValues']); ?>;
    const courier_name_del = <?php echo json_encode($data8['courierNames']); ?>;
    const legend_shp_ads = <?php echo json_encode($data7['legend']); ?>;
    const xValues_shp_ads = <?php echo json_encode($data7['xValues']); ?>;
    const yValues_shp_ads = <?php echo json_encode($data7['yValues']); ?>;
    const shopeeAccNames = <?php echo json_encode($data7['shopeeAccNames']); ?>;
    const xValues_shp_with = <?php echo json_encode($data9['xValues']); ?>;
    const yValues_shp_with = <?php echo json_encode($data9['yValues']); ?>;
    const legend_with = <?php echo json_encode($data9['legend']); ?>;
    </script>
</body>

</html>