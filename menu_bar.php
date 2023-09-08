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
        'Settings',
        'mdi mdi-cog',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Pin', 'mdi mdi-pin', 'pin_table.php', '1'),
            array('Pin Group', 'mdi mdi-ungroup', 'pin_group_table.php', '2'),
            array('User Group', 'mdi mdi-account-wrench-outline', 'user_group_table.php', '3'),
            array('Bank', 'mdi mdi-bank', 'bank_table.php', '8'),
            array('Brand', 'mdi mdi-label-outline', 'brand_table.php', '9'),
            array('Currency Unit', 'mdi mdi-currency-usd', 'currency_unit_table.php', '10'),
            array('Currencies', 'mdi mdi-swap-horizontal', 'currencies_table.php', '11'),
            array('Employment Type Status', 'mdi mdi-account-question-outline', 'em_type_status_table.php', '12'),
            array('Marital Status', 'mdi mdi-account-heart-outline', 'marital_status_table.php', '13'),
            array('Platform', 'mdi mdi-home-outline', 'platform_table.php', '14'),
            array('Product Status', 'mdi mdi-package-variant-closed', 'prod_status_table.php', '15'),
            array('Warehouse', 'mdi mdi-warehouse', 'warehouse.php', '16'),
            array('Audit Log', 'mdi mdi-text-box-search-outline', 'audit_log.php', '18')
        ),
        'pin' => array('1','2','3','8','9','10','11','12','13','14','15','16','18')
    ),
    array(
        'Employees',
        'mdi mdi-account-outline',
        'javascript:void(0)',
        'y',
        'expand' => array(
            array('Designations', 'mdi mdi-badge-account-outline', 'designations_table.php', '4'),
            array('Departments', 'mdi mdi-domain', 'department_table.php', '5'),
            array('Holidays', 'mdi mdi-calendar-star', 'holiday_table.php', '6'),
            array('Rate Checking', 'mdi mdi-package-variant', 'rate_checking.php', '17')
        ),
        'pin' => array('4','5','6','17')
    )
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
                        echo "<a $a href=\"$innerList[2]\"><i class=\"$innerList[1]\"></i><span> $innerList[0]</span><a>";
                        echo "<ul class=\"dropdown-menu menuBar\">";
                        foreach($innerList['expand'] as $url)
                        {
                            if(in_array($url[3], GlobalPin))
                            {
                                echo "<li><a class=\"dropdown-item\" href=\"$url[2]\">$url[0]</a></li>";
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
                        echo "<a $a href=\"#\"><i class=\"$innerList[1]\"></i><span> $innerList[0]</span><a>";
                        echo "<div class=\"collapse\" id=\"$innerList[0]-collapse\">";
                        echo "<ul class=\"list-unstyled collapse-menu\">";
                        foreach($innerList['expand'] as $url)
                        {
                            if(in_array($url[3], GlobalPin))
                            {
                                echo "<li><a class=\"nav-link\" href=\"$url[2]\"><i class=\"$url[1]\"></i><span> $url[0]<span></a></li>";
                            }
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
</aside>
<!-- V.Navbar -->

<script>
(function ($) {
    $("#sidebarCollapse").on("click", function () {
        $("#sidebar").toggleClass("active");

    if($("#sidebar").hasClass("active"))
    {
        $defaultColor = $("body").css('background-color');
        document.body.style.backgroundColor = "rgba(0,0,0,0.2)";
    }   
    else document.body.style.backgroundColor = $defaultColor;
    });
})(jQuery);
</script>

