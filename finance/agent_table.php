<?php
$pageTitle = "Agent";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/agent.php';
$result = getData('*', '', '', AGENT, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('agent_table');
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
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Agent </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="agent_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Email</th>
                            <th scope="col">Country</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {

                            $brand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $row2 = $curr->fetch_assoc();
    
                            $country = getData('country', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                            $row3 = $bank->fetch_assoc();
    
                            $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $usr = $pic->fetch_assoc();
                        
                        ?>

                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <td scope="row"><?= $row['name'] ?></td>
                                    <td scope="row"><?= $row2['brand'] ?></td>
                                    <td scope="row"><?= $usr['person_in_charge'] ?></td>
                                    <td scope="row"><?= $row['contact'] ?></td>
                                    <td scope="row"><?= $row['email'] ?></td>
                                    <td scope="row"><?= $row3['country'] ?></td>
                                    <td scope="row"><?= $row['remark'] ?></td>
                                <td scope="row">
                                    <div class="dropdown" style="text-align:center">
                                        <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                            <li>
                                                <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                                    <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] ?>">View</a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                                    <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                                    <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['transactionID'] ?>','<?= $row['remark'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/agent_table.php','D')">Delete</a>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Person In Charge</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Email</th>
                            <th scope="col">Country</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
    dropdownMenuDispFix();
    datatableAlignment('agent_table');
    setButtonColor();
</script>

</html>
