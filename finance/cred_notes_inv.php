<?php
$pageTitle = "Credit Notes (Invoice)";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = CRED_NOTES_INV;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/cred_notes_inv_table.php';
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
$rst = getData('*', "id = '$dataID'", '', $tblName, $finance_connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        'cdate' => $cdate,
        'ctime' => $ctime,
        'uid' => USER_ID,
        'cby' => USER_ID,
        'act_msg' => $viewActMsg,
        'page' => $pageTitle,
        'connect' => $connect,
    ];

    audit_log($log);
}

$logo_path = $SITEURL . '/' . img_server . 'themes/';
$proj_result = getData('*', "id = '1'", '', PROJ, $connect);

if (!$proj_result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo $redirectLink;
}

$proj_row = $proj_result->fetch_assoc();

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $currentDataName = postSpaceFilter('currentDataName');
            $pkg_price = postSpaceFilter('price');
            $cur_unit = postSpaceFilter('cur_unit_hidden');
            $brand = postSpaceFilter('brand_hidden');
            $cost = postSpaceFilter('package_cost');
            $cost_curr = postSpaceFilter('cost_curr_hidden');

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
                    if ($brand) {
                        array_push($newvalarr, $brand);
                        array_push($datafield, 'brand');
                    }
                    if ($cost) {
                        array_push($newvalarr, $cost);
                        array_push($datafield, 'cost');
                    }
                    if ($cost_curr) {
                        array_push($newvalarr, $cost_curr);
                        array_push($datafield, 'cost_curr');
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

                    $query = "INSERT INTO " . $tblName . "(name,brand,cost,cost_curr,price,currency_unit,product,barcode_slot_total,remark,create_by,create_date,create_time) VALUES ('$currentDataName','$brand','$cost', '$cost_curr','$pkg_price','$cur_unit','$prod_list','$barcode_slot_total','$dataRemark','" . USER_ID . "',curdate(),curtime())";
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

                    if ($row['brand'] != $brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['cost'] != $cost) {
                        array_push($oldvalarr, $row['cost']);
                        array_push($chgvalarr, $cost);
                        array_push($datafield, 'cost');
                    }

                    if ($row['cost_curr'] != $cost_curr) {
                        array_push($oldvalarr, $row['cost_curr']);
                        array_push($chgvalarr, $cost_curr);
                        array_push($datafield, 'cost_curr');
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
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName',brand='$brand',cost='$cost',cost_curr='$cost_curr',price ='$pkg_price', currency_unit ='$cur_unit', product ='$prod_list', barcode_slot_total ='$barcode_slot_total', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                    'log_act' => $pageAction,
                    'cdate' => $cdate,
                    'ctime' => $ctime,
                    'uid' => USER_ID,
                    'cby' => USER_ID,
                    'query_rec' => $query,
                    'query_table' => $tblName,
                    'page' => $pageTitle,
                    'connect' => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
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

<body style="background-color: rgb(240, 241, 247);">
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>">
                    <?= $pageTitle ?>
                </a> <i class="fa-solid fa-chevron-right fa-xs"></i>
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
                    <div class="container-xxl flex-grow-1">
                        <div class="row invoice-add">
                            <div class="col-lg-9 col-12 mb-lg-0 mb-4">
                                <div class="card invoice-preview-card p-4">
                                    <div class="row m-sm-4 m-0">
                                        <div class="col-7 mb-md-0 mb-3">
                                            <div class="d-flex mb-2 gap-2 align-items-center">
                                                <img id="logo" style="min-height:45px; max-height : 45px; width : auto;"
                                                    src="<?php echo (isset($proj_row['logo'])) ? $logo_path . $proj_row['logo'] : $SITEURL . '/image/logo2.png'; ?>">
                                                <span class="fw-bold fs-4">
                                                    <?php echo $proj_row['company_name']; ?>
                                                </span>
                                            </div>
                                            <p class="mb-2">
                                                <?php echo $proj_row['company_address']; ?>
                                            </p>
                                            <p class="mb-2">
                                                <?php echo $proj_row['company_business_no']; ?>
                                            </p>
                                            <p class="mb-3">
                                                <?php echo $proj_row['company_contact'] . " | " . $proj_row['company_email']; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-5">
                                            <dl class="row mb-2">
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end ps-0">
                                                    <span class="h4 text-capitalize mb-0 text-nowrap">Invoice</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-0 ps-sm-2">
                                                    <div class="input-group input-group-merge disabled w-px-150">
                                                        <span class="input-group-text">#</span>
                                                        <input type="text" class="form-control" disabled
                                                            placeholder="3905" value="3905" id="invoiceId" />
                                                    </div>
                                                </dd>
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end ps-0">
                                                    <span class="fw-normal">Date:</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-0 ps-sm-2">
                                                    <input type="text" class="form-control w-px-150 date-picker"
                                                        placeholder="YYYY-MM-DD" />
                                                </dd>
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end ps-0">
                                                    <span class="fw-normal">Due Date:</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-0 ps-sm-2">
                                                    <input type="text" class="form-control w-px-150 date-picker"
                                                        placeholder="YYYY-MM-DD" />
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                    <div class="row  m-sm-4 m-0">
                                        <h6 class="mb-2">Billing To:</h6>
                                        <div class="col-md-6 mb-md-0 mb-2">

                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <input class="form-control" type="text" placeholder="Customer Name"
                                                        name="pmf_name" id="pmf_name" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['name']) && !isset($pmf_name)) {
                                                                                                            echo $row['name'];
                                                                                                        } else if (isset($dataExisted) && isset($row['name']) && isset($pmf_name)) {
                                                                                                            echo $pmf_name;
                                                                                                        } else {
                                                                                                            echo '';
                                                                                                        } ?>"
                                                        <?php if ($act == '') echo 'disabled' ?>>
                                                    <?php if (isset($name_err)) { ?>
                                                    <div id="err_msg">
                                                        <span class="mt-n1"><?php echo $name_err; ?></span>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-12">
                                                    <textarea class="form-control" name="pmf_remark" id="pmf_remark"
                                                        rows="3" placeholder="Enter Address"
                                                        <?php if ($act == '') echo 'disabled' ?>><?php
                                                                if (isset($dataExisted) && isset($row['remark']) && !isset($pmf_remark)) {
                                                                    echo $row['remark'];
                                                                } else if (isset($dataExisted) && isset($row['remark']) && isset($pmf_remark)) {
                                                                    echo $pmf_remark;
                                                                } ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <input class="form-control" type="text" placeholder="Customer Email"
                                                        name="pmf_name" id="pmf_name" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['name']) && !isset($pmf_name)) {
                                                                                                            echo $row['name'];
                                                                                                        } else if (isset($dataExisted) && isset($row['name']) && isset($pmf_name)) {
                                                                                                            echo $pmf_name;
                                                                                                        } else {
                                                                                                            echo '';
                                                                                                        } ?>"
                                                        <?php if ($act == '') echo 'disabled' ?>>
                                                    <?php if (isset($name_err)) { ?>
                                                    <div id="err_msg">
                                                        <span class="mt-n1"><?php echo $name_err; ?></span>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-12">
                                                    <input class="form-control" type="text" placeholder="Phone Number"
                                                        name="pmf_name" id="pmf_name" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['name']) && !isset($pmf_name)) {
                                                                                                            echo $row['name'];
                                                                                                        } else if (isset($dataExisted) && isset($row['name']) && isset($pmf_name)) {
                                                                                                            echo $pmf_name;
                                                                                                        } else {
                                                                                                            echo '';
                                                                                                        } ?>"
                                                        <?php if ($act == '') echo 'disabled' ?>>
                                                    <?php if (isset($name_err)) { ?>
                                                    <div id="err_msg">
                                                        <span class="mt-n1"><?php echo $name_err; ?></span>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row m-sm-4 m-0">

                                        <hr class="my-3" />

                                        <div class="row">
                                            <div class="table-responsive mb-3">
                                                <table class="table table-striped" id="productList">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Description</th>
                                                            <th scope="col">Price</th>
                                                            <th scope="col">Quantity</th>
                                                            <th scope="col">Amount</th>
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
                                                            <td>
                                                                <?= $num ?>
                                                            </td>
                                                            <td class="autocomplete"><input type="text"
                                                                    name="prod_name[]" id="prod_name_<?= $num ?>"
                                                                    value="<?= $pn ?>" onkeyup="prodInfo(this)"
                                                                    <?= $readonly ?>><input type="hidden"
                                                                    name="prod_val[]" id="prod_val_<?= $num ?>"
                                                                    value="<?= $pid ?>"
                                                                    oninput="prodInfoAutoFill(this)">
                                                                <div id="err_msg">
                                                                    <span class="mt-n1">
                                                                        <?php if (isset($err4))
                                                                                echo $err4; ?>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td><input class="readonlyInput" type="text" name="wgt[]"
                                                                    id="wgt_<?= $num ?>" value="<?= $pw ?>" readonly>
                                                            </td>
                                                            <td><input class="readonlyInput" type="text"
                                                                    name="wgt_unit[]" id="wgt_unit_<?= $num ?>"
                                                                    value="<?= $pwun ?>" readonly><input type="hidden"
                                                                    name="wgt_unit_val[]" id="wgt_unit_val_<?= $num ?>"
                                                                    value="<?= $pwu ?>" readonly>
                                                            </td>
                                                            <td><input class="readonlyInput" type="text"
                                                                    name="barcode_status[]"
                                                                    id="barcode_status_<?= $num ?>" value="<?= $ps ?>"
                                                                    readonly>
                                                            </td>
                                                            <?php
                                                                if ($act != '') {
                                                                    if ($num == 1) {
                                                                        ?>
                                                            <td><button class="mt-1" id="action_menu_btn" type="button"
                                                                    onclick="Add()"><i
                                                                        class="fa-regular fa-square-plus fa-xl"
                                                                        style="color:#37c22e"></i></button></td>
                                                            <?php
                                                                    } else {
                                                                        ?>
                                                            <td><button class="mt-1" id="action_menu_btn" type="button"
                                                                    onclick="Remove(this)"><i
                                                                        class="fa-regular fa-trash-can fa-xl"
                                                                        style="color:#ff0000"
                                                                        value="Remove"></i></button>
                                                            </td>
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
                                                            <td class="autocomplete"><input type="text"
                                                                    name="prod_name[]" id="prod_name_1" value=""
                                                                    onkeyup="prodInfo(this)"><input type="hidden"
                                                                    name="prod_val[]" id="prod_val_1" value=""
                                                                    oninput="prodInfoAutoFill(this)">
                                                                <div id="err_msg">
                                                                    <span class="mt-n1">
                                                                        <?php if (isset($err4))
                                                                            echo $err4; ?>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td><input class="readonlyInput" type="text" name="wgt[]"
                                                                    id="wgt_1" value="" readonly></td>
                                                            <td><input class="readonlyInput" type="text"
                                                                    name="wgt_unit[]" id="wgt_unit_1" value=""
                                                                    readonly><input type="hidden" name="wgt_unit_val[]"
                                                                    id="wgt_unit_val_1" value="" readonly>
                                                            </td>
                                                            <td><input class="readonlyInput" type="text"
                                                                    name="barcode_status[]" id="barcode_status_1"
                                                                    value="" readonly></td>

                                                            <td><button class="mt-1" id="action_menu_btn" type="button"
                                                                    onclick="Add()"><i
                                                                        class="fa-regular fa-square-plus fa-xl"
                                                                        style="color:#37c22e"></i></button></td>
                                                        </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td scope="col" colspan="5" style="text-align:right">Total
                                                                Barcode
                                                            </td>
                                                            <td scope="col" id="barcode_slot_total"
                                                                style="text-align:center">
                                                                <?php
                                                            if (isset($barcode_slot_total) && $barcode_slot_total != '')
                                                                echo $barcode_slot_total;
                                                            else {
                                                                if (isset($dataExisted) && isset($row['barcode_slot_total']))
                                                                    echo $row['barcode_slot_total'];
                                                                else
                                                                    echo '0';
                                                            }
                                                            ?><input name="barcode_slot_total_hidden"
                                                                    id="barcode_slot_total_hidden" type="hidden"
                                                                    value="<?php echo (isset($row['barcode_slot_total'])) ? $row['barcode_slot_total'] : ''; ?>">
                                                            </td>
                                                            <td scope="col"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between flex-wrap">
                                                    <div class="col-12 col-md-6">
                                                        <dl class="row mb-2 form-group autocomplete">

                                                            <dt class="col-sm-4 mb-2 mb-sm-0">
                                                                <span class="form_lbl">Salesperson:</span>
                                                            </dt>
                                                            <dd class="col-sm-8 d-flex ps-sm-2 ">
                                                                <?php
                                                            unset($echoVal);

                                                            if (isset($row['cost_curr']))
                                                                $echoVal = $row['cost_curr'];

                                                            if (isset($echoVal)) {
                                                                $cost_curr_result = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);

                                                                $cost_curr_row = $cost_curr_result->fetch_assoc();
                                                            }
                                                            ?>
                                                                <input class="form-control" type="text" name="cost_curr"
                                                                    id="cost_curr"
                                                                    value="<?php echo !empty($echoVal) ? $cost_curr_row['unit'] : '' ?>" <?php if ($act == '')
                                                                           echo 'readonly' ?> required>
                                                                <input type="hidden" name="cost_curr_hidden"
                                                                    id="cost_curr_hidden"
                                                                    value="<?php echo (isset($row['cost_curr'])) ? $row['cost_curr'] : ''; ?>">
                                                                <div id="err_msg">
                                                                    <span class="mt-n1">
                                                                        <?php if (isset($cost_curr_err))
                                                                        echo $cost_curr_err; ?>
                                                                    </span>
                                                                </div>
                                                            </dd>

                                                        </dl>
                                                        <div class="form-group mb-3">
                                                            <label class="form-label form_lbl"
                                                                for="salesperson_remark">Remark:</label>
                                                            <textarea class="form-control" name="salesperson_remark"
                                                                id="salesperson_remark" rows="3" <?php if ($act == '')
                                                                echo 'readonly' ?>><?php if (isset($row['remark']))
                                                                echo $row['remark'] ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="mt-auto mb-auto col-12 col-md-4 justify-content-end">
                                                        <div class="invoice-calculations">
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Subtotal:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Discount:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Tax:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                            <hr />
                                                            <div class="d-flex justify-content-between">
                                                                <span class="w-px-100">Total:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <div class="form-group mb-3">
                                                <label class="form-label form_lbl" for="currentDataRemark">Note:</label>
                                                <textarea class="form-control" name="currentDataRemark"
                                                    id="currentDataRemark" rows="3" <?php if ($act == '')
                                                                echo 'readonly' ?>><?php if (isset($row['remark']))
                                                                echo $row['remark'] ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-12 invoice-actions  mb-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <button class="btn btn-primary d-grid w-100 mb-2" data-bs-toggle="offcanvas"
                                            data-bs-target="#sendInvoiceOffcanvas">
                                            <span
                                                class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                    class="ti ti-send ti-xs me-2"></i>Send Invoice</span>
                                        </button>
                                        <a href="./app-invoice-preview.html"
                                            class="btn btn-label-secondary d-grid w-100 mb-2">Preview</a>
                                        <button type="button" class="btn btn-label-secondary d-grid w-100">Save</button>
                                        <button class="btn btn-primary d-grid w-100 mb-2 cancel"
                                            name="actionBtn" id="actionBtn" value="back"><span
                                                class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                    class="ti ti-send ti-xs me-2"></i>Back</span></button>
                                    </div>
                                </div>
                                <div>
                                    <p class="mb-2">Accept payments via</p>
                                    <select class="form-select mb-4">
                                        <option value="Bank Account">Bank Account</option>
                                        <option value="Paypal">Paypal</option>
                                        <option value="Card">Credit/Debit Card</option>
                                        <option value="UPI Transfer">UPI Transfer</option>
                                    </select>
                                    <div class="d-flex justify-content-between mb-2">
                                        <label for="payment-terms" class="mb-0">Payment Terms</label>
                                        <label class="switch switch-primary me-0">
                                            <input type="checkbox" class="switch-input" id="payment-terms" checked />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                            <span class="switch-label"></span>
                                        </label>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <label for="client-notes" class="mb-0">Client Notes</label>
                                        <label class="switch switch-primary me-0">
                                            <input type="checkbox" class="switch-input" id="client-notes" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                            <span class="switch-label"></span>
                                        </label>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <label for="payment-stub" class="mb-0">Payment Stub</label>
                                        <label class="switch switch-primary me-0">
                                            <input type="checkbox" class="switch-input" id="payment-stub" />
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                            <span class="switch-label"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>


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
    setButtonColor();
    preloader(300, action);
    </script>

</body>

<script>
<?php include '../js/package.js'; ?>
</script>

</html>