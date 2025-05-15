<?php
$pageTitle = 'Dashboard';
include 'menuHeader.php';
include 'checkCurrentPagePin.php';
include 'include/dashboardPanel.php';

$currentPagePin = 7;

include ROOT.'/include/access.php';

$currentMonthYear = date('F-Y');
$currentMonthYearwithSmallerCaseMonth = date('M-Y');
$currentYear = date("Y");
$sgdExchangeMyrRate = 1;
$selectedPeriod = input('period') ? input('period') : $currentMonthYearwithSmallerCaseMonth;

// Separate the month and year
list($monthName, $year) = explode("-", $selectedPeriod);

// Convert the month name to a number
$monthNumber = date('m', strtotime($monthName));

$selectedYear = $year;
$selectedMonth = $monthNumber;
$formattedDate = DateTime::createFromFormat('m-Y', sprintf('%02d-%d', $selectedMonth, $selectedYear))
    ->format('M-Y');


$queryCurrencies = "SELECT exchange_currency_rate FROM " . CURRENCIES. " WHERE default_currency_unit = 2";
$resultCurriency = $connect->query($queryCurrencies);
if ($resultCurriency->num_rows > 0) {
    while ($rowCurrencies = $resultCurriency->fetch_assoc()) {
        $sgdExchangeMyrRate = $rowCurrencies['exchange_currency_rate'];
    }
}

$query = "SELECT DISTINCT DATE_FORMAT(date, '%M-%Y') AS month_year FROM " . SHOPEE_SG_ORDER_REQ . " ORDER BY date DESC";
$result = $finance_connect->query($query);

// Initialize an array to store the options
$options = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options[] = $row['month_year'];
    }
}
// Check if current month-year exists in the database, and if not, add it to the options
if (!in_array($currentMonthYear, $options)) {
    array_unshift($options, $currentMonthYear); // Add current month-year to the beginning of the array
}

//shopee my total sales by MONTH
$sqlQuery = "YEAR(`date`) = $selectedYear AND MONTH(`date`) = $selectedMonth AND currency=1 ";
$shopeeEveryMonthResult = getData("SUM(price) as price, SUM(final_amt) as final_amt, id,  GROUP_CONCAT(id) AS combined_ids ", $sqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeEveryMonthResult->num_rows > 0) {
    $ShopeeTotalIncomeArrr = $shopeeEveryMonthResult->fetch_assoc();
    $ShopeeTotalIncome = $ShopeeTotalIncomeArrr['price'] ? $ShopeeTotalIncomeArrr['price'] : '0';
    $ShopeeTotalFinalAmtIncome = $ShopeeTotalIncomeArrr['final_amt'] ? $ShopeeTotalIncomeArrr['final_amt'] : '0';
} else {
    $ShopeeTotalIncome = $ShopeeTotalFinalAmtIncome = '0';
}

//shopee my total sales by YEAR
$ShopeeByYearsqlQuery = "YEAR(`date`) = $selectedYear AND currency=1 ";
$shopeeEveryYearResult = getData("SUM(price) as price, SUM(final_amt) as final_amt, id,  GROUP_CONCAT(id) AS combined_ids ", $ShopeeByYearsqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeEveryYearResult->num_rows > 0) {
    $ShopeeTotalIncomeArrrByYear = $shopeeEveryYearResult->fetch_assoc();
    $ShopeeTotalIncomeByYear = $ShopeeTotalIncomeArrrByYear['price'] ? $ShopeeTotalIncomeArrrByYear['price'] : '0';
    $ShopeeTotalFinalAmtIncomeByYear = $ShopeeTotalIncomeArrrByYear['final_amt'] ? $ShopeeTotalIncomeArrrByYear['final_amt'] : '0';
} else {
    $ShopeeTotalIncomeByYear = $ShopeeTotalFinalAmtIncomeByYear = '0';
}

//shopee SG total sales by MONTH
$shopeeSGsqlQuery = "YEAR(`date`) = $selectedYear AND MONTH(`date`) = $selectedMonth AND currency=2";
$shopeeSGEveryMonthResult = getData("SUM(price) as price, SUM(final_amt) as final_amt, id,  GROUP_CONCAT(id) AS combined_ids ", $shopeeSGsqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeSGEveryMonthResult->num_rows > 0) {
    $ShopeeSgTotalIncomeArrr = $shopeeSGEveryMonthResult->fetch_assoc();
    $ShopeeSgTotalIncome = $ShopeeSgTotalIncomeArrr['price'] ? $ShopeeSgTotalIncomeArrr['price'] * $sgdExchangeMyrRate : '0';
    $ShopeeSgTotalFinalAmtIncome = $ShopeeSgTotalIncomeArrr['final_amt'] ? $ShopeeSgTotalIncomeArrr['final_amt'] * $sgdExchangeMyrRate : '0';
} else {
    $ShopeeSgTotalIncome = $ShopeeSgTotalFinalAmtIncome = '0';
}

//shopee SG total sales by YEAR
$shopeeSGByYearsqlQuery = "YEAR(`date`) = $selectedYear AND currency=2";
$shopeeSGEveryMonthResultByYear = getData("SUM(price) as price, SUM(final_amt) as final_amt, id,  GROUP_CONCAT(id) AS combined_ids ", $shopeeSGByYearsqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeSGEveryMonthResultByYear->num_rows > 0) {
    $ShopeeSgTotalIncomeArrrByYear = $shopeeSGEveryMonthResultByYear->fetch_assoc();
    $ShopeeSgTotalIncomeByYear = $ShopeeSgTotalIncomeArrrByYear['price'] ? $ShopeeSgTotalIncomeArrrByYear['price'] * $sgdExchangeMyrRate : '0';
    $ShopeeSgTotalFinalAmtIncomeByYear = $ShopeeSgTotalIncomeArrrByYear['final_amt'] ? $ShopeeSgTotalIncomeArrrByYear['final_amt'] * $sgdExchangeMyrRate : '0';
} else {
    $ShopeeSgTotalIncomeByYear = $ShopeeSgTotalFinalAmtIncomeByYear = '0';
}

//lazada total sales by MONTH
$lazadaSqlQuery = "YEAR(`create_date`) = $selectedYear AND MONTH(`create_date`) = $selectedMonth";
$lazadaEveryMonthResult = getData("SUM(final_income) as final_income, id, GROUP_CONCAT(id) AS combined_ids", $lazadaSqlQuery, '', LAZADA_ORDER_REQ, $connect);
if ($lazadaEveryMonthResult->num_rows > 0) {
    $LazadaTotalIncomeArrr = $lazadaEveryMonthResult->fetch_assoc();
    $lazadaTotalIncome = $LazadaTotalIncomeArrr['final_income'] ? $LazadaTotalIncomeArrr['final_income'] : '0';
} else {
    $lazadaTotalIncome = '0'; // No data found
}

//lazada total sales by YEAR
$lazadaSqlQueryByYear = "YEAR(`create_date`) = $selectedYear ";
$lazadaEveryMonthResultByYear = getData("SUM(final_income) as final_income, id, GROUP_CONCAT(id) AS combined_ids", $lazadaSqlQueryByYear, '', LAZADA_ORDER_REQ, $connect);
if ($lazadaEveryMonthResultByYear->num_rows > 0) {
    $LazadaTotalIncomeArrrByYear = $lazadaEveryMonthResultByYear->fetch_assoc();
    $lazadaTotalIncomeByYear = $LazadaTotalIncomeArrrByYear['final_income'] ? $LazadaTotalIncomeArrrByYear['final_income'] : '0';
} else {
    $lazadaTotalIncomeByYear = '0'; // No data found
}

//website total sales by MONTH
$websiteSqlQuery = "YEAR(`create_date`) = $selectedYear AND MONTH(`create_date`) = $selectedMonth";
$websiteEveryMonthResult = getData("SUM(price) as price, id, GROUP_CONCAT(id) AS combined_ids", $websiteSqlQuery, '', WEB_ORDER_REQ, $finance_connect);
if ($websiteEveryMonthResult->num_rows > 0) {
    $WebsiteTotalIncomeArrr = $websiteEveryMonthResult->fetch_assoc();
    $websiteTotalIncome = $WebsiteTotalIncomeArrr['price'] ? $WebsiteTotalIncomeArrr['price'] : '0';
} else {
    $websiteTotalIncome = '0'; // No data found
}

//website total sales by YEAR
$websiteSqlQueryByYear = "YEAR(`create_date`) = $selectedYear ";
$websiteEveryMonthResultByYear = getData("SUM(price) as price, id, GROUP_CONCAT(id) AS combined_ids", $websiteSqlQueryByYear, '', WEB_ORDER_REQ, $finance_connect);
if ($websiteEveryMonthResultByYear->num_rows > 0) {
    $WebsiteTotalIncomeArrrByYear = $websiteEveryMonthResultByYear->fetch_assoc();
    $websiteTotalIncomeByYear = $WebsiteTotalIncomeArrrByYear['price'] ? $WebsiteTotalIncomeArrrByYear['price'] : '0';
} else {
    $websiteTotalIncomeByYear = '0'; // No data found
}

//facebook total sales by MONTH
$facebookSqlQuery = "YEAR(`create_date`) = $selectedYear AND MONTH(`create_date`) = $selectedMonth";
$facebookEveryMonthResult = getData("SUM(price) as price, id, GROUP_CONCAT(id) AS combined_ids", $facebookSqlQuery, '', FB_ORDER_REQ, $finance_connect);
if ($facebookEveryMonthResult->num_rows > 0) {
    $FacebookTotalIncomeArrr = $facebookEveryMonthResult->fetch_assoc();
    $facebookTotalIncome = $FacebookTotalIncomeArrr['price'] ? $FacebookTotalIncomeArrr['price'] : '0';
} else {
    $facebookTotalIncome = '0'; // No data found
}

//facebook total sales by Year
$facebookSqlQueryByYear = "YEAR(`create_date`) = $selectedYear ";
$facebookEveryMonthResultByYear = getData("SUM(price) as price, id, GROUP_CONCAT(id) AS combined_ids", $facebookSqlQueryByYear, '', FB_ORDER_REQ, $finance_connect);
if ($facebookEveryMonthResultByYear->num_rows > 0) {
    $FacebookTotalIncomeArrrByYear = $facebookEveryMonthResultByYear->fetch_assoc();
    $facebookTotalIncomeByYear = $FacebookTotalIncomeArrrByYear['price'] ? $FacebookTotalIncomeArrrByYear['price'] : '0';
} else {
    $facebookTotalIncomeByYear = '0'; // No data found
}

$currenciesSqlQuery = "`default_currency_unit` = 2 AND `exchange_currency_unit` = 1";
$getExchangeCurrenciesRate = getData("exchange_currency_rate", $currenciesSqlQuery, '', CURRENCIES, $connect);
if ($getExchangeCurrenciesRate->num_rows > 0) {
    $currenciesArr = $getExchangeCurrenciesRate->fetch_assoc();
    $currenciesRate = $currenciesArr['exchange_currency_rate'] ? $currenciesArr['exchange_currency_rate'] : '0';
} else {
    $currenciesRate = '0'; // No data found
}


$finalTotalSales = $ShopeeTotalIncome + ($ShopeeSgTotalIncome * $currenciesRate) + $lazadaTotalIncome + $websiteTotalIncome + $facebookTotalIncome;

$finalTotalSalesByYear = $ShopeeTotalIncomeByYear + ($ShopeeSgTotalIncomeByYear * $currenciesRate) + $lazadaTotalIncomeByYear + $websiteTotalIncomeByYear + $facebookTotalIncomeByYear;
///goal target

$yearGoalTargetArr = $yearlyShopee_sg_goal = $yearlyShopee_my_goal = $yearlyLazada_goal = $yearlyFacebook_goal = $yearlyWebsite_goal = $yearlyTotal_goal = $total_goalByYear = '0';

$finalGoalTargetSqlQuery = "`year` = $selectedYear AND `month` = $selectedMonth";
$getYearlyGoalTarget = getData("*", $finalGoalTargetSqlQuery, '', YEARLYGOAL, $connect);
if ($getYearlyGoalTarget != false && $getYearlyGoalTarget->num_rows > 0) {
    $yearGoalTargetArr = $getYearlyGoalTarget->fetch_assoc();
    $yearlyShopee_my_goal = $yearGoalTargetArr['shopee_my_goal'] ? $yearGoalTargetArr['shopee_my_goal'] : '0';
    $yearlyShopee_sg_goal = $yearGoalTargetArr['shopee_sg_goal'] ? $yearGoalTargetArr['shopee_sg_goal'] : '0';
    $yearlyLazada_goal = $yearGoalTargetArr['lazada_goal'] ? $yearGoalTargetArr['lazada_goal'] : '0';
    $yearlyFacebook_goal = $yearGoalTargetArr['facebook_goal'] ? $yearGoalTargetArr['facebook_goal'] : '0';
    $yearlyWebsite_goal = $yearGoalTargetArr['website_goal'] ? $yearGoalTargetArr['website_goal'] : '0';
    $yearlyTotal_goal = $yearGoalTargetArr['total_goal'] ? $yearGoalTargetArr['total_goal'] : '0';

}

$finalGoalTargetByYearSqlQuery = "`year` = $selectedYear";
$getYearlyGoalTargetByYear = getData("sum(total_goal) as total_goal", $finalGoalTargetByYearSqlQuery, '', YEARLYGOAL, $connect);
if ($getYearlyGoalTargetByYear != false && $getYearlyGoalTargetByYear->num_rows > 0) {
    $row = $getYearlyGoalTargetByYear->fetch_assoc();

    // Access the 'total_goal' value from the associative array
    $total_goalByYear = isset($row['total_goal']) ? $row['total_goal'] : '0';
}

?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body>
    <div class="container-xxl">
        <?php
        function generateSalesData($label, $income, $goal) {
            return [
                'title' => $label . " (" . $goal . ")",
                'data' => [
                    ['label' => 'Current Sales', 'value' => $income],
                    ['label' => 'Balance Sales', 'value' => $income - $goal],
                ]
            ];
        }
    
        $monthlyConclusion = date("M Y") . " Monthly Conclusion";
        $data = [];
    
        if (in_array(17, $accessActionKey)) {
            if ($currentMonthYearwithSmallerCaseMonth != $formattedDate) {
                $data[] = ['label' => $selectedPeriod . ' Total Sales', 'value' => $finalTotalSales];
            }
    
            $data[] = ['label' => 'Sales Target', 'value' => $yearlyTotal_goal];
            $data[] = ['label' => 'Total Sales', 'value' => $finalTotalSales];
            $data[] = ['label' => 'Total Balance', 'value' => $finalTotalSales - $yearlyTotal_goal];
    
            $containerClass = "thisMonthContainer";
            generateDashboard($monthlyConclusion, $data, $containerClass, true);
        }
    
        if (in_array(16, $accessActionKey)) {
            $yearlyConclusion = $selectedYear . " Yearly Conclusion";
            $data = [
                ['label' => 'Sales Target', 'value' => $total_goalByYear],
                ['label' => 'Total Sales', 'value' => $finalTotalSalesByYear],
                ['label' => 'Total Balance', 'value' => $finalTotalSalesByYear - $total_goalByYear]
            ];
            $containerClass = "thisyearContainer";
            generateDashboard($yearlyConclusion, $data, $containerClass);
        }
        if (array_intersect([18, 19, 20, 21, 22, 23], $accessActionKey)): ?>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="mb-0">Sales (Package)</h2>
                <div class="dropdown dropdownContainer">
                    <button class="btn-lg dropdown-toggle dropdownSelectContainer" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $currentMonthYear; ?>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php foreach ($options as $option): ?>
                            <li>
                                <a class="dropdown-item <?php echo $selectedPeriod == $option ? 'active' : ''; ?>"
                                   href="?period=<?php echo $option; ?>" data-value="<?php echo $option; ?>">
                                    <?php echo $option; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif;

       
        echo '<div class="platformMainDashboard row">';
        
        $platforms = [
            18 => [
                'title' => "Shopee SG Target",
                'income' => $ShopeeSgTotalIncome,
                'goal' => $yearlyShopee_sg_goal,
            ],
            19 => [
                'title' => "Shopee MY Target",
                'income' => $ShopeeTotalIncome,
                'goal' => $yearlyShopee_my_goal,
            ],
            20 => [
                'title' => "Lazada MY Target",
                'income' => $lazadaTotalIncome,
                'goal' => $yearlyLazada_goal,
            ],
            21 => [
                'title' => "Website MY Target",
                'income' => $websiteTotalIncome,
                'goal' => $yearlyWebsite_goal,
            ],
            22 => [
                'title' => "Facebook Target",
                'income' => $facebookTotalIncome,
                'goal' => $yearlyFacebook_goal,
            ],
            23 => [
                'title' => "Total Expense",
                'income' => [
                    ['label' => 'Used Expense', 'value' => '0'],
                    ['label' => 'Balance Expense', 'value' => '0'],
                ],
                'goal' => null, // No goal needed for expenses
            ],
        ];
        
        // Counter to determine even/odd
        $counter = 0;
        
        foreach ($platforms as $key => $platform) {
            if (in_array($key, $accessActionKey)) {
                $counter++;
                $containerClass = ($counter % 2 == 0) ? "separatorContainer2" : "separatorContainer";
                
                // Handle expense separately because no "goal" passed
                if ($key == 23) {
                    generateDashboardPlateformAnalysis($platform['title'], $platform['income'], $containerClass);
                } else {
                    $salesData = generateSalesData($platform['title'], $platform['income'], $platform['goal']);
                    generateDashboardPlateformAnalysis($salesData['title'], $salesData['data'], $containerClass);
                }
            }
        }
        
        echo '</div>'; // End of main dashboard
        
        // Now handle "Sales Final Amount" section
        echo '<h2 class="mt-2">Sales (Final Amount)</h2>';
        echo '<div class="platformsepearatorLine row">';
        
        $finalAmounts = [
            24 => [
                'title' => "Shopee SG Received Final Amount",
                'income' => $ShopeeSgTotalFinalAmtIncome,
                'goal' => $yearlyShopee_sg_goal,
            ],
            25 => [
                'title' => "Shopee MY Received Final Amount",
                'income' => $ShopeeTotalFinalAmtIncome,
                'goal' => $yearlyShopee_my_goal,
            ],
        ];
        
        // Reset counter
        $counter = 0;
        
        foreach ($finalAmounts as $key => $platform) {
            if (in_array($key, $accessActionKey)) {
                $counter++;
                $containerClass = ($counter % 2 == 0) ? "separatorContainer2" : "separatorContainer";
                
                $salesData = generateSalesData($platform['title'], $platform['income'], $platform['goal']);
                generateDashboardPlateformAnalysis($salesData['title'], $salesData['data'], $containerClass);
            }
        }
        
        echo '</div>'; // End of final amount section
        ?>

    </div>
    </>

    <script>
        <?php include "./js/dashboard.js" ?>

    </script>


</body>

</html>