<?php
$pageTitle = "Holiday Table";
include 'menuHeader.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = 'holiday.php';
$result = getData('*','',HOLIDAY,$connect);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">

</head>

<script>
$( document ).ready(() => {
    createSortingTable('holiday_table');
});    
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex flex-column flex-md-row justify-content-between mb-3">
                <div class="left">
                        <h2>Holidays</h2>
                        <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-slash fa-rotate-90 fa-2xs"></i> Holidays</p>
                </div>

                <div class="right d-flex">
                    <div class="mt-auto mb-auto">
                        <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page."?act=".$act_1?>"><i class="fa-solid fa-plus"></i> Add Holiday </a>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="holiday_table">
                <thead>
                    <tr>
                        <th scope="col" style="display:none">ID</th>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Date</th>
                        <th scope="col">Day</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th scope="row" style="display:none"><?= $row['id'] ?></th>
                        <th scope="row"><?= $num; $num++ ?></th>
                        <td scope="row"><?= $row['name'] ?></td>
                        <td scope="row"><?= $date = date("j M Y", strtotime($row['date'])) ?></td>
                        <td scope="row"><?= $day = date("l", strtotime($row['date'])) ?></td>
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
                                <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id']?>',['<?= $row['name'] ?>','<?= $date.' '.$day?>'],'Holiday','<?= $redirect_page ?>','holiday_table.php','D')">Delete</a>
                                </li>
                            </ul>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col" style="display:none">ID</th>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Date</th>
                        <th scope="col">Day</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>

</div>

</body>
<script>
dropdownMenuDispFix();

$(window).resize(() => {
    datatableAlignment('holiday_table');
});

$(window).load(() => {
    datatableAlignment('holiday_table');
});
</script>
</html>