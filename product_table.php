<?php
$pageTitle = "Product Table";
include 'menuHeader.php';

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = 'product.php';
$result = getData('*','',PROD,$connect);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">

</head>

<script>
$( document ).ready(() => {
    createSortingTable('product_table');
}); 
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> Product</p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>Product</h2>
                        <div class="mt-auto mb-auto">
                            <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page."?act=".$act_1?>"><i class="fa-solid fa-plus"></i> Add Product </a>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="product_table">
                <thead>
                    <tr>
                        <th scope="col" style="display:none">ID</th>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Cost</th>
                        <th scope="col">Weight</th>
                        <th scope="col">Parent Product</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <th scope="row" style="display:none"><?= $row['id'] ?></th>
                        <th scope="row"><?= $num; $num++ ?></th>
                        <td scope="row"><?= $row['name'] ?></td>
                        <td scope="row">
                            <?php
                                $cur_unit_id = $row['currency_unit'];
                                $rst2 = getData('unit',"id = '$cur_unit_id'",CUR_UNIT,$connect);
                                $row2 = $rst2->fetch_assoc();
                                echo $row2['unit'].' '.$row['cost'];
                            ?>
                        </td>
                        <td scope="row">
                            <?php
                                $wgt_unit_id = $row['weight_unit'];
                                $rst2 = getData('unit',"id = '$wgt_unit_id'",WGT_UNIT,$connect);
                                $row2 = $rst2->fetch_assoc();
                                echo $row['weight'].' '.$row2['unit'];
                            ?>
                        </td>
                        <td scope="row">
                            <?php
                                if($row['parent_product'] != '')
                                {
                                    $product_prod = $row['parent_product'];
                                    $rst2 = getData('name',"id = '$product_prod'",PROD,$connect);
                                    $row2 = $rst2->fetch_assoc();
                                    echo $row2['name'];
                                }
                            ?>
                        </td>
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
                                <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id']?>',['<?= $row['name'] ?>'],'Product','<?= $redirect_page ?>','product_table.php','D')">Delete</a>
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
                        <th scope="col">Cost</th>
                        <th scope="col">Weight</th>
                        <th scope="col">Parent Product</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>

</div>

</body>
<script>
dropdownMenuDispFix();
datatableAlignment('product_table');
</script>
</html>