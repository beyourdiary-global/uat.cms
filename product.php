<?php
$pageTitle = "Product";
include 'menuHeader.php';

$prod_id = input('id');
$act = input('act');
$redirect_page = 'product_table.php';
$tblname = PROD;

// to display data to input
if($prod_id)
{
    $rst = getData('*',"id = '$prod_id'",$tblname,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($prod_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $prod_name = postSpaceFilter('prod_name');
    $prod_brand = postSpaceFilter('prod_brand_hidden');
    $prod_wgt = postSpaceFilter('prod_wgt');
    $prod_wgt_unit = postSpaceFilter('prod_wgt_unit_hidden');
    $prod_cost = postSpaceFilter('prod_cost');
    $prod_cur_unit = postSpaceFilter('prod_cur_unit_hidden');
    $prod_barcode_status = postSpaceFilter('prod_barcode_status') == 'Yes' ? 'Yes' : 'No';
    $prod_barcode_slot = $prod_barcode_status == 'Yes' ? postSpaceFilter('prod_barcode_slot') : '';
    $prod_expire_date = postSpaceFilter('prod_expire_date');
    $parent_prod = postSpaceFilter('parent_prod_hidden');

    $action = post('actionBtn');

    switch($action)
    {
        case 'addProd': case 'updProd':
             if($prod_name == '')
            {
                $err = "Product Name cannot be empty.";
            }

            if($prod_brand == '')
            {
                $err2 = "Product Brand cannot be empty.";
            }

            if($prod_wgt == '')
            {
                $err3 = "Product Weight cannot be empty.";
            }

            if($prod_wgt_unit == '')
            {
                $err4 = "Weight Unit cannot be empty.";
            }

            if($prod_cost == '')
            {
                $err5 = "Product Cost cannot be empty.";
            }

            if($prod_cur_unit == '')
            {
                $err6 = "Currency Unit cannot be empty.";
            }

            if($prod_barcode_status == 'Yes' &&  $prod_barcode_slot == '')
            {
                $err7 = "Product Barcode Slot cannot be empty.";
            }

            if($prod_expire_date == '')
            {
                $err8 = "Product Expire Date cannot be empty.";
            }

            if($prod_name != '' && $prod_brand != '' && $prod_wgt != '' && $prod_wgt_unit != '' && $prod_cost != '' && $prod_cur_unit != '' && $prod_barcode_status != '' && $prod_expire_date != '')
            {
                if($action == 'addProd')
                {
                    try
                    {
                        $query = "INSERT INTO ".$tblname."(name,brand,weight,weight_unit,cost,currency_unit,barcode_status,barcode_slot,expire_date,parent_product,create_date,create_time,create_by) VALUES ('$prod_name','$prod_brand','$prod_wgt','$prod_wgt_unit','$prod_cost','$prod_cur_unit','$prod_barcode_status','$prod_barcode_slot','$prod_expire_date','$parent_prod',curdate(),curtime(),'".$_SESSION['userid']."')";
                        mysqli_query($connect, $query);
                        generateDBData($tblname, $connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($prod_name != '')
                            array_push($newvalarr, $prod_name);

                        if($prod_brand != '')
                            array_push($newvalarr, $prod_brand);

                        if($prod_wgt != '')
                            array_push($newvalarr, $prod_wgt);

                        if($prod_wgt_unit != '')
                            array_push($newvalarr, $prod_wgt_unit);
                        
                        if($prod_cost != '')
                            array_push($newvalarr, $prod_cost);
                        
                        if($prod_cur_unit != '')
                            array_push($newvalarr, $prod_cur_unit);
                        
                        if($prod_barcode_status != '')
                            array_push($newvalarr, $prod_barcode_status);
                        
                        if($prod_barcode_slot != '')
                            array_push($newvalarr, $prod_barcode_slot);
                        
                        if($prod_expire_date != '')
                            array_push($newvalarr, $prod_expire_date);
                        
                        if($parent_prod != '')
                            array_push($newvalarr, $parent_prod);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$prod_name</b> into <b><i>Product Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = $tblname;
                        $log['page'] = 'Product';
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
                        $rst = getData('*',"id = '$prod_id'",$tblname,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $prod_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $prod_name);
                        }

                        if($row['brand'] != $prod_brand)
                        {
                            array_push($oldvalarr, $row['brand']);
                            array_push($chgvalarr, $prod_brand);
                        }

                        if($row['weight'] != $prod_wgt)
                        {
                            array_push($oldvalarr, $row['weight']);
                            array_push($chgvalarr, $prod_wgt);
                        }

                        if($row['weight_unit'] != $prod_wgt_unit)
                        {
                            array_push($oldvalarr, $row['weight_unit']);
                            array_push($chgvalarr, $prod_wgt_unit);
                        }

                        if($row['cost'] != $prod_cost)
                        {
                            array_push($oldvalarr, $row['cost']);
                            array_push($chgvalarr, $prod_cost);
                        }

                        if($row['currency_unit'] != $prod_cur_unit)
                        {
                            array_push($oldvalarr, $row['currency_unit']);
                            array_push($chgvalarr, $prod_cur_unit);
                        }

                        if($row['barcode_status'] != $prod_barcode_status)
                        {
                            array_push($oldvalarr, $row['barcode_status']);
                            array_push($chgvalarr, $prod_barcode_status);
                        }

                        if($row['barcode_slot'] != $prod_barcode_slot)
                        {
                            array_push($oldvalarr, $row['barcode_slot']);
                            array_push($chgvalarr, $prod_barcode_slot);
                        }

                        if($row['expire_date'] != $prod_expire_date)
                        {
                            array_push($oldvalarr, $row['expire_date']);
                            array_push($chgvalarr, $prod_expire_date);
                        }

                        if($row['parent_product'] != $parent_prod)
                        {
                            array_push($oldvalarr, $row['parent_product']);
                            array_push($chgvalarr, $parent_prod);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".$tblname." SET name ='$prod_name', brand ='$prod_brand', weight ='$prod_wgt', weight_unit ='$prod_wgt_unit', cost ='$prod_cost', currency_unit ='$prod_cur_unit', barcode_status ='$prod_barcode_status', barcode_slot ='$prod_barcode_slot', expire_date ='$prod_expire_date', parent_product ='$parent_prod', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '$prod_id'";
                            mysqli_query($connect, $query);
                            generateDBData($tblname, $connect);

                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = $_SESSION['userid'];

                            $log['act_msg'] = $_SESSION['user_name'] . " edited the data";
                            for($i=0; $i<sizeof($oldvalarr); $i++)
                            {
                                if($i==0)
                                    $log['act_msg'] .= " from <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                            }
                            $log['act_msg'] .= " from <b><i>Product Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = $tblname;
                            $log['page'] = 'Product';
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
            $rst = getData('*',"id = '$id'",$tblname,$connect);
            $row = $rst->fetch_assoc();

            $prod_id = $row['id'];
            $prod_name = $row['name'];

            $query = "DELETE FROM ".$tblname." WHERE id = ".$id;
            mysqli_query($connect, $query);
            generateDBData($tblname, $connect);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$prod_name</b> from <b><i>Product Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = $tblname;
            $log['page'] = 'Product';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($prod_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $prod_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$prod_name</b> from <b><i>Product Table</i></b>.";
    $log['page'] = 'Product';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
</head>

<body>

<div class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6">
            <form id="prodForm" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group my-5">
                            <h2>
                                <?php
                                switch($act)
                                {
                                    case 'I': echo 'Add Product'; break;
                                    case 'E': echo 'Edit Product'; break;
                                    default: echo 'View Product';
                                }
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="prod_name_lbl" for="prod_name">Product Name</label>
                            <input class="form-control" type="text" name="prod_name" id="prod_name" value=
                            "<?php
                                if(isset($prod_name))
                                    echo $prod_name;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['name'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="prod_brand_lbl" for="prod_brand">Product Brand</label>
                            <input class="form-control" type="text" name="prod_brand" id="prod_brand" value=
                            "<?php
                                unset($echoVal);
                                if(isset($prod_brand) && $prod_brand != '')
                                    $echoVal = $prod_brand;
                                else
                                {
                                    if(isset($dataExisted))
                                        $echoVal = $row['brand'];
                                }

                                if(isset($echoVal))
                                {
                                    $n_rst = getData('name',"id = '$echoVal'",BRAND,$connect);
                                    $n = $n_rst->fetch_assoc();
                                    echo $n['name'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <input type="hidden" name="prod_brand_hidden" id="prod_brand_hidden" value=
                            "<?php
                                if(isset($prod_brand) && $prod_brand != '')
                                    echo $prod_brand;
                                else
                                {
                                    if(isset($dataExisted))
                                        echo $row['brand'];
                                }
                            ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="prod_wgt_lbl" for="prod_wgt">Product Weight</label>
                            <input class="form-control" type="text" name="prod_wgt" id="prod_wgt" value=
                            "<?php
                                unset($echoVal);
                                if(isset($prod_wgt) && $prod_wgt != '')
                                    echo $prod_wgt;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['weight'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err3)) echo $err3; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="prod_wgt_unit_lbl" for="prod_wgt_unit">Product Weight Unit</label>
                            <input class="form-control" type="text" name="prod_wgt_unit" id="prod_wgt_unit" value=
                            "<?php
                                unset($echoVal);
                                if(isset($prod_wgt_unit) && $prod_wgt_unit != '')
                                    $echoVal = $prod_wgt_unit;
                                else
                                {
                                    if(isset($dataExisted))
                                        $echoVal = $row['weight_unit'];
                                }

                                if(isset($echoVal))
                                {
                                    $u_rst = getData('unit',"id = '$echoVal'",WGT_UNIT,$connect);
                                    $u = $u_rst->fetch_assoc();
                                    echo $u['unit'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <input type="hidden" name="prod_wgt_unit_hidden" id="prod_wgt_unit_hidden" value=
                            "<?php 
                                if(isset($prod_wgt_unit) && $prod_wgt_unit != '')
                                    echo $prod_wgt_unit;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['weight_unit'];
                                } 
                            ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err4)) echo $err4; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="prod_cost_lbl" for="prod_cost">Product Cost</label>
                            <input class="form-control" type="number" name="prod_cost" min="0" step=".01" id="prod_cost" value=
                            "<?php 
                                if(isset($prod_cost) && $prod_cost != '')
                                    echo $prod_cost;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['cost'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err5)) echo $err5; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="prod_cur_unit_lbl" for="prod_cur_unit">Product Currency Unit</label>
                            <input class="form-control" type="text" name="prod_cur_unit" id="prod_cur_unit" value=
                            "<?php
                                unset($echoVal);
                                if(isset($prod_cur_unit) && $prod_cur_unit != '')
                                    $echoVal = $prod_cur_unit;
                                else
                                {
                                    if(isset($dataExisted))
                                        $echoVal = $row['currency_unit'];
                                }

                                if(isset($echoVal))
                                {
                                    $u_rst = getData('unit',"id = '$echoVal'",CUR_UNIT,$connect);
                                    $u = $u_rst->fetch_assoc();
                                    echo $u['unit'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <input type="hidden" name="prod_cur_unit_hidden" id="prod_cur_unit_hidden" value=
                            "<?php
                                if(isset($prod_cur_unit) && $prod_cur_unit != '')
                                    echo $prod_cur_unit;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['currency_unit'];
                                }
                            ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err6)) echo $err6; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 d-flex align-items-center">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="prod_barcode_status_lbl" for="prod_barcode_status">Record Barcode?</label>
                            <input class="form-check-input ms-1" type="checkbox" name="prod_barcode_status" id="prod_barcode_status" value="Yes" <?php
                                if($act == '') 
                                    echo 'disabled';

                                if(isset($prod_barcode_status))
                                    echo $prod_barcode_status != 'Yes' ?: ' checked';
                                else
                                {
                                    if(isset($dataExisted)) 
                                    echo $row['barcode_status'] != 'Yes' ? '' : ' checked';
                                }
                            ?>>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" style="display:none" id="prod_barcode_slot_lbl" for="prod_barcode_slot">Product Barcode Slot</label>
                            <input class="form-control" style="display:none" type="text" name="prod_barcode_slot" id="prod_barcode_slot" value=
                            "<?php
                                if(isset($prod_barcode_slot) && $prod_barcode_slot != '')
                                    echo $prod_barcode_slot;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['barcode_slot'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err7)) echo $err7; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="prod_expire_date_lbl" for="prod_expire_date">Product Expire Date</label>
                            <input class="form-control" type="date" name="prod_expire_date" id="prod_expire_date" value=
                            "<?php 
                                if(isset($prod_expire_date) && $prod_expire_date != '')
                                    echo $prod_expire_date;
                                else
                                {
                                    if(isset($dataExisted)) 
                                        echo $row['expire_date'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err8)) echo $err8; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="parent_prod_lbl" for="parent_prod">Parent Product</label>
                            <input class="form-control" type="text" name="parent_prod" id="parent_prod" value=
                            "<?php
                                unset($echoVal);
                                if(isset($parent_prod) && $parent_prod != '')
                                    $echoVal = $parent_prod;
                                else
                                {
                                    if(isset($dataExisted))
                                        $echoVal = $row['parent_product'];
                                }

                                if(isset($echoVal) && $echoVal != '')
                                {
                                    $n_rst = getData('name',"id = '$echoVal'",PROD,$connect);
                                    $n = $n_rst->fetch_assoc();
                                    echo $n['name'];
                                }
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <input type="hidden" name="parent_prod_hidden" id="parent_prod_hidden" value=
                            "<?php
                            if(isset($parent_prod) && $parent_prod != '')
                                echo $parent_prod;
                            else
                            {
                                if(isset($dataExisted) && $row['parent_product'] != '')
                                    echo $row['parent_product'];
                            }
                            ?>">
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center">
                        <?php
                            switch($act)
                            {
                                case 'I':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addProd">Add Product</button>';
                                    break;
                                case 'E':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="updProd">Edit Product</button>';
                                    break;
                            }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="back">Back</button>
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
    echo '<script>confirmationDialog("","","Product","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
<script>
$(document).ready(function(){
    var prodBarcodeStatus = $("#prod_barcode_status");
    var prodBarcode = $("#prod_barcode_slot, #prod_barcode_slot_lbl");
    var prodBarcodeSlot = $("#prod_barcode_slot");
    var prodCost = $("#prod_cost");
    
    floatInput(prodCost);       // for cost input

    if(prodBarcodeStatus.prop('checked'))
        prodBarcode.show();
    else
        prodBarcode.hide();

    $(prodBarcodeStatus).on('change', () => {
        if(prodBarcodeStatus.prop('checked'))
            prodBarcode.show();
        else
        {
            prodBarcode.hide();
            prodBarcodeSlot.next().remove();
        }
    })

    if(!($("#prod_brand").attr('readonly')))
    {
        $("#prod_brand").keyup(function(){
            var param = {
                search: $(this).val(),                              // search value
                searchType: 'name',                                 // column of the table
                elementID: $(this).attr('id'),                      // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden',    // hidden input for storing the value
                dbTable: '<?= BRAND ?>'                             // json filename (generated when login)
            }
            searchInput(param);
        });
        $("#prod_brand").change(function(){
            if($(this).val() == '')
                $('#'+$(this).attr('id')+'_hidden').val('');
        });
    }

    if(!($("#prod_wgt_unit").attr('readonly')))
    {
        $("#prod_wgt_unit").keyup(function(){
            var param = {
                search: $(this).val(),
                searchType: 'unit',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= WGT_UNIT ?>'
            }
            searchInput(param);
        });
        $("#prod_wgt_unit").change(function(){
            if($(this).val() == '')
                $('#'+$(this).attr('id')+'_hidden').val('');
        });
    }

    if(!($("#prod_cur_unit").attr('readonly')))
    {
        $("#prod_cur_unit").keyup(function(){
            var param = {
                search: $(this).val(),
                searchType: 'unit',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= CUR_UNIT ?>'
            }
            searchInput(param);
        });
        $("#prod_cur_unit").change(function(){
            if($(this).val() == '')
                $('#'+$(this).attr('id')+'_hidden').val('');
        });
    }

    if(!($("#prod_cur_unit").attr('readonly')))
    {
        $("#parent_prod").keyup(function(){
            var param = {
                search: $(this).val(),
                searchType: 'name',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= $tblname ?>'
            }
            searchInput(param);
        });
        $("#parent_prod").change(function(){
            if($(this).val() == '')
                $('#'+$(this).attr('id')+'_hidden').val('');
        });
    }
});

</script>
</html>