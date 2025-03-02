<?php
$pageTitle = "Tax";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/tax.php';
$deleteRedirectPage = $SITEURL . '/finance/tax_table.php';
$result = getData('*', '', '', TAX_SETT, $finance_connect);
if (!$result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('tax_table');
    });
</script>



<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">
            <div class="col-12 col-md-11">


                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Tax </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped" id="tax_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Tax Country</th>
                            <th scope="col">Tax Name</th>
                            <th scope="col">Tax Percentage</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                         while ($row = $result->fetch_assoc()) {
                            if (isset($row['name'], $row['id']) && !empty($row['name'])) {
    
                                $country = getData('name', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                                $row3 = $country->fetch_assoc();
                        ?>
    
                                <tr>
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++; ?></th>
                                    <td scope="row" class="btn-container">
                                    <?php renderViewEditButton("View", $redirect_page, $row, $pinAccess); ?>
                                    <?php renderViewEditButton("Edit", $redirect_page, $row, $pinAccess, $act_2); ?>
                                    <?php renderDeleteButton($pinAccess, $row['id'], $row['percentage'], $row['remark'], $pageTitle, $redirect_page, $deleteRedirectPage); ?>
                                    </td>
                                    <td scope="row"><?php if (isset($row3['name'])) echo $row3['name'] ?></td>
                                    <td scope="row"><?= $row['name'] ?></td>
                                    <td scope="row"><?php if (isset($row['percentage'])) echo $row['percentage'] ?></td>
                                    <td scope="row"><?php if (isset($row['remark'])) echo $row['remark'] ?></td>
                                </tr>
                        <?php }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Tax Country</th>
                            <th scope="col">Tax Name</th>
                            <th scope="col">Tax Percentage</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        //to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
        dropdownMenuDispFix();
        //to resize table with bootstrap 5 classes
        datatableAlignment('table');
        setButtonColor();
    </script>
</body>

</html>