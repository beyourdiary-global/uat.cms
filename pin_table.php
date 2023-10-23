<?php
$pageTitle = "Pin Table";
include 'menuHeader.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/pin.php';
$result = getData('*','',PIN,$connect);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/pin.css">

</head>

<script>
$( document ).ready(() => {
    /**
     oufei 20231014
     common.fun.js
     function(id)
     create DataTable (sortable table)
    */
    createSortingTable('pin_table');
});    
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> Pin</p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>Pin</h2>
                        <div class="mt-auto mb-auto">
                            <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page."?act=".$act_1?>"><i class="fa-solid fa-plus"></i> Add Pin </a>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="pin_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                        <th scope="row"><?= $num; $num++ ?></th>
                        <td scope="row"><?= $row['name'] ?></td>
                        <td scope="row"><?= $row['remark'] ?></td>
                        <td scope="row">
                        <div class="dropdown" style="text-align:center">
                            <a
                                class="text-reset me-3 dropdown-toggle hidden-arrow"
                                href="#"
                                id="actionDropdownMenu"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                <li>
                                <a class="dropdown-item" href="<?= $redirect_page."?id=".$row['id']?>">View</a>
                                </li>
                                <li>
                                <a class="dropdown-item" href="<?= $redirect_page."?id=".$row['id'].'&act='.$act_2?>">Edit</a>
                                </li>
                                <li>
                                <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id']?>',['<?= $row['name'] ?>','<?= $row['remark'] ?>'],'Pin','<?= $redirect_page ?>','<?= $SITEURL ?>/pin_table.php','D')">Delete</a>
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
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Remark</th>
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
datatableAlignment('pin_table');
</script>
</html>