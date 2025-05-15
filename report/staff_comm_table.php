<?php
$pageTitle = "Staff Commission Report";
$isFinance = 1;
$currentPagePin = 124;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';
include ROOT.'/include/access.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/agent.php';
$deleteRedirectPage = $SITEURL . '/finance/agent_table.php';
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

    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">
            <div class="col-12 col-md-11">


                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                                class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>
                </div>
                <?php
                if (!$result) {
                    echo '<div class="text-center"><h4>No Result!</h4></div>';
                } else {
                    ?>
                    <table class="table table-striped" id="agent_table">
                        <thead>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col">S/N</th>
                                <th scope="col">Name</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Person In Charge</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Email</th>
                                <th scope="col">Country</th>
                                <th scope="col">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) {

                                $brand = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                                $row2 = $brand->fetch_assoc();

                                $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                                $usr = $pic->fetch_assoc();

                                $country = getData('name', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                                $row3 = $country->fetch_assoc();
                                ?>

                                <tr>
                                    <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                    <th scope="row"><?= $num++; ?></th>
                                    <td scope="row"><?= $row['name'] ?></td>
                                    <td scope="row"><?= isset($row2['name']) ? $row2['name'] : '' ?></td>
                                    <td scope="row"><?= isset($usr['name']) ? $usr['name'] : '' ?></td>
                                    <td scope="row"><?= $row['contact'] ?></td>
                                    <td scope="row"><?= $row['email'] ?></td>
                                    <td scope="row"><?= $row3['name'] ?></td>
                                    <td scope="row"><?= $row['remark'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col">S/N</th>
                                <th scope="col">Name</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Person In Charge</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Email</th>
                                <th scope="col">Country</th>
                                <th scope="col">Remark</th>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            </div>
        </div>
</body>

<script>


    dropdownMenuDispFix();
    datatableAlignment('agent_table');
    setButtonColor();
</script>

</html>