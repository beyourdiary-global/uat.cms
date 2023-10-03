<?php
$pageTitle = "Stock In";
include 'menuHeader.php';

$barcode = input('barcode');
$pkg_id = input('pkg_id');
$whse_id = input('whse_id');
$redirect_page = 'dashboard.php';  // if no value get
$tblname = STK_REC;

// display
// create input and button
$barcode_input = "";
$usr_btn = "";

$rst_pkg_info = getData('*',"id='$pkg_id'",PKG,$connect);
$rst_whse_info = getData('name',"id='$whse_id'",WHSE,$connect);
$rst_usr = getData('*',"status='A'",USR_USER,$connect);

if($rst_whse_info)
{
    $whse_name = $rst_whse_info->fetch_assoc();
    $whse_name = $whse_name['name'];
} else {
    $whse_name = '';
}

if($rst_pkg_info)
{
    $pkg_info = $rst_pkg_info->fetch_assoc();
    $pkg_name = $pkg_info['name'];
    $pkg_barcode_slot_total = $pkg_info['barcode_slot_total'];

    if($pkg_barcode_slot_total >= 1)
    {
        for($x=1;$x<=$pkg_barcode_slot_total;$x++)
        {
            $barcode_input .= "<input class=\"form-control mb-1\" id=\"barcode_input_$x\" name=\"barcode_input[]\" type=\"text\" placeholder=\"Barcode Slot $x\">";
        }
    }
}

if($rst_usr)
{
    while($usr = $rst_usr->fetch_assoc())
    {
        $usr_id = $usr['id'];
        $usr_name = $usr['name'];

        /* $usr_btn .= "<button class=\"btn btn-lg btn-rounded btn-primary mx-2\" name=\"usrBtn\" id=\"actionBtn\" value=\"$usr_id\">$usr_name</button>"; */
        $usr_btn .= "<button class=\"btn btn-rounded btn-primary mx-2 my-1\" style=\"color:#FFFFFF;\" name=\"usrBtn\" id=\"actionBtn\" value=\"$usr_id\">$usr_name</button>";
    }
}

/* echo $barcode_input;
echo $usr_btn; */

// submission
if(post('usrBtn'))
{
    $usrBtn = post('usrBtn');
    $barcode_input = post('barcode_input');

    if($barcode_input != '')
    {
        $arrNum = sizeOF($barcode_input);
        if($arrNum >= 1)
        {
            foreach($barcode_input as $batch_code)
            {
                $bulkInsert = "INSERT INTO $tblname (product_id,stock_in_date,barcode,product_batch_code,warehouse_id,stock_in_person_in_charges,remark,create_date,create_time,create_by,`status`) VALUES ('')";
            }
        }    
    }
    else
    {

    }
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
        <form id="stockForm" method="post" action="">
            <div class="row">
                <div class="col-12">
                    <div class="form-group my-5">
                        <h3>
                            Stock In
                        </h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="prod_name_lbl" for="prod_name">Package</label>
                        <input class="form-control" type="text" name="pkg_name" id="prod_name" value="<?php if($pkg_name) echo $pkg_name?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="prod_name_lbl" for="prod_name">Warehouse</label>
                        <input class="form-control" type="text" name="pkg_name" id="prod_name" value="<?php if($whse_name) echo $whse_name?>" readonly>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="prod_name_lbl" for="prod_name">Barcode</label>
                        <input class="form-control" type="text" name="pkg_name" id="prod_name" value="<?php if($barcode) echo $barcode?>" readonly>
                    </div>
                </div>
            </div>

            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="prod_name_lbl" for="prod_name">Barcode Slot Input: <?= $pkg_barcode_slot_total ?></label>
                        <?= $barcode_input ?>
                        <div id="err_msg">
                            <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <hr />

            <div class="row">
                <div class="col-12 d-flex justify-content-none justify-content-md-center">
                    <div class="form-group mb-3">
                        <?= $usr_btn ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>

</html>