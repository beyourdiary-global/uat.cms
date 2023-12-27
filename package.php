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
$errorMsgAlert = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";

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
    deleteRecord($tblName, $dataID, $row['name'], $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data ";
    } else {
        $viewActMsg = USER_NAME . " viewed the data <b>" . $row['name'] . "</b> from <b><i>$tblName Table</i></b>.";
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

            $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName)
                        array_push($newvalarr, $currentDataName);

                    if ($pkg_price)
                        array_push($newvalarr, $pkg_price);

                    if ($cur_unit)
                        array_push($newvalarr, $cur_unit);

                    if ($prod_list)
                        array_push($newvalarr, $prod_list);

                    if ($barcode_slot_total)
                        array_push($newvalarr, $barcode_slot_total);

                    if ($dataRemark)
                        array_push($newvalarr, $dataRemark);

                    $query = "INSERT INTO " . $tblName . "(name,price,currency_unit,product,barcode_slot_total,remark,create_by,create_date,create_time) VALUES ('$currentDataName','$pkg_price','$cur_unit','$prod_list','$barcode_slot_total','$dataRemark','" . USER_ID . "',curdate(),curtime())";

                    $returnData = mysqli_query($connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            } else {
                try {
                    if ($row['name'] != $currentDataName) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $currentDataName);
                    }

                    if ($row['price'] != $pkg_price) {
                        array_push($oldvalarr, $row['price']);
                        array_push($chgvalarr, $pkg_price);
                    }

                    if ($row['currency_unit'] != $cur_unit) {
                        array_push($oldvalarr, $row['currency_unit']);
                        array_push($chgvalarr, $cur_unit);
                    }

                    if ($row['product'] != $prod_list) {
                        array_push($oldvalarr, $row['product']);
                        array_push($chgvalarr, $prod_list);
                    }

                    if ($row['barcode_slot_total'] != $barcode_slot_total) {
                        array_push($oldvalarr, $row['barcode_slot_total']);
                        array_push($chgvalarr, $barcode_slot_total);
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
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
                }
            }

            if (isset($errorMsg)) {
                $act = "F";
                $errorMsg = str_replace('\'', '', $errorMsg);
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

                    if (isset($returnData)) {
                        $log['act_msg'] = USER_NAME . " added <b>$currentDataName</b> into <b><i>$tblName Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>$currentDataName</b> into <b><i>$tblName Table</i></b> ( $errorMsg )";
                    }
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($oldvalarr, $chgvalarr, $tblName, (isset($returnData) ? '' : $errorMsg));
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
    async function setBarcodeSlotTotal(rowCount) {
        var num = 1;
        // init
        var totalSlot_id = $('#barcode_slot_total');
        totalSlot_id.text(0);

        while (num <= rowCount) {
            totalSlot = parseInt(totalSlot_id.text());
            var barcodeSlot_id = $('#barcode_slot_' + num);

            if (barcodeSlot_id !== 0) {
                var barcodeSlot = parseInt(barcodeSlot_id.val());

                if (!isNaN(barcodeSlot))
                    totalSlot += barcodeSlot;

                totalSlot_id.text(totalSlot);
                totalSlot_id.append('<input name="barcode_slot_total_hidden" id="barcode_slot_total_hidden" type="hidden" value="' + totalSlot + '">');

                num++;
            }
        }
    }

    function Add() {
        AddRow($("#prod_name").val(), $("#prod_val").val(), $("#wgt").val(), $("#wgt_unit").val(), $("#wgt_unit_val").val(), $("#barcode_status").val(), $("#barcode_slot").val());
    };

    function AddRow() {
        //Get the reference of the Table's TBODY element.
        var tBody = $("#productList > TBODY")[0];
        var numbering = +$("#productList > TBODY > TR:last > TD:first").text();
        numbering += 1;
        numbering = numbering.toFixed(0)

        //Add Row.
        row = tBody.insertRow(-1);

        //Add cell.
        var cell = $(row.insertCell(-1));
        cell.html(numbering);
        var cell = $(row.insertCell(-1));
        cell.html('<input type="text" name="prod_name[]" id="prod_name_' + numbering + '" value="" onkeyup="prodInfo(this)"><input type="hidden" name="prod_val[]" id="prod_val_' + numbering + '" value="" oninput="prodInfoAutoFill(this)">');
        cell.addClass('autocomplete');
        cell = $(row.insertCell(-1));
        cell.html('<input class="readonlyInput" type="text" name="wgt[]" id="wgt_' + numbering + '" value="" readonly>');
        cell = $(row.insertCell(-1));
        cell.html('<input class="readonlyInput" type="text" name="wgt_unit[]" id="wgt_unit_' + numbering + '" value="" readonly><input type="hidden" name="wgt_unit_val[]" id="wgt_unit_val_' + numbering + '" value="" readonly>');
        cell = $(row.insertCell(-1));
        cell.html('<input class="readonlyInput" type="text" name="barcode_status[]" id="barcode_status_' + numbering + '" value="" readonly>');
        cell = $(row.insertCell(-1));
        cell.html('<input class="readonlyInput" type="text" name="barcode_slot[]" id="barcode_slot_' + numbering + '" value="" readonly>');

        //Add Button cell.
        cell = $(row.insertCell(-1));
        var btnRemove = $('<button class="mt-1" id="action_menu_btn"><i class="fa-regular fa-trash-can fa-xl" style="color:#ff0000"></i></button>');
        btnRemove.attr("type", "button");
        btnRemove.attr("onclick", "Remove(this);");
        btnRemove.val("Remove");
        cell.append(btnRemove);
    };

    function Remove(button) {
        //Determine the reference of the Row using the Button.
        var row = $(button).closest("TR");
        var name = $("TD", row).eq(0).html();
        var rowCount = parseInt($("#productList TBODY TR:last TD").eq(0).html());

        if (confirm("Do you want to delete: " + name)) {

            //Get the reference of the Table.
            var table = $("#productList")[0];

            //Delete the Table row using it's Index.
            table.deleteRow(row[0].rowIndex);

            //Recalc barcode slot
            setBarcodeSlotTotal(rowCount);
        }
    };

    // product autofill
    function prodInfo(element) {
        var id = $(element).attr('id').split('_');
        id = id[(id.length) - 1];

        if (!($(element).attr('readonly'))) {
            var param = {
                search: $(element).val(),
                searchType: 'name',
                page: 'package',
                elementID: $(element).attr('id'),
                hiddenElementID: 'prod_val_' + id,
                dbTable: '<?= PROD ?>'
            }
            searchInput(param);

            if ($(element).val() == '') {
                $('#prod_val_' + id).val('');
                $('#wgt_' + id).val('');
                $('#wgt_unit_' + id).val('');
                $('#wgt_unit_val_' + id).val('');
                $('#barcode_status_' + id).val('');
                $('#barcode_slot_' + id).val('');
            }
        }
    }

    function prodInfoAutoFill(element) {
        var id = $(element).attr('id').split('_');
        id = id[(id.length) - 1];
        var prodArr = [];
        var wgtArr = [];
        var rowCount = parseInt($("#productList TBODY TR:last TD").eq(0).html());

        var retrieveProdInfo = async () => {
            prodArr = await retrieveJSONData($(element).attr('value'), 'id', '<?= PROD ?>');
        }

        var setProdInfo = async () => {
            $('#wgt_' + id).val(prodArr[0]['weight']);
            $('#wgt_unit_val_' + id).val(prodArr[0]['weight_unit']);
            $('#barcode_status_' + id).val(prodArr[0]['barcode_status']);
            $('#barcode_slot_' + id).val(prodArr[0]['barcode_slot']);
        }

        var retrieveWgtUnit = async () => {
            wgtArr = await retrieveJSONData($('#wgt_unit_val_' + id).attr('value'), 'id', '<?= WGT_UNIT ?>');
        }

        var setWgtUnit = async () => {
            $('#wgt_unit_' + id).val(wgtArr[0]['unit']);
        }

        var allFunc = async () => {
            await retrieveProdInfo();
            await setProdInfo();
            await retrieveWgtUnit();
            await setWgtUnit();
            await setBarcodeSlotTotal(rowCount);
        }

        allFunc();
    }

    $(document).ready(function() {
        if (!($("#cur_unit").attr('readonly'))) {
            $("#cur_unit").keyup(function() {
                var param = {
                    search: $(this).val(),
                    searchType: 'unit',
                    elementID: $(this).attr('id'),
                    hiddenElementID: $(this).attr('id') + '_hidden',
                    dbTable: '<?= CUR_UNIT ?>'
                }
                searchInput(param);
            });
            $("#cur_unit").change(function() {
                if ($(this).val() == '')
                    $('#' + $(this).attr('id') + '_hidden').val('');
            });
        }
    })
</script>

</html>