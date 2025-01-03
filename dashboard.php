<?php
$pageTitle = 'Dashboard';
include 'menuHeader.php';
include 'checkCurrentPagePin.php';
include 'include/dashboardPanel.php';

$currentMonthYear = date('F-Y');
$currentMonthYearwithSmallerCaseMonth = date('M-Y');
$currentYear = date("Y");

$selectedPeriod = input('period') ? input('period') : $currentMonthYearwithSmallerCaseMonth;

// Separate the month and year
list($monthName, $year) = explode("-", $selectedPeriod);

// Convert the month name to a number
$monthNumber = date('m', strtotime($monthName));

$selectedYear = $year;
$selectedMonth = $monthNumber;


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
$shopeeEveryMonthResult = getData("SUM(price) as price, id,  GROUP_CONCAT(id) AS combined_ids ", $sqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeEveryMonthResult->num_rows > 0) {
    $ShopeeTotalIncomeArrr = $shopeeEveryMonthResult->fetch_assoc();
    $ShopeeTotalIncome = $ShopeeTotalIncomeArrr['price'] ? $ShopeeTotalIncomeArrr['price'] : '0';
} else {
    $ShopeeTotalIncome = '0';
}

//shopee my total sales by YEAR
$ShopeeByYearsqlQuery = "YEAR(`date`) = $selectedYear AND currency=1 ";
$shopeeEveryYearResult = getData("SUM(price) as price, id,  GROUP_CONCAT(id) AS combined_ids ", $ShopeeByYearsqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeEveryYearResult->num_rows > 0) {
    $ShopeeTotalIncomeArrrByYear = $shopeeEveryYearResult->fetch_assoc();
    $ShopeeTotalIncomeByYear = $ShopeeTotalIncomeArrrByYear['price'] ? $ShopeeTotalIncomeArrrByYear['price'] : '0';
} else {
    $ShopeeTotalIncomeByYear = '0';
}

//shopee SG total sales by MONTH
$shopeeSGsqlQuery = "YEAR(`date`) = $selectedYear AND MONTH(`date`) = $selectedMonth AND currency=2";
$shopeeSGEveryMonthResult = getData("SUM(price) as price, id,  GROUP_CONCAT(id) AS combined_ids ", $shopeeSGsqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeSGEveryMonthResult->num_rows > 0) {
    $ShopeeSgTotalIncomeArrr = $shopeeSGEveryMonthResult->fetch_assoc();
    $ShopeeSgTotalIncome = $ShopeeSgTotalIncomeArrr['price'] ? $ShopeeSgTotalIncomeArrr['price'] : '0';
} else {
    $ShopeeSgTotalIncome = '0';
}

//shopee SG total sales by YEAR
$shopeeSGByYearsqlQuery = "YEAR(`date`) = $selectedYear AND currency=2";
$shopeeSGEveryMonthResultByYear = getData("SUM(price) as price, id,  GROUP_CONCAT(id) AS combined_ids ", $shopeeSGByYearsqlQuery, '', SHOPEE_SG_ORDER_REQ, $finance_connect);
if ($shopeeSGEveryMonthResultByYear->num_rows > 0) {
    $ShopeeSgTotalIncomeArrrByYear = $shopeeSGEveryMonthResultByYear->fetch_assoc();
    $ShopeeSgTotalIncomeByYear = $ShopeeSgTotalIncomeArrrByYear['price'] ? $ShopeeSgTotalIncomeArrrByYear['price'] : '0';
} else {
    $ShopeeSgTotalIncomeByYear = '0';
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
        $monthlyConclusion = date("M Y") . " Monthy Conclusion";
        $data = [
            ['label' => $selectedPeriod . ' Total Sales', 'value' => $finalTotalSales],
            ['label' => 'Sales Target', 'value' => '0'],
            ['label' => 'Total Sales', 'value' => '0'],
            ['label' => 'Total Balance', 'value' => '0'],
        ];
        $containerClass = "thisMonthContainer";
        generateDashboard($monthlyConclusion, $data, $containerClass, true);


        $yearlyConclusion = $selectedYear . " Yearly Conclusion";
        $data = [
            ['label' => 'Sales Target', 'value' => '0'],
            ['label' => 'Total Sales', 'value' => $finalTotalSalesByYear],
            ['label' => 'Total Balance', 'value' => '0']
        ];
        $containerClass = "thisyearContainer";
        generateDashboard($yearlyConclusion, $data, $containerClass);
        ?>
        <div class="dropdown dropdownContainer">
            <button class="btn-lg dropdown-toggle dropdownSelectContainer" type="button" id="dropdownMenuButton"
                data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo $currentMonthYear; ?> <!-- Default selected month-year -->
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <?php foreach ($options as $option): ?>
                    <li><a class="dropdown-item" href="?period=<?php echo $option; ?>" data-value="<?php echo $option; ?>"
                            <?php echo $selectedPeriod == $option ? "selected" : ""; ?>> <?php echo $option; ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php
        echo '<div class="platformMainDashboard row">';
        $containerClass = "separatorContainer";

        //shopee SG
        $shopeeSgTarget = "Shopee SG Target";
        $shopeeSgTarget_data = [
            ['label' => 'Current Sales', 'value' => $ShopeeSgTotalIncome],
            ['label' => 'Balance Sales', 'value' => '0'],
        ];
        generateDashboardPlateformAnalysis($shopeeSgTarget, $shopeeSgTarget_data, $containerClass);

        //shopee MY
        $shopeeMyTarget = "Shopee MY Target";
        $shopeeMy_data = [
            ['label' => 'Current Sales', 'value' => $ShopeeTotalIncome],
            ['label' => 'Balance Sales', 'value' => '0'],
        ];
        $containerClass2 = "separatorContainer2";
        generateDashboardPlateformAnalysis($shopeeMyTarget, $shopeeMy_data, $containerClass2);

        //lazada MY
        $lazadaMyTarget = "Lazada MY Target";
        $lazadaMy_data = [
            ['label' => 'Current Sales', 'value' => $lazadaTotalIncome],
            ['label' => 'Balance Sales', 'value' => '0'],
        ];
        generateDashboardPlateformAnalysis($lazadaMyTarget, $lazadaMy_data, $containerClass);

        echo '<div class="platformsepearatorLine">';
        //website
        $lazadaMyTarget = "Website MY Target";
        $lazadaMy_data = [
            ['label' => 'Current Sales', 'value' => $websiteTotalIncome],
            ['label' => 'Balance Sales', 'value' => '0'],
        ];
        generateDashboardPlateformAnalysis($lazadaMyTarget, $lazadaMy_data, $containerClass2);

        //facebook 
        $facebookMyTarget = "Facebook Target";
        $facebookMy_data = [
            ['label' => 'Current Sales', 'value' => $facebookTotalIncome],
            ['label' => 'Balance Sales', 'value' => '0'],
        ];
        generateDashboardPlateformAnalysis($facebookMyTarget, $facebookMy_data, $containerClass);

        //expense
        $totalExpense = "Total Expense";
        $totalExpense_data = [
            ['label' => 'Used Expense', 'value' => '0'],
            ['label' => 'Balance Expense', 'value' => '0'],
        ];
        generateDashboardPlateformAnalysis($totalExpense, $totalExpense_data, $containerClass2);
        echo '</div>';

        echo "</div>";
        ?>



    </div>



    </>

    <script>
        <?php include "./js/dashboard.js" ?>

    </script>


</body>

</html>