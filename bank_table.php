<?php
$pageTitle = "Bank Table";
include 'menuHeader.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';

$redirect_page = 'bank.php';
$result = getData('*','',BANK,$connect);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">

</head>

<script>
$( document ).ready(() => {
    createSortingTable('bank_table');
}); 
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex justify-content-between">
                <div class="left">
                        <h2>Bank</h2>
                        <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-slash fa-rotate-90 fa-2xs"></i> Bank</p>
                </div>

                <div class="right d-flex">
                    <div class="mt-auto mb-auto">
                        <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page."?act=".$act_1?>"><i class="fa-solid fa-plus"></i> Add Bank </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped" id="bank_table">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Remark</th>
                            <th scope="col" id="action_col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <th scope="row"><?= $row['id'] ?></th>
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
                                    <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id']?>',['<?= $row['name'] ?>','<?= $row['remark'] ?>'],'Bank','<?= $redirect_page ?>','bank_table.php','D')">Delete</a>
                                    </li>
                                </ul>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

</div>

</body>
<script>
dropdownMenuDispFix();
</script>
</html>