<?php
$pageTitle = "Package";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

echo '<script>var page = "' . $pageTitle . '"; checkCurrentPage(page);</script>';

$tblName = PKG;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/package_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

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
    deleteRecord($tblName, $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
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

            $currentDataName = postSpaceFilter('currentDataName');
            $pkg_price = postSpaceFilter('price');
            $cur_unit = postSpaceFilter('cur_unit_hidden');

            // middle
            $prod_list = post('prod_val');
            $prod_list = implode(',', array_filter($prod_list));


            $barcode_slot_total = postSpaceFilter('barcode_slot_total_hidden');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName) {
                        array_push($newvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($pkg_price) {
                        array_push($newvalarr, $pkg_price);
                        array_push($datafield, 'price');
                    }

                    if ($cur_unit) {
                        array_push($newvalarr, $cur_unit);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($prod_list) {
                        array_push($newvalarr, $prod_list);
                        array_push($datafield, 'product');
                    }

                    if ($barcode_slot_total) {
                        array_push($newvalarr, $barcode_slot_total);
                        array_push($datafield, 'barcode_slot_total');
                    }

                    if ($dataRemark) {
                        array_push($newvalarr, $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(name,price,currency_unit,product,barcode_slot_total,remark,create_by,create_date,create_time) VALUES ('$currentDataName','$pkg_price','$cur_unit','$prod_list','$barcode_slot_total','$dataRemark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);
                    $dataID = $connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if ($row['name'] != $currentDataName) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($row['price'] != $pkg_price) {
                        array_push($oldvalarr, $row['price']);
                        array_push($chgvalarr, $pkg_price);
                        array_push($datafield, 'price');
                    }

                    if ($row['currency_unit'] != $cur_unit) {
                        array_push($oldvalarr, $row['currency_unit']);
                        array_push($chgvalarr, $cur_unit);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($row['product'] != $prod_list) {
                        array_push($oldvalarr, $row['product']);
                        array_push($chgvalarr, $prod_list);
                        array_push($datafield, 'product');
                    }

                    if ($row['barcode_slot_total'] != $barcode_slot_total) {
                        array_push($oldvalarr, $row['barcode_slot_total']);
                        array_push($chgvalarr, $barcode_slot_total);
                        array_push($datafield, 'barcode_slot_total');
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName',price ='$pkg_price', currency_unit ='$cur_unit', product ='$prod_list', barcode_slot_total ='$barcode_slot_total', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($connect, $query);
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
    <link rel="stylesheet" href="./css/package.css">
</head>

<body>

    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php echo $pageActionTitle ?>
        </p>
    </div>

    <div id="formContainer" class="container-fluid mt-2">
        <div class="col-12 col-md-12 formWidthAdjust">
            <form id="form" method="post" novalidate>
                <div class="form-group mb-5">
                    <h2>
                        <?php echo $pageActionTitle ?>
                    </h2>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label" for="currentDataName"><?php echo $pageTitle ?> Name</label>
                            <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                            <div id="err_msg">
                                <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="price_lbl" for="price">Selling Price</label>
                            <input class="form-control" type="number" name="price" id="price" value="<?php echo (isset($row['price'])) ? $row['price'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="cur_unit_lbl" for="cur_unit">Currency Unit</label>
                            <?php
                            unset($echoVal);

                            if (isset($row['currency_unit']))
                                $echoVal = $row['currency_unit'];

                            if (isset($echoVal)) {
                                $product_info_result = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);

                                $product_info_row = $product_info_result->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="cur_unit" id="cur_unit" value="<?php echo !empty($echoVal) ? $product_info_row['unit'] : ''  ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            <input type="hidden" name="cur_unit_hidden" id="cur_unit_hidden" value="<?php echo (isset($row['currency_unit'])) ? $row['currency_unit'] : ''; ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err3)) echo $err3; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive mb-3">
                        <table class="table table-striped" id="productList">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">Weight</th>
                                    <th scope="col">Weight Unit</th>
                                    <th scope="col">Barcode Status</th>
                                    <th scope="col">Barcode Slot</th>
                                    <th scope="col" id="action_col"></th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                // check act
                                if ($act != '')
                                    $readonly = '';
                                else
                                    $readonly = ' readonly';

                                // get value
                                unset($echoVal);

                                if (isset($row['product']))
                                    $echoVal = $row['product'];

                                // echo
                                if (isset($echoVal)) {
                                    $num = 1; // numbering
                                    $echoVal = explode(',', $echoVal);
                                    foreach ($echoVal as $prod_id) {
                                        // product info
                                        $product_info_result = getData('*', "id = '$prod_id'", '', PROD, $connect);
                                        $product_info_row = $product_info_result->fetch_assoc();

                                        $pid = $product_info_row['id'];
                                        $pn = $product_info_row['name'];
                                        $pw = $product_info_row['weight'];
                                        $pwu = $product_info_row['weight_unit'];
                                        $ps = $product_info_row['barcode_status'];
                                        $pslot = $product_info_row['barcode_slot'];

                                        // weight unit info
                                        $product_info_result = getData('unit', "id = '$pwu'", '', WGT_UNIT, $connect);
                                        $product_info_row = $product_info_result->fetch_assoc();

                                        $pwun = $product_info_row['unit'];
                                ?>
                                        <tr>
                                            <td><?= $num ?></td>
                                            <td class="autocomplete"><input type="text" name="prod_name[]" id="prod_name_<?= $num ?>" value="<?= $pn ?>" onkeyup="prodInfo(this)" <?= $readonly ?>><input type="hidden" name="prod_val[]" id="prod_val_<?= $num ?>" value="<?= $pid ?>" oninput="prodInfoAutoFill(this)">
                                                <div id="err_msg">
                                                    <span class="mt-n1"><?php if (isset($err4)) echo $err4; ?></span>
                                                </div>
                                            </td>
                                            <td><input class="readonlyInput" type="text" name="wgt[]" id="wgt_<?= $num ?>" value="<?= $pw ?>" readonly></td>
                                            <td><input class="readonlyInput" type="text" name="wgt_unit[]" id="wgt_unit_<?= $num ?>" value="<?= $pwun ?>" readonly><input type="hidden" name="wgt_unit_val[]" id="wgt_unit_val_<?= $num ?>" value="<?= $pwu ?>" readonly></td>
                                            <td><input class="readonlyInput" type="text" name="barcode_status[]" id="barcode_status_<?= $num ?>" value="<?= $ps ?>" readonly></td>
                                            <td><input class="readonlyInput" type="text" name="barcode_slot[]" id="barcode_slot_<?= $num ?>" value="<?= $pslot ?>" readonly></td>
                                            <?php
                                            if ($act != '') {
                                                if ($num == 1) {
                                            ?>
                                                    <td><button class="mt-1" id="action_menu_btn" type="button" onclick="Add()"><i class="fa-regular fa-square-plus fa-xl" style="color:#37c22e"></i></button></td>
                                                <?php
                                                } else {
                                                ?>
                                                    <td><button class="mt-1" id="action_menu_btn" type="button" onclick="Remove(this)"><i class="fa-regular fa-trash-can fa-xl" style="color:#ff0000" value="Remove"></i></button></td>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </tr>
                                    <?php
                                        $num++;
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td>1</td>
                                        <td class="autocomplete"><input type="text" name="prod_name[]" id="prod_name_1" value="" onkeyup="prodInfo(this)"><input type="hidden" name="prod_val[]" id="prod_val_1" value="" oninput="prodInfoAutoFill(this)">
                                            <div id="err_msg">
                                                <span class="mt-n1"><?php if (isset($err4)) echo $err4; ?></span>
                                            </div>
                                        </td>
                                        <td><input class="readonlyInput" type="text" name="wgt[]" id="wgt_1" value="" readonly></td>
                                        <td><input class="readonlyInput" type="text" name="wgt_unit[]" id="wgt_unit_1" value="" readonly><input type="hidden" name="wgt_unit_val[]" id="wgt_unit_val_1" value="" readonly></td>
                                        <td><input class="readonlyInput" type="text" name="barcode_status[]" id="barcode_status_1" value="" readonly></td>
                                        <td><input class="readonlyInput" type="text" name="barcode_slot[]" id="barcode_slot_1" value="" readonly></td>
                                        <td><button class="mt-1" id="action_menu_btn" type="button" onclick="Add()"><i class="fa-regular fa-square-plus fa-xl" style="color:#37c22e"></i></button></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td scope="col" colspan="5" style="text-align:right">Total Barcode</td>
                                    <td scope="col" id="barcode_slot_total" style="text-align:center">
                                        <?php
                                        if (isset($barcode_slot_total) && $barcode_slot_total != '')
                                            echo $barcode_slot_total;
                                        else {
                                            if (isset($dataExisted) && isset($row['barcode_slot_total']))
                                                echo $row['barcode_slot_total'];
                                            else echo '0';
                                        }
                                        ?><input name="barcode_slot_total_hidden" id="barcode_slot_total_hidden" type="hidden" value="<?php echo (isset($row['barcode_slot_total'])) ? $row['barcode_slot_total'] : ''; ?>"></td>
                                    <td scope="col"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>


                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataRemark"><?php echo $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                    <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        setButtonColor();
        setAutofocus(action);
    </script>

</body>

<script>
    <?php include './js/package.js'; ?>
</script>

</html>