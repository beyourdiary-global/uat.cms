<?php
$pinArr = array();
$pin_query = "SELECT id FROM ".PIN;
$pin_result = $connect->query($pin_query);
if($pin_result->num_rows >= 1)
{
    while($pin_row = $pin_result->fetch_assoc())
    {
        array_push($pinArr, $pin_row['id']);
    }
}

$menuList = array(
    // dashboard
    array(
        'Dashboard',                    // pagename
        'mdi mdi-view-dashboard',       // icon class
        'dashboard.php',                // page
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
            array('Designations', '
            ', 'designations_table.php', '4'),
            array('Departments', 'mdi mdi-domain', 'department_table.php', '5'),
        ),
        'pin' => array('4','5')
    ),
    array(
        'Product',
        'mdi mdi-package-variant',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Product', 'mdi mdi-package-variant', 'product_table.php', '20'),
            array('Package', 'mdi mdi-package', 'package_table.php', '21'),
        ),
        'pin' => array('20','21')
    ),
    array(
        'Other',
        'mdi mdi-dots-horizontal',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Barcode Generator', 'mdi mdi-barcode', 'barcode_generator.php', '22'),
            array('Rate Checking', 'mdi mdi-package-variant', 'rate_checking.php', '17'),
            array('Theme Setting', 'mdi mdi-brush-variant', 'theme_setting.php', '23'),
        ),
        'pin' => array('22','17','23')
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
                    array('Pin', 'mdi mdi-pin', 'pin_table.php', '1'),
                    array('Pin Group', 'mdi mdi-ungroup', 'pin_group_table.php', '2'),
                    array('User Group', 'mdi mdi-account-wrench-outline', 'user_group_table.php', '3'),
                ),
                'pin' => array('1','2','3'),
            ),
            array(
                'User Administration Setting',
                'mdi mdi-account-key',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Bank', 'mdi mdi-bank', 'bank_table.php', '8'),
                    array('Currencies', 'mdi mdi-swap-horizontal', 'currencies_table.php', '11'),
                    array('Currency Unit', 'mdi mdi-currency-usd', 'currency_unit_table.php', '10'),
                    array('Platform', 'mdi mdi-home-outline', 'platform_table.php', '14'),
                    array('Warehouse', 'mdi mdi-warehouse', 'warehouse.php', '16'),
                    array('Weight Unit', 'mdi mdi-weight', 'weight_unit_table.php', '19'),
                    array('Change Password', 'mdi mdi-key-change', 'changePassword.php', '25'),
                ),
                'pin' => array('8','11','10','14','16','19','25'),
            ),
            array(
                'Product Administration Setting',
                'mdi mdi-archive-settings-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Product Status', 'mdi mdi-package-variant-closed', 'prod_status_table.php', '15'),
                    array('Brand', 'mdi mdi-label-outline', 'brand_table.php', '9'),
                ),
                'pin' => array('15','9'),
            ),
            array(
                'Employee Administration Setting',
                'mdi mdi-account-wrench-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Employment Type Status', 'mdi mdi-account-question-outline', 'em_type_status_table.php', '12'),
                    array('Marital Status', 'mdi mdi-account-heart-outline', 'marital_status_table.php', '13'),
                    array('Holidays', 'mdi mdi-calendar-star', 'holiday_table.php', '6'),
                    array('Leave Type', 'mdi mdi-run-fast', 'leave_type_table.php', '24'),
                    array('Identity Type', 'mdi mdi-book-search-outline', 'Identity_type_table.php','26'),
                    array('Leave Status', 'mdi mdi-run-fast', 'leave_status_table.php','27'),
                    array('Race', 'mdi mdi-account-star-outline', 'race_table.php', '28'),
                ),
                'pin' => array('12','13','6','24','26','27','28'),
            ),   
            array(
                'Customer Administration Setting',
                'mdi mdi-account-wrench-outline',
                'javascript:void(0)',
                'y',
                'expand' => array(
                    array('Customer Segmentation', 'mdi mdi-account-group-outline', 'cus_segmentation_table.php','29'),
                ),
                'pin' => array('29'),
            ), 
        ),
        'pin' => array('1','2','3','8','11','10','14','16','19','15','9','12','13','6','24','26', '27', '28','29')

    ),
    array(
        'Audit Log',
        'mdi mdi-text-box-search-outline',
        'audit_log.php',
        'n',
        'expand' => array(),
        'pin' => array('18')
    ),
);

?>


<head>
<link rel="stylesheet" href="./css/main.css">
</head>

<style>
@media (max-width: 768px) {
    #navbarMenuBar
    {
        display:none;
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
                foreach($menuList as $innerList)
                {
                    if(!empty(array_intersect($innerList['pin'], GlobalPin)))
                    {
                        $li = $innerList[3] == 'y' ? "class=\"nav-item dropdown\"" : "class=\"nav-item\"";
                        $a = $innerList[3] == 'y' ? "class=\"nav-link dropdown-toggle\" data-bs-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\"" : "class=\"nav-link\"";

                        echo "<li $li>";
                        echo "<a $a href=\"$innerList[2]\"><i class=\"$innerList[1]\"></i><span> $innerList[0]</span></a>";
                        echo "<ul class=\"dropdown-menu menuBar\">";
                        foreach($innerList['expand'] as $url)
                        {
                            if(isset($url['expand']))
                            {
                                if(!empty(array_intersect($url['pin'], GlobalPin)))
                                {
                                    echo "<li>";
                                    echo "<a class=\"dropdown-item dropdown-toggle\" href=\"$url[2]\"><span> $url[0]</span></a>";
                                    echo "<ul class=\"dropdown-menu dropdown-submenu menuBar\">";

                                    foreach($url['expand'] as $url2)
                                    {
                                        if(in_array($url2[3], GlobalPin))
                                        {
                                            echo "<li><a class=\"dropdown-item\" href=\"$url2[2]\">$url2[0]</a></li>";
                                        }
                                    }

                                    echo "</ul>";
                                    echo "</li>";
                                }
                            }
                            else 
                            {
                                if(in_array($url[3], GlobalPin))
                                {
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
                foreach($menuList as $innerList)
                {
                    if(!empty(array_intersect($innerList['pin'], GlobalPin)))
                    {
                        $li = $innerList[3] == 'y' ? "class=\"nav-item dropdown\"" : "class=\"nav-item\"";
                        $a = $innerList[3] == 'y' ? "class=\"nav-link dropdown-toggle\" data-bs-toggle=\"collapse\" data-bs-target=\"#$innerList[0]-collapse\" aria-expanded=\"false\"" : "class=\"nav-link\" href=\"$innerList[2]\"";

                        echo "<li $li>";
                        echo "<a $a href=\"#\"><i class=\"$innerList[1]\"></i><span> $innerList[0]</span></a>";
                        echo "<div class=\"collapse\" id=\"$innerList[0]-collapse\">";
                        echo "<ul class=\"list-unstyled collapse-menu\">";
                        foreach($innerList['expand'] as $url)
                        {
                            if(isset($url['expand']))
                            {
                                if(!empty(array_intersect($url['pin'], GlobalPin)))
                                {
                                    $idCollapse = str_replace(" ","-",$url[0]);

                                    $li = $url[3] == 'y' ? "class=\"nav-item dropdown\"" : "class=\"nav-item\"";
                                    $a = $url[3] == 'y' ? "class=\"nav-link dropdown-toggle\" data-bs-toggle=\"collapse\" data-bs-target=\"#$idCollapse-collapse\" aria-expanded=\"false\"" : "class=\"nav-link\" href=\"$url[2]\"";

                                    echo "<li $li>";
                                    echo "<a $a href=\"#\"><i class=\"$url[1]\"></i><span> $url[0]</span></a>";
                                    echo "<div class=\"collapse\" id=\"$idCollapse-collapse\">";
                                    echo "<ul class=\"list-unstyled collapse-menu\">";

                                    foreach($url['expand'] as $url2)
                                    {
                                        if(in_array($url2[3], GlobalPin))
                                        {
                                            echo "<li><a class=\"nav-link\" href=\"$url2[2]\"><i class=\"$url2[1]\"></i><span> $url2[0]<span></a></li>";
                                        }
                                    }

                                    echo "</ul>";
                                    echo "</div>";
                                    echo "</li>";
                                }
                            }
                            else
                            {
                                if(in_array($url[3], GlobalPin))
                                {
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

(function ($) {
sidebar_toggleBtn.on("click", function () {
    if(sidebar.hasClass("active")) {
        sidebar.toggleClass("close",true);
        opacityBackground.hide();

        // timeout value based on .close css transition (0.3s)
        setTimeout(() => {
            sidebar.removeClass('active');
            sidebar.removeClass('close');
        }, 500);
    }   
    else {
        sidebar.toggleClass("active",true);
        sidebar.toggleClass("close",false);
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

