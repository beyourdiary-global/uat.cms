<?php
$pageTitle = "Package";
include 'menuHeader.php';

$pkg_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/package_table.php';
$tblname = PKG;

// to display data to input
if($pkg_id)
{
    $rst = getData('*',"id = '$pkg_id'",'',$tblname,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc(); 
    }
}

/* if(!($pkg_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>"); */

if(post('actionBtn'))
{
    // top
    $pkg_name = postSpaceFilter('package_name');
    $pkg_price = postSpaceFilter('price');
    $cur_unit = postSpaceFilter('cur_unit_hidden');

    // middle
    $prod_list = post('prod_val');
    $prod_list = implode(',', array_filter($prod_list));


    $barcode_slot_total = postSpaceFilter('barcode_slot_total_hidden');
    $pkg_remark = postSpaceFilter('package_remark');

    $action = post('actionBtn');

    switch($action)
    {
        case 'addPkg': case 'updPkg':
             if($pkg_name == '')
            {
                $err = "Package Name cannot be empty.";
            }

            if($pkg_price == '')
            {
                $err2 = "Package Price cannot be empty.";
            }

            if($cur_unit == '')
            {
                $err3 = "Currency Unit cannot be empty.";
            }

            if($prod_list == '')
            {
                $err4 = "Must have at least one product inside the package.";
            }


            if (isDuplicateRecord("name", $pkg_name, $tblname, $connect, $pkg_id) && isDuplicateRecord("price", $pkg_price, $tblname, $connect, $pkg_id) && isDuplicateRecord("currency_unit", $cur_unit, $tblname, $connect, $pkg_id) && isDuplicateRecord("product", $prod_list, $tblname, $connect, $pkg_id)) {
                $err5 = "Duplicate record found for this package.";
                break;
            }else if ($pkg_name != '' && $pkg_price != '' && $cur_unit != '' && $prod_list != '' && !isset($err5)) {
                if($action == 'addPkg')
                {   
                    try
                    {
                        $query = "INSERT INTO ".$tblname."(name,price,currency_unit,product,barcode_slot_total,remark,create_date,create_time,create_by) VALUES ('$pkg_name','$pkg_price','$cur_unit','$prod_list','$barcode_slot_total','$pkg_remark',curdate(),curtime(),'".USER_ID."')";
                        mysqli_query($connect, $query);
                        generateDBData($tblname, $connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($pkg_name != '')
                            array_push($newvalarr, $pkg_name);

                        if($pkg_price != '')
                            array_push($newvalarr, $pkg_price);

                        if($cur_unit != '')
                            array_push($newvalarr, $cur_unit);

                        if($prod_list != '')
                            array_push($newvalarr, $prod_list);
                        
                        if($barcode_slot_total != '')
                            array_push($newvalarr, $barcode_slot_total);
                        
                        if($pkg_remark != '')
                            array_push($newvalarr, $pkg_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$pkg_name</b> into <b><i>Package Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = $tblname;
                        $log['page'] = 'Package';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
                else
                {
                    try
                    {
                        // take old value
                        $rst = getData('*',"id = '$pkg_id'",'',$tblname,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $pkg_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $pkg_name);
                        }

                        if($row['price'] != $pkg_price)
                        {
                            array_push($oldvalarr, $row['price']);
                            array_push($chgvalarr, $pkg_price);
                        }

                        if($row['currency_unit'] != $cur_unit)
                        {
                            array_push($oldvalarr, $row['currency_unit']);
                            array_push($chgvalarr, $cur_unit);
                        }

                        if($row['product'] != $prod_list)
                        {
                            array_push($oldvalarr, $row['product']);
                            array_push($chgvalarr, $prod_list);
                        }

                        if($row['barcode_slot_total'] != $barcode_slot_total)
                        {
                            array_push($oldvalarr, $row['barcode_slot_total']);
                            array_push($chgvalarr, $barcode_slot_total);
                        }

                        if($row['remark'] != $pkg_remark)
                        {
                            if($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if($pkg_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $pkg_remark;
                            
                            array_push($chgvalarr, $new_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".$tblname." SET name ='$pkg_name', price ='$pkg_price', currency_unit ='$cur_unit', product ='$prod_list', barcode_slot_total ='$barcode_slot_total', remark ='$pkg_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$pkg_id'";
                            mysqli_query($connect, $query);
                            generateDBData($tblname, $connect);

                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = USER_ID;

                            $log['act_msg'] = USER_NAME . " edited the data";
                            for($i=0; $i<sizeof($oldvalarr); $i++)
                            {
                                if($i==0)
                                    $log['act_msg'] .= " from <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                            }
                            $log['act_msg'] .= " from <b><i>Package Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = $tblname;
                            $log['page'] = 'Package';
                            $log['oldval'] = $oldval;
                            $log['changes'] = $chgval;
                            $log['connect'] = $connect;
                            audit_log($log);
                        }
                        else $act = 'NC';
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            break;
        case 'back':
            echo("<script>location.href = '$redirect_page';</script>");
            break;
    }
}

if(post('act') == 'D')
{
    $id = post('id');
    
    if($id)
    {
        try
        {
            // take unit
            $rst = getData('*',"id = '$id'",'',$tblname,$connect);
            $row = $rst->fetch_assoc();

            $pkg_id = $row['id'];
            $pkg_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord($tblname,$id,$pkg_name,$connect,$cdate,$ctime,$pageTitle);
            
            generateDBData($tblname, $connect);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = USER_ID;
            $log['act_msg'] = USER_NAME . " deleted the data <b>$pkg_name</b> from <b><i>Package Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = $tblname;
            $log['page'] = 'Package';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($pkg_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $pkg_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$pkg_name</b> from <b><i>Package Table</i></b>.";
    $log['page'] = 'Package';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/package.css">
</head>

<body>

<div class="d-flex flex-column my-3 ms-3">
    <p><a href="<?= $redirect_page ?>">Package</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add Package'; break;
        case 'E': echo 'Edit Package'; break;
        default: echo 'View Package';
    }
    ?></p>
</div>

<div id="packageFormContainer" class="container-fluid mt-2">
    <div class="col-12 col-md-12 formWidthAdjust">
        <form id="packageForm" method="post" action="">
            <div class="row">
                <div class="form-group my-3">
                    <h2>
                        <?php
                        switch($act)
                        {
                            case 'I': echo 'Add Package'; break;
                            case 'E': echo 'Edit Package'; break;
                            default: echo 'View Package';
                        }
                        ?>
                    </h2>
            </div>

            <div id="err_msg"  class="mb-3">
                 <span class="mt-n1" style="font-size: 21px;"><?php if (isset($err5)) echo $err5; ?></span>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="package_name_lbl" for="package_name">Name</label>
                        <input class="form-control" type="text" name="package_name" id="package_name" value=
                        "<?php
                            if(isset($pkg_name))
                                echo $pkg_name;
                            else if(isset($dataExisted) && isset($row['name'])) 
                                echo $row['name'];

                        ?>" <?php if($act == '') echo 'readonly' ?>>
                        <div id="err_msg">
                            <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="price_lbl" for="price">Selling Price</label>
                        <input class="form-control" type="number" name="price" id="price" value=
                        "<?php
                            if(isset($pkg_price))
                                echo $pkg_price;
                            else if(isset($dataExisted) && isset($row['price'])) 
                                echo $row['price'];

                        ?>" <?php if($act == '') echo 'readonly' ?>>
                        <div id="err_msg">
                            <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group autocomplete mb-3">
                        <label class="form-label form_lbl" id="cur_unit_lbl" for="cur_unit">Currency Unit</label>
                        <input class="form-control" type="text" name="cur_unit" id="cur_unit" value=
                        "<?php
                            unset($echoVal);
                            if(isset($cur_unit) && $cur_unit != '')
                                $echoVal = $cur_unit;
                            else if(isset($dataExisted) && isset($row['currency_unit']))
                                $echoVal = $row['currency_unit'];

                            if(isset($echoVal))
                            {
                                $product_info_result = getData('unit',"id = '$echoVal'",'',CUR_UNIT,$connect);
                                $product_info_row = $product_info_result->fetch_assoc();
                                echo $product_info_row['unit'];
                            }
                        ?>" <?php if($act == '') echo 'readonly' ?>>
                        <input type="hidden" name="cur_unit_hidden" id="cur_unit_hidden" value=
                        "<?php
                            if(isset($cur_unit) && $cur_unit != '')
                                echo $cur_unit;
                            else if(isset($dataExisted) && isset($row['currency_unit'])) 
                                echo $row['currency_unit'];

                        ?>">
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
                                if($act != '')
                                    $readonly = '';
                                else
                                    $readonly = ' readonly';

                                // get value
                                unset($echoVal);
                                if(isset($prod_list) && $prod_list != '')
                                    $echoVal = $prod_list;
                                else if(isset($dataExisted) && isset($row['product']))
                                    $echoVal = $row['product'];

                                // echo
                                if(isset($echoVal))
                                {
                                    $num = 1; // numbering
                                    $echoVal = explode(',',$echoVal);
                                    foreach($echoVal as $prod_id)
                                    {
                                        // product info
                                        $product_info_result = getData('*',"id = '$prod_id'",'',PROD,$connect);
                                        $product_info_row = $product_info_result->fetch_assoc();

                                        $pid = $product_info_row['id'];
                                        $pn = $product_info_row['name'];
                                        $pw = $product_info_row['weight'];
                                        $pwu = $product_info_row['weight_unit'];
                                        $ps = $product_info_row['barcode_status'];
                                        $pslot = $product_info_row['barcode_slot'];

                                        // weight unit info
                                        $product_info_result = getData('unit',"id = '$pwu'",'',WGT_UNIT,$connect);
                                        $product_info_row = $product_info_result->fetch_assoc();

                                        $pwun = $product_info_row['unit'];
                            ?>
                            <tr>
                                <td><?= $num ?></td>
                                <td class="autocomplete"><input type="text" name="prod_name[]" id="prod_name_<?= $num ?>" value="<?= $pn ?>" onkeyup="prodInfo(this)" <?= $readonly ?>><input type="hidden" name="prod_val[]" id="prod_val_<?= $num ?>" value="<?= $pid ?>" oninput="prodInfoAutoFill(this)">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err4)) echo $err4; ?></span>
                                </div></td>
                                <td><input class="readonlyInput" type="text" name="wgt[]" id="wgt_<?= $num ?>" value="<?= $pw ?>" readonly></td>
                                <td><input class="readonlyInput" type="text" name="wgt_unit[]" id="wgt_unit_<?= $num ?>" value="<?= $pwun ?>" readonly><input type="hidden" name="wgt_unit_val[]" id="wgt_unit_val_<?= $num ?>" value="<?= $pwu ?>" readonly></td>
                                <td><input class="readonlyInput" type="text" name="barcode_status[]" id="barcode_status_<?= $num ?>" value="<?= $ps ?>" readonly></td>
                                <td><input class="readonlyInput" type="text" name="barcode_slot[]" id="barcode_slot_<?= $num ?>" value="<?= $pslot ?>" readonly></td>
                            <?php
                                if($act != '')
                                {
                                    if($num == 1)
                                    {
                            ?>
                                <td><button class="mt-1"id="action_menu_btn" type="button" onclick="Add()"><i class="fa-regular fa-square-plus fa-xl" style="color:#37c22e"></i></button></td>
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
                                </div></td>
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
                                <td scope="col" id="barcode_slot_total" style="text-align:center"><?php
                                if(isset($barcode_slot_total) && $barcode_slot_total != '')
                                    echo $barcode_slot_total;
                                else
                                {
                                    if(isset($dataExisted) && isset($row['barcode_slot_total'])) 
                                        echo $row['barcode_slot_total'];
                                    else echo '0';
                                }
                                ?><input name="barcode_slot_total_hidden" id="barcode_slot_total_hidden" type="hidden" value="<?php
                                if(isset($barcode_slot_total) && $barcode_slot_total != '')
                                    echo $barcode_slot_total;
                                else if(isset($dataExisted) && isset($row['barcode_slot_total'])) 
                                    echo $row['barcode_slot_total'];

                                ?>"></td>
                                <td scope="col"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="package_remark_lbl" for="package_remark">Remark</label>
                        <textarea class="form-control" id="package_remark" name="package_remark" style="width:100%" rows="5"><?php
                        if(isset($pkg_remark) && $pkg_remark != '')
                            echo $pkg_remark;
                        else if(isset($dataExisted) && isset($row['remark'])) 
                            echo $row['remark'];

                        ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-3 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                            switch($act)
                            {
                                case 'I':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addPkg">Add Package</button>';
                                    break;
                                case 'E':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updPkg">Edit Package</button>';
                                    break;
                            }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Package","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
<script>
/**
  oufei 20231014
  common.fun.js
  function(void)
  to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
*/
dropdownMenuDispFix();

async function setBarcodeSlotTotal(rowCount) {
    var num = 1;
    // init
    var totalSlot_id = $('#barcode_slot_total');
    totalSlot_id.text(0);

    while(num <= rowCount)
    {
        totalSlot = parseInt(totalSlot_id.text());
        var barcodeSlot_id = $('#barcode_slot_'+num);
        
        if(barcodeSlot_id !== 0)
        {
            var barcodeSlot = parseInt(barcodeSlot_id.val());

            if(!isNaN(barcodeSlot))
                totalSlot += barcodeSlot;

            totalSlot_id.text(totalSlot);
            totalSlot_id.append('<input name="barcode_slot_total_hidden" id="barcode_slot_total_hidden" type="hidden" value="'+totalSlot+'">');

            num++;
        }
    }
}

function Add() {
    AddRow($("#prod_name").val(),$("#prod_val").val(),$("#wgt").val(),$("#wgt_unit").val(),$("#wgt_unit_val").val(),$("#barcode_status").val(),$("#barcode_slot").val());
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
    cell.html('<input type="text" name="prod_name[]" id="prod_name_'+numbering+'" value="" onkeyup="prodInfo(this)"><input type="hidden" name="prod_val[]" id="prod_val_'+numbering+'" value="" oninput="prodInfoAutoFill(this)">');
    cell.addClass('autocomplete');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="wgt[]" id="wgt_'+numbering+'" value="" readonly>');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="wgt_unit[]" id="wgt_unit_'+numbering+'" value="" readonly><input type="hidden" name="wgt_unit_val[]" id="wgt_unit_val_'+numbering+'" value="" readonly>');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="barcode_status[]" id="barcode_status_'+numbering+'" value="" readonly>');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="barcode_slot[]" id="barcode_slot_'+numbering+'" value="" readonly>');

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
    id = id[(id.length)-1];

    if(!($(element).attr('readonly')))
    {
        var param = {
            search: $(element).val(),
            searchType: 'name',
            page: 'package',
            elementID: $(element).attr('id'),
            hiddenElementID: 'prod_val_'+id,
            dbTable: '<?= PROD ?>'
        }
        searchInput(param);

        if($(element).val() == '')
        {
            $('#prod_val_'+id).val('');
            $('#wgt_'+id).val('');
            $('#wgt_unit_'+id).val('');
            $('#wgt_unit_val_'+id).val('');
            $('#barcode_status_'+id).val('');
            $('#barcode_slot_'+id).val('');
        }
    }
}

function prodInfoAutoFill(element) {
	var id = $(element).attr('id').split('_');
    id = id[(id.length)-1];
    var prodArr = [];
    var wgtArr = [];
    var rowCount = parseInt($("#productList TBODY TR:last TD").eq(0).html());
    
    var retrieveProdInfo = async () => {
        prodArr = await retrieveJSONData($(element).attr('value'),'id','<?= PROD ?>');
    }

    var setProdInfo = async () => {
        $('#wgt_'+id).val(prodArr[0]['weight']);
        $('#wgt_unit_val_'+id).val(prodArr[0]['weight_unit']);
        $('#barcode_status_'+id).val(prodArr[0]['barcode_status']);
        $('#barcode_slot_'+id).val(prodArr[0]['barcode_slot']);
    }

    var retrieveWgtUnit = async () => {
        wgtArr = await retrieveJSONData($('#wgt_unit_val_'+id).attr('value'),'id','<?= WGT_UNIT ?>');
    }

    var setWgtUnit = async () => {
        $('#wgt_unit_'+id).val(wgtArr[0]['unit']);
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

$(document).ready(function () {
    if(!($("#cur_unit").attr('readonly')))
    {
        $("#cur_unit").keyup(function(){
            var param = {
                search: $(this).val(),
                searchType: 'unit',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= CUR_UNIT ?>'
            }
            searchInput(param);
        });
        $("#cur_unit").change(function(){
            if($(this).val() == '')
                $('#'+$(this).attr('id')+'_hidden').val('');
        });
    }
})
</script>
</html>