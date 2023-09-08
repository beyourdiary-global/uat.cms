<?php
include 'menuHeader.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';

$num = 1;  //for numbering
$result = getData('*','',CUR_UNIT,$connect);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">

</head>

<script>
$( document ).ready(() => {
    createSortingTable('currency_unit_table');
});    
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex justify-content-between">
                <div class="left">
                        <h2>Currency Unit</h2>
                        <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-slash fa-rotate-90 fa-2xs"></i> Currency Unit</p>
                </div>

                <div class="right d-flex">
                    <div class="mt-auto mb-auto">
                        <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="currency_unit.php?act=<?php echo $act_1?>"><i class="fa-solid fa-plus"></i> Add Currency Unit </a>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="currency_unit_table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Unit</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th scope="row"><?php echo $num ?></th>
                        <td scope="row"><?php echo $row['unit'] ?></td>
                        <td scope="row"><?php echo $row['remark'] ?></td>
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
                                <i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                <li>
                                <a class="dropdown-item" href="currency_unit.php?id=<?php echo $row['id']?>">View</a>
                                </li>
                                <li>
                                <a class="dropdown-item" href="currency_unit.php?id=<?php echo $row['id'].'&act='.$act_2?>">Edit</a>
                                </li>
                                <li>
                                <a class="dropdown-item" onclick="confirmationDialog('<?php echo $row['id']?>',['<?php echo $row['unit'] ?>','<?php echo $row['remark'] ?>'],'Currency Unit','currency_unit.php','currency_unit_table.php','D')">Delete</a>
                                </li>
                            </ul>
                            </div>
                        </td>
                    </tr>
                    <?php $num++; } ?>
                </tbody>
            </table>
        </div>

</div>

</body>
</html>