<?php

$userID = USER_ID;

if (empty($userID)) {
    echo "<script>window.location.href = '$SITEURL/index.php';</script>";
}

$pinArr = array();

$pin_query = "SELECT id FROM " . PIN;
$pin_result = $connect->query($pin_query);

if ($pin_result->num_rows >= 1) {
    while ($pin_row = $pin_result->fetch_assoc()) {
        array_push($pinArr, $pin_row['id']);
    }
}


$menuList = array(
    // dashboard
    array(
        'Dashboard',                    // pagename
        'mdi mdi-view-dashboard',       // icon class
        $SITEURL . '/dashboard.php',                // page
        'n',                            // check whether is a dropdown
        'expand' => array(),            // dropdown list item
        'pin' => array('0')             // action
    ),
    array(
        'Employees',
        'mdi mdi-account-outline',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Designations', '', $SITEURL . '/designations_table.php', '4'),
            array('Departments', 'mdi mdi-domain', $SITEURL . '/department_table.php', '5'),
            array('Employee Details', 'mdi mdi-information-outline', $SITEURL . '/employeeDetailsTable.php', '34'),
        ),
        'pin' => array('4', '5', '34')
    ),
    array(
        'Customer',
        'mdi mdi-account-outline',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Customer Info', 'mdi mdi-information-outline', $SITEURL . '/customerInfoTable.php', '38'),
        ),
        'pin' => array('38')
    ),
    array(
        'Product',
        'mdi mdi-package-variant',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Product', 'mdi mdi-package-variant', $SITEURL . '/product_table.php', '20'),
            array('Package', 'mdi mdi-package', $SITEURL . '/package_table.php', '21'),
        ),
        'pin' => array('20', '21')
    ),
    array(
        'Finance',
        'mdi mdi-finance',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array(
                'Accounting',
                'mdi mdi-finance',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Merchant', 'mdi storefront-outline', $SITEURL . '/finance/merchant_table.php', '36')
                ),
                'pin' => array('36'),
            ),
            array(
                'Assets and Liabilities List',
                'mdi mdi-finance',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Current Bank Account Transaction', 'mdi storefront-outline', $SITEURL.'/finance/curr_bank_trans_table.php', '37'),
                    array('Investment Transaction', 'mdi storefront-outline', $SITEURL.'/finance/investment_trans_table.php', '40'), 
                    array('Inventories Transaction', 'mdi storefront-outline', $SITEURL.'/finance/invtr_trans_table.php', '41'),
                    array('Sundry Debtors Transaction', 'mdi storefront-outline', $SITEURL.'/finance/sundry_debt_trans_table.php', '42'),
                    array('Other Creditor Transaction', 'mdi storefront-outline', $SITEURL.'/finance/other_creditor_trans_table.php', '43'),
                    array('Initial Capital Transaction', 'mdi storefront-outline', $SITEURL.'/finance/initial_capital_trans_table.php', '44'),   
                    array('Cash On Hand Transaction', 'mdi storefront-outline', $SITEURL.'/finance/cash_on_hand_trans_table.php', '45'),
                    array('Monthly Bank Transaction Backup Record', 'mdi storefront-outline', $SITEURL.'/finance/bank_trans_backup_table.php', '51'),
                ),
                'pin' => array('37','40','41','42','43','44','45','51'),

            ),
            array(
                'Expense',
                'mdi mdi-finance',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Meta Ads Account', 'mdi storefront-outline', $SITEURL . '/finance/meta_ads_acc_table.php', '46'),
                    array('Facebook Ads Top Up Transaction', 'mdi storefront-outline', $SITEURL . '/finance/fb_ads_topup_trans_table.php', '48'),
                    array('Merchant Commission Record', 'mdi storefront-outline', $SITEURL . '/finance/merchant_comm_record_table.php', '49'),
                    array('Facebook Ads Top Up Transaction', 'mdi storefront-outline', $SITEURL . '/finance/fb_ads_topup_trans_table.php', '47'),
                    array('Shopee Account', 'mdi storefront-outline', $SITEURL . '/finance/shopee_acc_table.php', '58'),

                ),
                'pin' => array('46','47','48','49','58'),
            ),
        ),
        'pin' => array('36', '37','40','41','42','43','44','45','46','47','48','49','51','58')

    ),
    array(
        'Other',
        'mdi mdi-dots-horizontal',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Barcode Generator', 'mdi mdi-barcode', $SITEURL . '/barcode_generator.php', '22'),
            array('Rate Checking', 'mdi mdi-package-variant', $SITEURL . '/rate_checking.php', '17'),
            array('Theme Setting', 'mdi mdi-brush-variant', $SITEURL . '/theme_setting.php', '23'),
            array('System Setting', 'mdi mdi-brush-variant', $SITEURL . '/system_setting.php', '39'),

        ),
        'pin' => array('22', '17', '23', '39')
    ),
    array(
        'Settings',
        'mdi mdi-cog',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array(
                'User Management',
                'mdi mdi-folder-account',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Pin', 'mdi mdi-pin', $SITEURL . '/pin_table.php', '1'),
                    array('Pin Group', 'mdi mdi-ungroup', $SITEURL . '/pin_group_table.php', '2'),
                    array('User Group', 'mdi mdi-account-wrench-outline', $SITEURL . '/user_group_table.php', '3'),
                ),
                'pin' => array('1', '2', '3'),
            ),
            array(
                'User Administration Setting',
                'mdi mdi-account-key',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Bank', 'mdi mdi-bank', 'bank_table.php', '8'),
                    array('Currencies', 'mdi mdi-swap-horizontal', $SITEURL . '/currencies_table.php', '11'),
                    array('Currency Unit', 'mdi mdi-currency-usd', $SITEURL . '/currency_unit_table.php', '10'),
                    array('Platform', 'mdi mdi-home-outline', $SITEURL . '/platform_table.php', '14'),
                    array('Warehouse', 'mdi mdi-warehouse', $SITEURL . '/warehouse.php', '16'),
                    array('Weight Unit', 'mdi mdi-weight', $SITEURL . '/weight_unit_table.php', '19'),
                    array('Change Password', 'mdi mdi-key-change', $SITEURL . '/changePassword.php', '25'),
                ),
                'pin' => array('8', '11', '10', '14', '16', '19', '25'),
            ),
            array(
                'Product Administration Setting',
                'mdi mdi-archive-settings-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Product Status', 'mdi mdi-package-variant-closed', $SITEURL . '/prod_status_table.php', '15'),
                    array('Brand', 'mdi mdi-label-outline', $SITEURL . '/brand_table.php', '9'),
                    array('Courier Account', 'mdi mdi-label-outline', $SITEURL . '/courier_table.php', '50'),
                ),
                'pin' => array('15', '9', '50'),
            ),
            array(
                'Employee Administration Setting',
                'mdi mdi-account-wrench-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Employment Type Status', 'mdi mdi-account-question-outline', $SITEURL . '/em_type_status_table.php', '12'),
                    array('Marital Status', 'mdi mdi-account-heart-outline', $SITEURL . '/marital_status_table.php', '13'),
                    array('Holidays', 'mdi mdi-calendar-star', $SITEURL . '/holiday_table.php', '6'),
                    array('Leave Type', 'mdi mdi-run-fast', $SITEURL . '/leave_type_table.php', '24'),
                    array('Identity Type', 'mdi mdi-book-search-outline', $SITEURL . '/identityTypeTable.php', '26'),
                    array('Leave Status', 'mdi mdi-run-fast', $SITEURL . '/leave_status_table.php', '27'),
                    array('Race', 'mdi mdi-account-star-outline', $SITEURL . '/race_table.php', '28'),
                    array('Socso Category', 'mdi mdi-google-fit', $SITEURL . '/socso_category_table.php', '30'),
                    array('Employer EPF Rate', 'mdi mdi-account-star-outline', $SITEURL . '/employer_epf_rate_table.php', '32'),
                    array('Employee EPF Rate', 'mdi mdi-account-supervisor', $SITEURL . '/employee_epf_rate_table.php', '31'),

                ),
                'pin' => array('12', '13', '6', '24', '26', '27', '28', '30', '31', '32'),
            ),
            array(
                'Customer Administration Setting',
                'mdi mdi-account-wrench-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Customer Segmentation', 'mdi mdi-account-group-outline', $SITEURL . '/cus_segmentation_table.php', '29'),
                    array('Tag', 'mdi mdi-account-group-outline', $SITEURL . '/tagTable.php', '35'),
                ),
                'pin' => array('29', '35'),
            ),
            array(
                'Payroll Administration  Setting',
                'mdi mdi-cash-multiple',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Payment Method', 'mdi mdi-contactless-payment-circle', $SITEURL . '/payment_method_table.php', '33'),
                    array('Tax Setting', 'mdi mdi-contactless-payment-circle', $SITEURL . '/tax_setting_table.php', '57'),
                ),
                'pin' => array('33','57'),
            ),
            array(
                'Finance Administration  Setting',
                'mdi mdi-account-wrench-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Expense Type', 'mdimdi-account-wrench-outline', $SITEURL . '/finance/expense_type_table.php', '47'),
                    array('Payment Method (Finance)', 'mdimdi-account-wrench-outline', $SITEURL . '/finance/fin_payment_method_table.php', '60'),
                    array('Payment Terms', 'mdimdi-account-wrench-outline', $SITEURL . '/finance/payment_terms_table.php', '63'),
                ),
                'pin' => array('47','60','63'),
            ),
        ),

        'pin' => array('1', '2', '3', '8', '11', '10', '14', '16', '19', '15', '9', '12', '13', '6', '24', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','60','63')
    ),
    array(
        'Audit Log',
        'mdi mdi-text-box-search-outline',
        $SITEURL . '/audit_log.php',
        'n',
        'expand' => array(),
        'pin' => array('18')
    ),
);

?>


<head>
    <link rel="stylesheet" href="<?php $SITEURL . '/css/main.css' ?>">
</head>

<style>
@media (max-width: 768px) {
    #navbarMenuBar {
        display: none;
        color: #FFFFFF;
    }
}
</style>

<script>

</script>

<!-- H.Navbar -->
<nav class="menuBar">
    <!-- Container wrapper -->
    <div class="container-fluid">
        <!-- Elements -->
        <ul class="nav nav-tabs">
            <?php
            /*
            if(!GlobalPin){
                echo "<script>location.href ='$SITEURL/index.php';</script>";
            }
            */
            foreach ($menuList as $innerList) {
                if (!empty(array_intersect($innerList['pin'], GlobalPin))) {
                    $li = $innerList[3] == 'y' ? "class=\"nav-item dropdown\"" : "class=\"nav-item\"";
                    $a = $innerList[3] == 'y' ? "class=\"nav-link dropdown-toggle\" data-bs-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\"" : "class=\"nav-link\"";

                    echo "<li $li>";
                    echo "<a $a href=\"$innerList[2]\"><i class=\"$innerList[1]\"></i><span> $innerList[0]</span></a>";
                    echo "<ul class=\"dropdown-menu menuBar\">";
                    foreach ($innerList['expand'] as $url) {
                        if (isset($url['expand'])) {
                            if (!empty(array_intersect($url['pin'], GlobalPin))) {
                                echo "<li>";
                                echo "<a class=\"dropdown-item dropdown-toggle\" href=\"$url[2]\"><span> $url[0]</span></a>";
                                echo "<ul class=\"dropdown-menu dropdown-submenu menuBar\">";

                                foreach ($url['expand'] as $url2) {
                                    if (in_array($url2[3], GlobalPin)) {
                                        echo "<li><a class=\"dropdown-item\" href=\"$url2[2]\">$url2[0]</a></li>";
                                    }
                                }

                                echo "</ul>";
                                echo "</li>";
                            }
                        } else {
                            if (in_array($url[3], GlobalPin)) {
                                echo "<li><a class=\"dropdown-item\" href=\"$url[2]\">$url[0]</a></li>";
                            }
                        }
                    }
                    echo "</ul>";
                    echo "</li>";
                }
            }
            ?>
        </ul>
        <!-- Elements -->
    </div>
    <!-- Container wrapper -->
</nav>
<!-- H.Navbar -->

<!-- V.Navbar -->
<aside>
    <nav class="sidebar-nav" id="sidebar">
        <!-- Container wrapper -->
        <div class="container-fluid">
            <!-- Elements -->
            <ul class="nav nav-tabs">
                <?php
                foreach ($menuList as $innerList) {
                    if (!empty(array_intersect($innerList['pin'], GlobalPin))) {
                        $li = $innerList[3] == 'y' ? "class=\"nav-item dropdown\"" : "class=\"nav-item\"";
                        $a = $innerList[3] == 'y' ? "class=\"nav-link dropdown-toggle\" data-bs-toggle=\"collapse\" data-bs-target=\"#$innerList[0]-collapse\" aria-expanded=\"false\"" : "class=\"nav-link\" href=\"$innerList[2]\"";

                        echo "<li $li>";
                        echo "<a $a href=\"#\"><i class=\"$innerList[1]\"></i><span> $innerList[0]</span></a>";
                        echo "<div class=\"collapse\" id=\"$innerList[0]-collapse\">";
                        echo "<ul class=\"list-unstyled collapse-menu\">";
                        foreach ($innerList['expand'] as $url) {
                            if (isset($url['expand'])) {
                                if (!empty(array_intersect($url['pin'], GlobalPin))) {
                                    $idCollapse = str_replace(" ", "-", $url[0]);

                                    $li = $url[3] == 'y' ? "class=\"nav-item dropdown\"" : "class=\"nav-item\"";
                                    $a = $url[3] == 'y' ? "class=\"nav-link dropdown-toggle\" data-bs-toggle=\"collapse\" data-bs-target=\"#$idCollapse-collapse\" aria-expanded=\"false\"" : "class=\"nav-link\" href=\"$url[2]\"";

                                    echo "<li $li>";
                                    echo "<a $a href=\"#\"><i class=\"$url[1]\"></i><span> $url[0]</span></a>";
                                    echo "<div class=\"collapse\" id=\"$idCollapse-collapse\">";
                                    echo "<ul class=\"list-unstyled collapse-menu\">";

                                    foreach ($url['expand'] as $url2) {
                                        if (in_array($url2[3], GlobalPin)) {
                                            echo "<li><a class=\"nav-link\" href=\"$url2[2]\"><i class=\"$url2[1]\"></i><span> $url2[0]<span></a></li>";
                                        }
                                    }

                                    echo "</ul>";
                                    echo "</div>";
                                    echo "</li>";
                                }
                            } else {
                                if (in_array($url[3], GlobalPin)) {
                                    echo "<li><a class=\"nav-link\" href=\"$url[2]\"><i class=\"$url[1]\"></i><span> $url[0]<span></a></li>";
                                }
                            }

                            /* if(in_array($url[3], GlobalPin))
                            {
                                echo "<li><a class=\"nav-link\" href=\"$url[2]\"><i class=\"$url[1]\"></i><span> $url[0]<span></a></li>";
                            } */
                        }
                        echo "</ul>";
                        echo "</div>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>
            <!-- Elements -->
        </div>
        <!-- Container wrapper -->
    </nav>
    <div id="filter_screen" class="filter_screen" style="display:none;">
    </div>
</aside>
<!-- V.Navbar -->

<script>
var sidebar = $("#sidebar");
var sidebar_toggleBtn = $("#sidebarCollapse"); // variable from menuHeader
var opacityBackground = $('div#filter_screen');

(function($) {
    sidebar_toggleBtn.on("click", function() {
        if (sidebar.hasClass("active")) {
            sidebar.toggleClass("close", true);
            opacityBackground.hide();

            // timeout value based on .close css transition (0.3s)
            setTimeout(() => {
                sidebar.removeClass('active');
                sidebar.removeClass('close');
            }, 500);
        } else {
            sidebar.toggleClass("active", true);
            sidebar.toggleClass("close", false);
            opacityBackground.show();
        }
    });

    opacityBackground.on('click', function(e) {
        var sidebar2 = $("#sidebar, #sidebarCollapse");
        if (!sidebar2.is(e.target) && sidebar2.has(e.target).length === 0) {
            sidebar.toggleClass('close', true);
            opacityBackground.hide();
            setTimeout(() => {
                sidebar.removeClass('active');
                sidebar.removeClass('close');
            }, 300);
        }
    });
})(jQuery);
</script>