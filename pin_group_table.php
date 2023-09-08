<?php
include 'menuHeader.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';

$num = 1;  //for numbering
$result = getData('*','',PIN_GRP,$connect);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/pin.css">

</head>

<script>
$( document ).ready(() => {
    createSortingTable('pin_group_table');
});    
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex justify-content-between">
                <div class="left">
                        <h2>Pin Group</h2>
                        <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-slash fa-rotate-90 fa-2xs"></i> Pin Group</p>
                </div>

                <div class="right d-flex">
                    <div class="mt-auto mb-auto">
                        <a class="btn btn-sm btn-rounded btn-primary" name="addPinBtn" id="addPinBtn" href="pin_group.php?act=<?php echo $act_1?>"><i class="fa-solid fa-plus"></i> Add Pin Group </a>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="pin_group_table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Pins</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th scope="row"><?php echo $num ?></th>
                        <td scope="row"><?php echo $row['name'] ?></td>
                        <td scope="row">
                            <?php 
                                $pin_arr = explode(",", $row['pins']);
                                $pinname_arr = array();
                                foreach($pin_arr as $val)
                                {
                                    $pinname_qry = "SELECT `name` FROM ".PIN." WHERE id = '$val'";
                                    $pinname_result = mysqli_query($connect, $pinname_qry);
                                    $pinname_row = $pinname_result->fetch_assoc();
                                    array_push($pinname_arr, $pinname_row['name']);
                                }
                                echo implode(", ", $pinname_arr);
                            ?>
                        </td>
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
                                <a class="dropdown-item" href="pin_group.php?id=<?php echo $row['id']?>">View</a>
                                </li>
                                <li>
                                <a class="dropdown-item" href="pin_group.php?id=<?php echo $row['id'].'&act='.$act_2?>">Edit</a>
                                </li>
                                <li>
                                <a class="dropdown-item" onclick="confirmationDialog('<?php echo $row['id']?>',['<?php echo $row['name'] ?>','<?php echo $row['remark'] ?>'],'Pin Group','pin_group.php','pin_group_table.php','D')">Delete</a>
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