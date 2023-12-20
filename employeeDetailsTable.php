<?php
$pageTitle = "Employee Details";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/employeeDetails.php';
$result = getData('*', '', '', EMPPERSONALINFO, $connect);

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">

</head>

<script>
    $(document).ready(() => {
        /**
         oufei 20231014
         common.fun.js
         function(id)
         create DataTable (sortable table)
        */
        createSortingTable('employeeDetailsTable');
    });
</script>

<body>

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
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add <?php echo $pageTitle ?> </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="employeeDetailsTable">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col">S/N</th>
                        <th scope="col">Name</th>
                        <th scope="col">Identity Type</th>
                        <th scope="col">Identity Number</th>
                        <th scope="col">Email</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Birthday</th>
                        <th scope="col">Race</th>
                        <th scope="col">Residence </th>
                        <th scope="col">Nationality </th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Alternate Phone Number</th>
                        <th scope="col">Address Line 1</th>
                        <th scope="col">Address Line 2</th>
                        <th scope="col">City</th>
                        <th scope="col">State</th>
                        <th scope="col">Postcode</th>
                        <th scope="col">Marital status</th>
                        <th scope="col">Number of kids</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                            <th scope="row"><?= $num;
                                            $num++ ?></th>
                            <td scope="row"><?= $row['name'] ?></td>

                            <?php
                            $resultIDType = getData('*', 'id = ' . $row['id_type'], '', ID_TYPE, $connect);

                            while ($rowIDType = $resultIDType->fetch_assoc()) {
                                echo "<td scope='row'>" . $rowIDType['name'] . "</td>";
                            }
                            ?>

                            <td scope="row"><?= $row['id_number'] ?></td>
                            <td scope="row"><?= $row['email'] ?></td>
                            <td scope="row"><?= $row['gender'] ?></td>
                            <td scope="row"><?= $row['date_of_birth'] ?></td>

                            <?php
                            $resultRace = getData('*', 'id = ' . $row['race_id'], '', RACE, $connect);

                            while ($rowRace = $resultRace->fetch_assoc()) {
                                echo "<td scope='row'>" . $rowRace['name'] . "</td>";
                            }
                            ?>

                            <td scope="row"><?= $row['residence_status'] ?></td>

                            <?php
                            $resultNationality = getData('*', 'id = ' . $row['nationality'], '', 'countries', $connect);

                            while ($rowNationality = $resultNationality->fetch_assoc()) {
                                echo "<td scope='row'>" . $rowNationality['name'] . "</td>";
                            }
                            ?>

                            <td scope="row"><?= $row['phone_number'] ?></td>
                            <td scope="row"><?= $row['phone_number'] ?></td>
                            <td scope="row"><?= $row['address_line_1'] ?></td>
                            <td scope="row"><?= $row['address_line_2'] ?></td>
                            <td scope="row"><?= $row['city'] ?></td>
                            <td scope="row"><?= $row['state'] ?></td>
                            <td scope="row"><?= $row['postcode'] ?></td>

                            <?php

                            $resultMrtSts = getData('*', 'id = ' . $row['marital_status'], '', MRTL_STATUS, $connect);

                            while ($rowMrtSts = $resultMrtSts->fetch_assoc()) {
                                echo "<td scope='row'>" . $rowMrtSts['name'] . "</td>";
                            }
                            ?>

                            <td scope="row"><?= $row['no_of_children'] ?></td>
                            <td scope="row">
                                <div class="dropdown" style="text-align:center">
                                    <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                        <li>
                                            <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                                <a class="dropdown-item" href="<?php echo $redirect_page ?>?id=<?php echo $row['id'] ?>">View</a>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                                <a class="dropdown-item" href="<?php echo $redirect_page ?>?id=<?php echo $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                                <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['name'] ?>','<?= $row['id_number'] ?>','<?= $row['email'] ?>'],'<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/employeeDetailsTable.php','D')">Delete</a>
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
                        <th scope="col">S/N</th>
                        <th scope="col">Name</th>
                        <th scope="col">Identity Type</th>
                        <th scope="col">Identity Number</th>
                        <th scope="col">Email</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Birthday</th>
                        <th scope="col">Race</th>
                        <th scope="col">Residence </th>
                        <th scope="col">Nationality </th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Alternate Phone Number</th>
                        <th scope="col">Address Line 1</th>
                        <th scope="col">Address Line 2</th>
                        <th scope="col">City</th>
                        <th scope="col">State</th>
                        <th scope="col">Postcode</th>
                        <th scope="col">Marital status</th>
                        <th scope="col">Number of kids</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
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
    datatableAlignment('employeeDetailsTable');
</script>

</html>