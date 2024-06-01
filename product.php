<?php
$pageTitle = "Product";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = PROD;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/product_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);
generateDBData(PROD, $connect);
//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, '', $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['name'] . "</b> from <b><i>$tblName Table</i></b>.";
    }

    $log = [
        'log_act' => $pageAction,
        'cdate'   => $cdate,
        'ctime'   => $ctime,
        'uid'     => USER_ID,
        'cby'     => USER_ID,
        'act_msg' => $viewActMsg,
        'page'    => $pageTitle,
        'connect' => $connect,
    ];

    audit_log($log);
}

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $prod_name = postSpaceFilter('prod_name');
            $prod_brand = postSpaceFilter('prod_brand_hidden');
            $prod_wgt = postSpaceFilter('prod_wgt');
            $prod_wgt_unit = postSpaceFilter('prod_wgt_unit_hidden');
            $prod_cost = postSpaceFilter('prod_cost');
            $prod_cur_unit = postSpaceFilter('prod_cur_unit_hidden');
            $prod_barcode_status = postSpaceFilter('prod_barcode_status') == 'Yes' ? 'Yes' : 'No';
            $prod_barcode_slot = $prod_barcode_status == 'Yes' ? postSpaceFilter('prod_barcode_slot') : '';
            $prod_category = postSpaceFilter('prod_category_hidden');
            $prod_expire_date = postSpaceFilter('prod_expire_date');
            $parent_prod = postSpaceFilter('parent_prod_hidden');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            $check_duplicate_record = isDuplicateRecord("name", $prod_name, $tblName, $connect, $dataID)
                && isDuplicateRecord("brand", $prod_brand, $tblName, $connect, $dataID)
                && isDuplicateRecord("weight", $prod_wgt, $tblName, $connect, $dataID)
                && isDuplicateRecord("weight_unit", $prod_wgt_unit, $tblName, $connect, $dataID)
                && isDuplicateRecord("cost", $prod_cost, $tblName, $connect, $dataID)
                && isDuplicateRecord("currency_unit", $prod_cur_unit, $tblName, $connect, $dataID)
                && isDuplicateRecord("barcode_status", $prod_barcode_status, $tblName, $connect, $dataID)
                && isDuplicateRecord("barcode_slot", $prod_barcode_slot, $tblName, $connect, $dataID)
                && isDuplicateRecord("product_category", $prod_category, $tblName, $connect, $dataID);

            if ($check_duplicate_record) {
                $err = "Duplicate record found for current " . $pageTitle;
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($prod_name) {
                        array_push($newvalarr, $prod_name);
                        array_push($datafield, 'name');
                    }

                    if ($prod_brand) {
                        array_push($newvalarr, $prod_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($prod_wgt) {
                        array_push($newvalarr, $prod_wgt);
                        array_push($datafield, 'weight');
                    }

                    if ($prod_wgt_unit) {
                        array_push($newvalarr, $prod_wgt_unit);
                        array_push($datafield, 'weight_unit');
                    }

                    if ($prod_cost) {
                        array_push($newvalarr, $prod_cost);
                        array_push($datafield, 'cost');
                    }

                    if ($prod_cur_unit) {
                        array_push($newvalarr, $prod_cur_unit);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($prod_barcode_status) {
                        array_push($newvalarr, $prod_barcode_status);
                        array_push($datafield, 'barcode_status');
                    }

                    if ($prod_barcode_slot) {
                        array_push($newvalarr, $prod_barcode_slot);
                        array_push($datafield, 'barcode_slot');
                    }

                    if ($prod_category){
                        array_push($newvalarr, $prod_category);
                        array_push($datafield, 'prod_category');
                    }

                    if ($prod_expire_date){

                        array_push($newvalarr, $prod_expire_date);
                        array_push($datafield, 'expire_date');
                    }

                    if ($parent_prod) {
                        array_push($newvalarr, $parent_prod);
                        array_push($datafield, 'parent_product');
                    }

                    $query = "INSERT INTO " . $tblName . "(name, brand, weight, weight_unit, cost, currency_unit, barcode_status, barcode_slot, product_category, expire_date, parent_product, create_by, create_date, create_time) VALUES ('$prod_name', '$prod_brand', '$prod_wgt', '$prod_wgt_unit', '$prod_cost', '$prod_cur_unit', '$prod_barcode_status', '$prod_barcode_slot', '$prod_category', '$prod_expire_date', '$parent_prod', '" . USER_ID . "', curdate(), curtime())";
                    $returnData = mysqli_query($connect, $query);
                    generateDBData(PROD, $connect);
                    $dataID = $connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    var_dump($errorMsg);
                    $act = "F";
                }
            } else {
                try {
                    if ($row['name'] != $prod_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $prod_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['brand'] != $prod_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $prod_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['weight'] != $prod_wgt) {
                        array_push($oldvalarr, $row['weight']);
                        array_push($chgvalarr, $prod_wgt);
                        array_push($datafield, 'weight');
                    }

                    if ($row['weight_unit'] != $prod_wgt_unit) {
                        array_push($oldvalarr, $row['weight_unit']);
                        array_push($chgvalarr, $prod_wgt_unit);
                        array_push($datafield, 'weight_unit');
                    }

                    if ($row['cost'] != $prod_cost) {
                        array_push($oldvalarr, $row['cost']);
                        array_push($chgvalarr, $prod_cost);
                        array_push($datafield, 'cost');
                    }

                    if ($row['currency_unit'] != $prod_cur_unit) {
                        array_push($oldvalarr, $row['currency_unit']);
                        array_push($chgvalarr, $prod_cur_unit);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($row['barcode_status'] != $prod_barcode_status) {
                        array_push($oldvalarr, $row['barcode_status']);
                        array_push($chgvalarr, $prod_barcode_status);
                        array_push($datafield, 'barcode_status');
                    }

                    if ($row['barcode_slot'] != $prod_barcode_slot) {
                        array_push($oldvalarr, $row['barcode_slot']);
                        array_push($chgvalarr, $prod_barcode_slot);
                        array_push($datafield, 'barcode_slot');
                    }

                    if ($row['prod_category'] != $prod_category) {
                        array_push($oldvalarr, $row['prod_category']);
                        array_push($chgvalarr, $prod_category);
                    }

                    if ($row['expire_date'] != $prod_expire_date) {
                        array_push($oldvalarr, $row['expire_date']);
                        array_push($chgvalarr, $prod_expire_date);
                        array_push($datafield, 'expire_date');
                    }

                    if ($row['parent_product'] != $parent_prod) {
                        array_push($oldvalarr, $row['parent_product']);
                        array_push($chgvalarr, $parent_prod);
                        array_push($datafield, 'parent_product');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$prod_name', brand ='$prod_brand', weight ='$prod_wgt', weight_unit ='$prod_wgt_unit', cost ='$prod_cost', currency_unit ='$prod_cur_unit', barcode_status ='$prod_barcode_status', barcode_slot ='$prod_barcode_slot',product_category ='$prod_category', expire_date ='$prod_expire_date', parent_product ='$parent_prod', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        
                        $returnData = mysqli_query($connect, $query);
                        generateDBData(PROD, $connect);
                        
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            }

            // audit log
            if (isset($query)) {

                $log = [
                    'log_act'      => $pageAction,
                    'cdate'        => $cdate,
                    'ctime'        => $ctime,
                    'uid'          => USER_ID,
                    'cby'          => USER_ID,
                    'query_rec'    => $query,
                    'query_table'  => $tblName,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($pageAction == 'Edit') {
                    $log['oldval']  = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                }
                audit_log($log);
            }

            break;

        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
    }
}

//Function(title, subtitle, page name, ajax url path, redirect path, action)
//To show action dialog after finish certain action (eg. edit)

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-8 col-md-6 formWidthAdjust">
                <form id="form" method="post" novalidate>
                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size : 21px"><?php if (isset($err)) echo $err; ?></span>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" id="prod_name_lbl" for="prod_name">Product Name<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="prod_name" id="prod_name" value="<?php echo (isset($row['name'])) ? $row['name'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl" id="prod_brand_lbl" for="prod_brand">Product Brand<span
                                        class="requireRed">*</span></label>
                                <?php

                                unset($echoVal);

                                if (isset($row['brand']))
                                    $echoVal = $row['brand'];

                                if (isset($echoVal)) {
                                    $brand_rst = getData('name', "id = '$echoVal'", '', BRAND, $connect);
                                    if (!$brand_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $brand_row = $brand_rst->fetch_assoc();
                                 
                                }
                                ?>

                                <input class="form-control" type="text" name="prod_brand" id="prod_brand" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $brand_row['name'] : ''  ?>" required>

                                <input type="hidden" name="prod_brand_hidden" id="prod_brand_hidden" value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl" id="prod_wgt_unit_lbl" for="prod_wgt_unit">Product Weight Unit<span
                                        class="requireRed">*</span></label>
                                <?php

                                unset($echoVal);

                                if (isset($row['weight_unit']))
                                    $echoVal = $row['weight_unit'];

                                if (isset($echoVal)) {
                                    $weight_rst = getData('unit', "id = '$echoVal'", '', WGT_UNIT, $connect);
                                    if (!$weight_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $weight_row = $weight_rst->fetch_assoc();
                                }

                                ?>
                                <input class="form-control" type="text" name="prod_wgt_unit" id="prod_wgt_unit" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $weight_row['unit'] : ''  ?>" required>
                                <input type="hidden" name="prod_wgt_unit_hidden" id="prod_wgt_unit_hidden" value="<?php echo (isset($row['weight_unit'])) ? $row['weight_unit'] : ''; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" id="prod_wgt_lbl" for="prod_wgt">Product Weight<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="prod_wgt" id="prod_wgt" value="<?php echo (isset($row['weight'])) ? $row['weight'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl" id="prod_cur_unit_lbl" for="prod_cur_unit">Product Currency Unit<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['currency_unit']))
                                    $echoVal = $row['currency_unit'];

                                if (!empty($echoVal)) {
                                    $currency_unit_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                                    if (!$currency_unit_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $currency_unit_row = $currency_unit_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="prod_cur_unit" id="prod_cur_unit" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $currency_unit_row['unit'] : ''  ?>" required>
                                <input type="hidden" name="prod_cur_unit_hidden" id="prod_cur_unit_hidden" value="<?php echo (isset($row['currency_unit'])) ? $row['currency_unit'] : ''; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" id="prod_cost_lbl" for="prod_cost">Product Cost<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="prod_cost" min="0" step=".01" id="prod_cost" value="<?php echo (isset($row['cost'])) ? $row['cost'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" id="prod_barcode_status_lbl" for="prod_barcode_status">Record Barcode?</label>
                                <input class="form-check-input ms-1" type="checkbox" name="prod_barcode_status" id="prod_barcode_status" value="Yes" <?php if ($act == '') echo 'disabled'; ?> <?php echo (isset($row['barcode_status']) && $row['barcode_status'] == 'Yes') ? 'checked' : ''; ?>>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" style="display:none" id="prod_barcode_slot_lbl" for="prod_barcode_slot">Product Barcode Slot</label>
                                <input class="form-control" style="display:none" type="text" name="prod_barcode_slot" id="prod_barcode_slot" value="<?php echo (isset($row['barcode_slot'])) ? $row['barcode_slot'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?>>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl" id="prod_category_lbl" for="prod_category">Category</label>
                                <?php
                                unset($echoVal);

                                if (isset($row['product_category']))
                                    $echoVal = $row['product_category'];

                                if (!empty($echoVal)) {
                                    $product_rst = getData('name', "id = '$echoVal'", '', PROD_CATEGORY, $connect);
                                    if (!$product_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $product_row = $product_rst->fetch_assoc();
                                } 
                                ?>
                                <input class="form-control" type="text" name="prod_category" id="prod_category" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $product_row['name'] : ''  ?>">
                                <input type="hidden" name="prod_category_hidden" id="prod_category_hidden" value="<?php echo (isset($row['product_category'])) ? $row['product_category'] : ''; ?>">

                            </div>
                        </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" id="prod_expire_date_lbl" for="prod_expire_date">Product Expire Date<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="date" name="prod_expire_date" id="prod_expire_date" value="<?php echo (isset($row['expire_date'])) ? $row['expire_date'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            </div>
                        </div>


                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl" id="parent_prod_lbl" for="parent_prod">Parent Product</label>
                                <?php
                                unset($echoVal);

                                if (isset($row['parent_product']))
                                    $echoVal = $row['parent_product'];

                                if (!empty($echoVal)) {
                                    $product_rst = getData('name', "id = '$echoVal'", '', PROD, $connect);
                                    if (!$product_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $product_row = $product_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="parent_prod" id="parent_prod" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $product_row['name'] : ''  ?>">
                                <input type="hidden" name="parent_prod_hidden" id="parent_prod_hidden" value="<?php echo (isset($row['parent_product'])) ? $row['parent_product'] : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                        <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);
    </script>

</body>

<script>
    $(document).ready(function() {
        var prodBarcodeStatus = $("#prod_barcode_status");
        var prodBarcode = $("#prod_barcode_slot, #prod_barcode_slot_lbl");
        var prodBarcodeSlot = $("#prod_barcode_slot");

        if (prodBarcodeStatus.prop('checked'))
            prodBarcode.show();
        else
            prodBarcode.hide();

        $(prodBarcodeStatus).on('change', () => {
            if (prodBarcodeStatus.prop('checked'))
                prodBarcode.show();
            else {
                prodBarcode.hide();
                prodBarcodeSlot.next().remove();
            }
        })

        if (!($("#prod_brand").attr('readonly'))) {
            $("#prod_brand").keyup(function() {
                var param = {
                    search: $(this).val(), // search value
                    searchType: 'name', // column of the table
                    elementID: $(this).attr('id'), // id of the input
                    hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                    dbTable: '<?= BRAND ?>' // json filename (generated when login)
                }
                var arr = searchInput(param, '<?= $SITEURL ?>');
            });
            $("#prod_brand").change(function() {
                if ($(this).val() == '')
                    $('#' + $(this).attr('id') + '_hidden').val('');
            });

            $("#prod_brand_hidden").change(function() {
                if ($("#prod_brand_hidden").val() != '') {
                    console.log(arr);
                };
            })
        }

        if (!($("#prod_wgt_unit").attr('readonly'))) {
            $("#prod_wgt_unit").keyup(function() {
                var param = {
                    search: $(this).val(),
                    searchType: 'unit',
                    elementID: $(this).attr('id'),
                    hiddenElementID: $(this).attr('id') + '_hidden',
                    dbTable: '<?= WGT_UNIT ?>'
                }
                searchInput(param, '<?= $SITEURL ?>');
            });
            $("#prod_wgt_unit").change(function() {
                if ($(this).val() == '')
                    $('#' + $(this).attr('id') + '_hidden').val('');
            });
        }

        if (!($("#prod_cur_unit").attr('readonly'))) {
            $("#prod_cur_unit").keyup(function() {
                var param = {
                    search: $(this).val(),
                    searchType: 'unit',
                    elementID: $(this).attr('id'),
                    hiddenElementID: $(this).attr('id') + '_hidden',
                    dbTable: '<?= CUR_UNIT ?>'
                }
                searchInput(param, '<?= $SITEURL ?>');
            });
            $("#prod_cur_unit").change(function() {
                if ($(this).val() == '')
                    $('#' + $(this).attr('id') + '_hidden').val('');
            });
        }

        if (!($("#prod_category").attr('readonly'))) {
            $("#prod_category").keyup(function() { 
                var param = {
                    search: $(this).val(), 
                    searchType: 'name', 
                    elementID: $(this).attr('id'), 
                    hiddenElementID: $(this).attr('id') + '_hidden', 
                    dbTable: '<?= PROD_CATEGORY ?>'
                } 
                
        

                searchInput(param, '<?= $SITEURL ?>'); 
            }); 
            $("#prod_category").change(function() {
                if ($(this).val() == '')
                    $('#' + $(this).attr('id') + '_hidden').val('');
            });
        }

        if (!($("#parent_prod").attr('readonly'))) {
            $("#parent_prod").keyup(function() {
                var param = {
                    search: $(this).val(),
                    searchType: 'name',
                    elementID: $(this).attr('id'),
                    hiddenElementID: $(this).attr('id') + '_hidden',
                    dbTable: '<?= $tblName ?>'
                }
                searchInput(param, '<?= $SITEURL ?>');
            });
            $("#parent_prod").change(function() {
                if ($(this).val() == '')
                    $('#' + $(this).attr('id') + '_hidden').val('');
            });
        }
    });
</script>

</html>
