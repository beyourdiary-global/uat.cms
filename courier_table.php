<?php
$pageTitle = "Courier";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/courier.php';
$deleteRedirectPage = $SITEURL . '/courier_table.php';
$result = getData('*', '', '', COURIER, $connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('courier_table');
    });
</script>

<style>
    .btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        margin: 3px;
    }

    .btn-container {
        white-space: nowrap;
    }
</style>

<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-11">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                            class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2><?php echo $pageTitle ?></h2>
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                    href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                    Courier </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if (!$result) {
                echo '<div class="text-center"><h4>No Result!</h4></div>';
            } else {
                ?>
                <table class="table table-striped" id="courier_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Courier ID</th>
                            <th scope="col">Tracking Link</th>
                            <th scope="col">Courier Name</th>
                            <th scope="col">Courier Country</th>
                            <th scope="col">Taxable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            if (isset($row['name'], $row['id']) && !empty($row['name'])) {
                                $row2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                                $countries = $row2->fetch_assoc();
                                ?>

                                <tr>
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++; ?></th>
                                    <td scope="row" class="btn-container">
                                        <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess); ?>
                                        <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2); ?>
                                        <?php renderDeleteButton($pinAccess, $row['id'], $row['id'], $row['name'], $pageTitle, $redirect_page, $deleteRedirectPage); ?>
                                    </td>
                                    <td scope="row"><?php if (isset($row['id']))
                                        echo $row['id'] ?></td>
                                        <td scope="row"><?= $row['tracking_link'] ?></td>
                                    <td scope="row"><?= $row['name'] ?></td>
                                    <td scope="row"><?php if (isset($countries['nicename']))
                                        echo $countries['nicename'] ?></td>
                                        <td scope="row"><?php if (isset($row['taxable'])) {
                                        echo ($row['taxable'] == 'Y') ? 'Yes' : 'No';
                                    } ?></td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Courier ID</th>
                            <th scope="col">Tracking Link</th>
                            <th scope="col">Courier Name</th>
                            <th scope="col">Courier Country</th>
                            <th scope="col">Taxable</th>
                        </tr>
                    </tfoot>
                </table>
            <?php } ?>
        </div>
    </div>

</body>

<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";

    checkCurrentPage(page, action);
    /**
      oufei 20231014
      common.fun.js
      function(void)
      to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
    */
    dropdownMenuDispFix();

    /**
      oufei 20231014
      common.fun.js
      function(id)
      to resize table with bootstrap 5 classes
    */
    datatableAlignment('courier_table');
</script>

</html>