<?php

$pageTitle = "Barcode Generator";
include 'menuHeader.php';
include "./header/phpqrcode/qrlib.php";
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);

$redirect_page = '';
$tblname = PROD;
$product_id = input('id');
$act = input('act');

//set it to writable location, a place for temp generated PNG files
$PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
if (!file_exists($PNG_TEMP_DIR)) {
    mkdir($PNG_TEMP_DIR, 0777, true);
}

// to display data to input
if ($product_id) {
    $rst = getData('*', "id = '$product_id'", '', $tblname, $connect);

    if ($rst != false) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } else {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
    }
}

//html PNG location prefix
$PNG_WEB_DIR = 'temp/';

//processing form input
$errorCorrectionLevel = 'H';
$matrixPointSize = 2;

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'generate':
            $product_name = postSpaceFilter('product');
            $product = postSpaceFilter('product_hidden');
            $page_no = postSpaceFilter('page_no');
            $warehouse = postSpaceFilter('warehouse');

            if (!$product && $product == '')
                $err = 'Please select the product to generate barcode.';

            if (!$page_no || !($page_no != '0'))
                $err2 = 'Page Number cannot be empty or less than 1.';

            if ($warehouse == 'noValue')
                $err3 = 'Please select the warehouse to generate barcode';

            if (($product && $product) && ($page_no || ($page_no != '0')) && ($warehouse != 'noValue')) {
                $rst_projInfo = getData("barcode_prefix,barcode_next_number", "id='1'", '', PROJ, $connect);
                if (!$rst_projInfo) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $projInfo = $rst_projInfo->fetch_assoc();

                if ($projInfo) {
                    $barcode_prefix = $projInfo['barcode_prefix'];
                    $barcode_next_number = $projInfo['barcode_next_number'];

                    $finalBarcodeNo = $barcode_next_number + $page_no;
                    echo '<div id="printArea" class="container2">';
                    for ($x = 1; $x <= $page_no; $x++) {
                        $usr_id = $_SESSION['userid'];
                        $_SESSION['barcode_next_number'] = $barcode_next_number;
                        $_SESSION['x'] = $x;
                        $_SESSION['product'] = $product;
                        $_SESSION['warehouse'] = $warehouse;
                        $_SESSION['qr_scanned'] = true;
                        $qrCode_url = $SITEURL . "/stockRecord.php?barcode=" . ($barcode_next_number + $x) . "&prdid=" . $product . "&whseid=" . $warehouse . "&usr_id=" . $usr_id;
                        $filename = $PNG_TEMP_DIR . 'barcode' . md5($qrCode_url . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                        QRcode::png($qrCode_url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                        echo '<div class="column"><img src="' . $PNG_WEB_DIR . basename($filename) . '" />' . '<p class="title">' . $product_name . ' ' . ($barcode_next_number + $x) . '
                            </p>
                        </div>';
                    }
                    $sqlupd = "UPDATE projects SET barcode_next_number = '" . $finalBarcodeNo . "' WHERE id = '1'";
                    $query2 = mysqli_query($connect, $sqlupd);
                    // Automatically trigger the print action using JavaScript
                    echo '<script>
                        window.onload = function() {
                            var header = document.querySelector(".sticky-top");
                            var form = document.querySelector("form");
                            header.style.display = "none";
                            form.style.display = "none";
                            window.print();
                        }
                        window.onafterprint = function() {
                            // Print page has been closed
                            // Remove the content of the container
                            var container = document.querySelector("#printArea");
                            container.innerHTML = "";
                            
                            // Show the form again
                            var header = document.querySelector(".sticky-top");
                            var form = document.querySelector("form");
                            header.style.display = "block";
                            form.style.display = "block";
                        }
                    </script>';
                    echo '</div>';
                }
            }
            break;
        case 'back':
            break;
    }
}

/* 
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Product","","'.$redirect_page.'","'.$act.'");</script>';
} 
*/
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/barcode_generator.css">
</head>

<body>

    <div class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6">
            <form id="prodForm" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group my-5">
                            <h2>
                                Generate Barcode
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="pkg_lbl" for="product">Product Name</label>
                            <input class="form-control" type="text" name="product" id="product" value="<?php
                                                                                                        unset($echoVal);
                                                                                                        if (isset($product) && $product != '')
                                                                                                            $echoVal = $product;
                                                                                                        else if (isset($dataExisted))
                                                                                                            $echoVal = $row['id'];

                                                                                                        if (isset($echoVal)) {
                                                                                                            $rst = getData('name', "id = '$echoVal'", '', $tblname, $connect);
                                                                                                            if (!$rst) {
                                                                                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                                                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                                                                                            }
                                                                                                            $row = $rst->fetch_assoc();
                                                                                                            if (isset($row['name'])) echo $row['name'];
                                                                                                        }
                                                                                                        ?>">

                            <input type="hidden" name="product_hidden" id="product_hidden" value="<?php
                                                                                                    if (isset($product) && $product != '')
                                                                                                        echo $product;
                                                                                                    else if (isset($dataExisted) && isset($row['name']))
                                                                                                        echo $row['name'];
                                                                                                    ?>">

                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="page_no_lbl" for="page_no">Page No.</label>
                            <input class="form-control" type="text" name="page_no" id="page_no" value="<?php
                                                                                                        if (isset($page_no))
                                                                                                            echo $page_no;
                                                                                                        ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="warehouse_lbl" for="warehouse">Warehouse</label>
                            <select class="form-select" name="warehouse" id="warehouse">
                                <option value="noValue" <?php if (!isset($warehouse)) echo 'selected' ?>>--Please Choose--</option>
                                <?php
                                $rst_warehouse_list = getData("id,name", '', '', WHSE, $connect);
                                if (!$rst_warehouse_list) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                while ($warehouse_list = $rst_warehouse_list->fetch_assoc()) {
                                    $whse_id = $warehouse_list['id'];
                                    $whse_name = $warehouse_list['name'];

                                    $selected = '';
                                    if (isset($warehouse))
                                        if ($warehouse == $whse_id)
                                            $selected = "selected";

                                    echo "<option value=\"$whse_id\" $selected>$whse_name</option>";
                                }
                                ?>
                            </select>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err3)) echo $err3; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center">
                            <button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="generate">Generate</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>
<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ''; ?>";

    checkCurrentPage(page, action);
    setButtonColor();

    $(document).ready(function() {
        var packageName = $("#product");

        packageName.keyup(function(e) {
            var param = {
                search: $(this).val(), // search value
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= $tblname ?>' // json filename (generated when login)
            }
            var arr = searchInput(param, '<?= $SITEURL ?>');
        });
        packageName.change(function() {
            if ($(this).val() == '')
                $('#' + $(this).attr('id') + '_hidden').val('');
        });
    });
</script>

</html>