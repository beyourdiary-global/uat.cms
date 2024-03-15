<?php
$pageTitle = "Facebook Customer Record (Deals)";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$reg_member_page = $SITEURL . '/urb_cust_reg.php';

$redirect_page = $SITEURL . '/fb_cust_deals.php';
$deleteRedirectPage = $SITEURL . '/fb_cust_deals_table.php';
$result = getData('*', '', '', FB_CUST_DEALS, $connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('fb_cust_deals');
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

        <div class="col-12 col-md-8">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                            class="fa-solid fa-chevron-right fa-xs"></i>
                        <?php echo $pageTitle ?>
                    </p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>
                            <?php echo $pageTitle ?>
                        </h2>
                        <?php if ($result) { ?>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        Record </a>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php
            if (!$result) {
                echo '<div class="text-center"><h4>No Result!</h4></div>';
            } else {
                ?>

                <table class="table table-striped" id="fb_cust_deals">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Name</th>
                            <th scope="col">Facebook Link</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Sales Person In Charge</th>
                            <th scope="col">Country</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Facebook Page</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Series</th>
                            <th scope="col">Shipping Receiver Name</th>
                            <th scope="col">Shipping Receiver Address</th>
                            <th scope="col">Shipping Receiver Contact</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) {
                            $q1 = getData('name', "id='" . $row['sales_pic'] . "'", '', USR_USER, $connect);
                            $pic = $q1->fetch_assoc();

                            $q2 = getData('nicename', "id='" . $row['country'] . "'", '', COUNTRIES, $connect);
                            $country = $q2->fetch_assoc();

                            $q3 = getData('name', "id='" . $row['brand'] . "'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            $q4 = getData('name', "id='" . $row['series'] . "'", '', BRD_SERIES, $connect);
                            $series = $q4->fetch_assoc();


                            //fb page
                            $q6 = getData('name', "id='" . $row['fb_page'] . "'", '', FB_PAGE_ACC, $finance_connect);
                            $fb_page = $q6->fetch_assoc();

                            //channel
                            $q7 = getData('name', "id='" . $row['channel'] . "'", '', CHANEL_SC_MD, $finance_connect);
                            $channel = $q7->fetch_assoc();
                            ?>

                            <tr>
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th scope="row">
                                    <?= $num++; ?>
                                </th>
                                <td scope="row" class="btn-container">
                                <div class="d-flex align-items-center">
                                     <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                        <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['id'] ?>"><i class="fas fa-eye"></i></a>
                                    <?php endif; ?>
                                    <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                        <a class="btn btn-warning me-1" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                         <a class="btn btn-danger" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>','<?= $row['contact'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i class="fas fa-trash-alt"></i></a>
                                    <?php endif; ?>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <?php 
                                         $member_exist = getData('name', "name='" . $row['id'] . "'", '', URBAN_CUST_REG, $connect); 
                
                                         if ($member_exist->fetch_assoc()) {
                                            $reg_url = $reg_member_page . "?id=" . $row['id'] . '&act=' . $act_2;
                                         } else {
                                            $reg_url = $reg_member_page . "?id=" . $row['id'] . '&act=' . $act_1;
                                        }
                                        ?>
                                        <li><a class="dropdown-item" href="<?= $reg_url ?>">Urbanism Member</a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>

                                <td scope="row">
                                    <?= $row['name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['fb_link'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['contact'] ?>
                                </td>
                                <td scope="row">
                                    <?= $pic['name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $country['nicename'] ?>
                                </td>
                                <td scope="row">
                                    <?= $brand['name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $fb_page['name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $channel['name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $series['name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['ship_rec_name'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['ship_rec_add'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['ship_rec_contact'] ?>
                                </td>
                                <td scope="row">
                                    <?= $row['remark'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col" id="action_col">Action</th>
                            <th scope="col">Name</th>
                            <th scope="col">Facebook Link</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Sales Person In Charge</th>
                            <th scope="col">Country</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Facebook Page</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Series</th>
                            <th scope="col">Shipping Receiver Name</th>
                            <th scope="col">Shipping Receiver Address</th>
                            <th scope="col">Shipping Receiver Contact</th>
                            <th scope="col">Remark</th>
                        </tr>
                    </tfoot>
                </table>
            <?php } ?>
        </div>


    </div>

</body>
<script>
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
    datatableAlignment('fb_cust_deals');
</script>

</html>