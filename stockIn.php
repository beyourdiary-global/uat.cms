<?php
$pageTitle = "Stock In";
include 'menuHeader.php';

$barcode = input('barcode');
$prod_id = input('prdid');
$whse_id = input('whseid');
$usr_id = input('usr_id');
$redirect_page = 'dashboard.php';  // if no value get

// Check if required parameters are missing and redirect if necessary
if (!$barcode || !$prod_id || !$whse_id || !$usr_id) {
    echo "<script type='text/javascript'>alert('Invalid request for stock-in. Please provide valid data.'); window.location.href ='$SITEURL/dashboard.php';</script>";
    exit; // Terminate further execution
}

$tblname = STK_REC;

// display
// create input and button
$barcode_input = "";
$usr_btn = "";

$rst_prod_info = getData('*', "id='$prod_id'", '', PRODUCT, $connect);
$rst_whse_info = getData('name',"id='$whse_id'",'',WHSE,$connect);
$rst_usr = getData('*',"status='A'",'',USR_USER,$connect);


// Check if database queries fail and redirect if necessary
if (!$rst_prod_info || !$rst_whse_info || !$rst_usr) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.'); window.location.href ='$SITEURL/dashboard.php';</script>";
}

// Get warehouse name
$whse_name = $rst_whse_info ? $rst_whse_info->fetch_assoc()['name'] : '';

// Get product info and generate barcode input fields
$prod_info = $rst_prod_info ? $rst_prod_info->fetch_assoc() : null;
$prod_name = $prod_info ? $prod_info['name'] : '';
$prod_barcode_slot_required = $prod_info ? $prod_info['barcode_required'] === 'yes' : false;
$prod_barcode_slot_total = $prod_info ? $prod_info['barcode_slot_total'] : 0;

if ($prod_barcode_slot_required && $prod_barcode_slot_total >= 1) {
    for ($x = 1; $x <= $prod_barcode_slot_total; $x++) {
        $barcode_input .= "<input class=\"form-control mb-1\" id=\"barcode_input_$x\" name=\"barcode_input[]\" type=\"text\" placeholder=\"Barcode Slot $x\">";
    }
}

// Get user info and generate buttons
while ($usr = $rst_usr->fetch_assoc()) {
    $usr_id = $usr['id'];
    $usr_name = $usr['name'];

    $usr_btn .= "<button class=\"btn btn-rounded btn-primary mx-2 my-1\" style=\"color:#FFFFFF;\" name=\"usrBtn\" id=\"actionBtn\" value=\"$usr_id\">$usr_name</button>";
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