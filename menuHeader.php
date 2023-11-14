<!DOCTYPE html>
<html>

<head>
    <?php
    include_once "header.php";
    ?>
    <link rel="stylesheet" href="./css/main.css">
</head>

<?php
$img_path = img_server.'themes/';
$rst = getData('*', "id = '1'", PROJ, $connect);

if ($rst != false) {
    $dataExisted = 1;
    $row = $rst->fetch_assoc();
}
?>

<!-- Navbar -->
<div class="sticky-top">
    <nav class="navbar navbar-expand-md topNav p-0">
        <!-- Container wrapper -->
        <div class="container-fluid p-0">
            <!-- Toggle button -->
            <button class="navbar-toggler ps-4" style="height:50px;" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-collapse" aria-expanded="false" id="sidebarCollapse">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Navbar brand -->
            <div class="d-flex align-items-center mx-2">
                <a class="logo_section navbar-brand mx-4" href="#">
                    <img  id="logo" src="
                    <?php
                    if ($dataExisted)
                        echo $img_path  . $row['logo'];
                    else
                        echo img . byd_logo;
                    ?>">
                </a>
            </div>

            <!-- Collapsible wrapper -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Right elements -->
                <div class="container-fluid d-flex justify-content-between">
                    <!-- Title -->
                    <div class="d-flex flex-row align-items-center">
                        <ul class="navbar-nav ms-4">
                            <li class="nav-item menuheader-text">
                                <?php
                                if ($dataExisted)
                                    echo $row['project_title'];
                                else
                                    echo "CMS SYSTEM";
                                ?>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Language -->
                        <div class="dropdown me-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLanguage" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span id="language_name"><i class="flag flag-united-states"></i> English</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-center mt-3" aria-labelledby="navbarDropdownMenuLanguage">
                                <li>
                                    <a class="dropdown-item" href="#"><i class="flag flag-united-states"></i>English</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#"><i class="flag flag-french-guiana"></i>French</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#"><i class="flag flag-spain"></i>Spanish</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#"><i class="flag flag-germany"></i>German</a>
                                </li>
                            </ul>
                        </div>
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="navbarDropdownNotifMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-bell fa-lg"></i>
                                <span class="badge rounded-pill badge-notification badge-color ms-1">3</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right mt-3" aria-labelledby="navbarDropdownNotifMenuLink">
                                <li>
                                    <a class="dropdown-item" href="#">Some news</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">Another news</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="navbarDropdownMessageMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-comment fa-lg"></i>
                                <span class="badge rounded-pill badge-notification badge-color ms-1">8</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right mt-3" aria-labelledby="navbarDropdownMessageMenuLink">
                                <li>
                                    <a class="dropdown-item" href="#">Some news</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">Another news</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </li>
                            </ul>
                        </div>
                        <!-- Avatar -->
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuAvatar" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img id="accpfp" src="<?php echo img . defaultpfp ?>" class="rounded-circle">
                                Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right mt-3" aria-labelledby="navbarDropdownMenuAvatar">
                                <li>
                                    <a class="dropdown-item" href="#">My profile</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">Settings</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= $SITEURL ?>/logout.php">Logout</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Right elements -->
            </div>

            <!-- Toggle button -->
            <div class="navbar-toggler pe-4">
                <div class="dropdown">
                    <button class="nav-link d-flex align-items-center" href="#" id="navbarTogglerMenuAvatar" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-vertical fa-lg"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right mt-4" aria-labelledby="navbarTogglerMenuAvatar">
                        <li>
                            <a class="dropdown-item" href="#">My profile</a>
                            <div class="dropdown-divider my-0"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Settings</a>
                            <div class="dropdown-divider my-0"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= $SITEURL ?>/logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>


        </div>
        <!-- Container wrapper -->
    </nav>
    <!-- Navbar -->
    <?php include "menu_bar.php"; ?>
</div>

</html>