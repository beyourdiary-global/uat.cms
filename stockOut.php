<?php
$pageTitle = "Stock Out";
include 'menuHeader.php';

$barcode = input('barcode');
$prod_id = input('pkg_id');
$whse_id = input('whse_id');
$usr_id = input('usr_id');
$redirect_page = 'dashboard.php';  // if no value get
$clearLocalStorage = '<script>localStorage.clear();</script>';
// Check if required parameters are missing and redirect if necessary
if (!$barcode || !$prod_id || !$whse_id || !$usr_id) {
    echo "<script type='text/javascript'>alert('Invalid request for stock-out. Please provide valid data.'); window.location.href ='$SITEURL/dashboard.php';</script>";
    exit; // Terminate further execution
}


// display
// create input and button
$barcode_input = "";
$usr_btn = "";

$rst_prod_info = getData('*', "id='$prod_id'", '', PROD, $connect);
$rst_whse_info = getData('name',"id='$whse_id'",'',WHSE,$connect);
$rst_usr = getData('*',"status='A'",'',USR_USER,$connect);
$rst_prod_info = getData('*', "id='$prod_id'", '', PROD, $connect);

// Get warehouse name
$whse_name = $rst_whse_info ? $rst_whse_info->fetch_assoc()['name'] : '';

// Get product info and generate barcode input fields
$prod_info = $rst_prod_info ? $rst_prod_info->fetch_assoc() : null;
$prod_name = $prod_info ? $prod_info['name'] : '';
$prod_barcode_slot_required = $prod_info ? $prod_info['barcode_status'] === 'Yes' : false;
$prod_barcode_slot_total = $prod_info ? $prod_info['barcode_slot'] : 0;
$prod_brand = $prod_info ? $prod_info['brand'] : '';
$prod_category = $prod_info ? $prod_info['parent_product'] : '';

$tblname = STK_REC;

$result = getData('*', "product_id='$prod_id' AND stock_out_date IS NULL AND stock_out_person_in_charges IS NULL AND stock_out_customer_purchase_id IS NULL", '', $tblname, $connect);

$stock_rec = $result ? $result->fetch_assoc() : null;
$row_count = $result ? mysqli_num_rows($result) : 0;
if ($prod_barcode_slot_required && $prod_barcode_slot_total >= 1) {
    $max_slots = $prod_barcode_slot_total - $row_count;
    for ($x = 1; $x <= $max_slots; $x++) {
        $barcode_input .= "<input class=\"form-control mb-1\" id=\"barcode_input_$x\" name=\"barcode_input[]\" type=\"text\" placeholder=\"Barcode Slot $x\">";
    }
    $order_id = post('order_id');
    if($max_slots == 0 && $order_id){
        $lazada = getData('*', "oder_number='$order_id' ", '',LAZADA_ORDER_REQ, $connect);
        
        $shopee = getData('*',"orderID='$order_id'", "", '',SHOPEE_SG_ORDER_REQ, $finance_connect);
        $fbs = getData('*', "id='$order_id'", '',FB_ORDER_REQ, $finance_connect);
        $web = getData('*', "order_id='$order_id'", '',WEB_ORDER_REQ, $finance_connect);
        $newStatus = 'CP';
        if ($lazada) {
            $query = "UPDATE LAZADA_ORDER_REQ SET order_status = '$newStatus' WHERE oder_number='$order_id";
            $acc_result = $connect->query($query);
         
        }
        
        if ($shopee) {
            $query = "UPDATE SHOPEE_SG_ORDER_REQ SET order_status = '$newStatus' WHERE orderID='$order_id'";
            $acc_result = $finance_connect->query($query);
           
        }
        
        if ($fbs) {
            $query = "UPDATE FB_ORDER_REQ SET order_status = '$newStatus' WHERE id='$order_id'";
            $acc_result = $finance_connect->query($query);
           
        }
        
        if ($web) {
            $query = "UPDATE WEB_ORDER_REQ SET order_status = '$newStatus' WHERE order_id='$order_id'";
            $acc_result = $finance_connect->query($query);
          
        }
    }
}

// Check if database queries fail and redirect if necessary
if (!$rst_prod_info || !$rst_whse_info || !$rst_usr) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.'); window.location.href ='$SITEURL/dashboard.php';</script>";
}

if(!$stock_rec){
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.'); window.location.href ='$SITEURL/dashboard.php';</script>";
}

if ($stock_rec['stock_out_date'] != null && $stock_rec['stock_out_person_in_charges'] != null &&  $stock_rec['stock_out_customer_purchase_id'] != null) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}

// Get user info and generate buttons
while ($usr = $rst_usr->fetch_assoc()) {
    $usr_id = $usr['id'];
    $usr_name = $usr['name'];

    $usr_btn = "<button class=\"btn btn-rounded btn-primary mx-2 my-1 submitBtn\" style=\"color:#FFFFFF;\" name=\"usrBtn\" id=\"actionBtn\" value=\"$usr_id\">Submit</button>";
}

// Submission
if (post('usrBtn')) {
    $usrBtn = post('usrBtn');
    $barcodeInputs = post('barcode_input');
    $orderid = post('order_id');
    if ($barcodeInputs != '') {
        $arrNum = sizeof($barcodeInputs);

        $datafield = $newvalarr = array();
        if ($arrNum >= 1) {
          
            if ($prod_info) {
                $brandId = $prod_info['brand'];
                $productId = $prod_info['id'];
                $productCategoryId = $prod_info['product_category'];
                if (isset($prod_info['brand'])) {
                    array_push($newvalarr, $prod_info['brand']);
                    array_push($datafield, 'brand');
                }
                
                if (isset($prod_info['id'])) {
                    array_push($newvalarr, $prod_info['id']);
                    array_push($datafield, 'product_id');
                }
                
                if (isset($prod_info['product_category'])) {
                    array_push($newvalarr, $prod_info['product_category']);
                    array_push($datafield, 'product_category_id');
                }
                foreach ($barcodeInputs as $batchCode) {
                 
                    $stockInDate = date("Y-m-d");
                    $barcode = input('barcode');
                    $productBatchCode = $batchCode;
                    $productStatusId = 4;
                    $warehouseId = $whse_id;
                    $stockInPersonInCharges = $usrBtn;
                    $remark = "Stock Out";
                    $createDate = date("Y-m-d");
                    $createTime = date("H:i:s");
                    $createBy = $usrBtn;
                    $stockOutDate = date("Y-m-d");
                    $stockOutPersonInCharges = $usrBtn;
                    $stockOutCustomerPurchaseId = 1;
                    $tblname2 = STK_OUT;
                    $insertQuery ="UPDATE $tblname 
                    SET 
                        stock_out_date = '$stockOutDate', 
                        stock_out_person_in_charges = '$stockOutPersonInCharges', 
                        stock_out_customer_purchase_id = '$orderid' 
                    WHERE 
                        brand_id = '$brandId' AND 
                        product_id = '$productId' AND 
                        barcode = '$barcode'";
                        var_dump($insertQuery);
                    $insertQuery2 = "INSERT INTO $tblname2 (brand_id, product_id, product_category, stock_out_date, stock_out_person_in_charges, stock_out_customer_purchase_id, remark, create_date, create_time, create_by) VALUES ('$brandId', '$productId','$productCategoryId','$stockInDate', '$stockInPersonInCharges','$orderid', '$remark', '$createDate', '$createTime', '$createBy')";
                
                    $result = mysqli_query($connect, $insertQuery);
                    $result2 = mysqli_query($connect, $insertQuery2);
                    if ($result) {
                        $logMessage = "$usr_name added a data <b>Stock Out - [ Barcode: $barcode ], [ Product ID: $productId ], [ Warehouse ID: $warehouseId ], [ User ID: $createBy ] </b> under <i><b>$tblname</b></i>." ;
                      

                            $log = [
                                'log_act'      => 'add',
                                'act_msg'      => $logMessage,
                                'cdate'        => $cdate,
                                'ctime'        => $ctime,
                                'uid'          => USER_ID,
                                'cby'          => USER_ID,
                                'query_rec'    => $insertQuery,
                                'query_table'  => $tblname,
                                'page'         => $pageTitle,
                                'connect'      => $connect,
                            ];
                            $log['newval'] = implodeWithComma($newvalarr);
                            audit_log($log);
                        
                       
                        
                        
                        echo "<script>showNotification('Stock Out successful.');</script>";
                        $_SESSION['tempValConfirmBox'] = true;
                    } else {
                        echo "<script>showNotification('Stock Out failed. Please try again.');</script>";
                    }
                }
            }
        }
    }else{
        if ($prod_info) {
            $datafield = $newvalarr = array();
            $brandId = $prod_info['brand'];
            $productId = $prod_info['id'];
            $productCategoryId = $prod_info['product_category'];
            if (isset($prod_info['brand'])) {
                array_push($newvalarr, $prod_info['brand']);
                array_push($datafield, 'brand');
            }
            
            if (isset($prod_info['id'])) {
                array_push($newvalarr, $prod_info['id']);
                array_push($datafield, 'product_id');
            }
            
            if (isset($prod_info['product_category'])) {
                array_push($newvalarr, $prod_info['product_category']);
                array_push($datafield, 'product_category_id');
            }
                
                $stockInDate = date("Y-m-d");
                $order_id = input('order_id');
                $barcode = input('barcode');
                $productBatchCode = null;
                $productStatusId = 4;
                $warehouseId = $whse_id;
                $stockInPersonInCharges = $usrBtn;
                $remark = "Stock Out";
                $createDate = date("Y-m-d");
                $createTime = date("H:i:s");
                $createBy = $usrBtn;
                $tblname2 = STK_OUT;
                $stockOutDate = date("Y-m-d");
                $stockOutPersonInCharges = $usrBtn;
                $stockOutCustomerPurchaseId = 1;
                $insertQuery ="UPDATE $tblname 
                SET 
                    stock_out_date = '$stockOutDate', 
                    stock_out_person_in_charges = '$stockOutPersonInCharges', 
                    stock_out_customer_purchase_id = '$orderid' 
                WHERE 
                    brand_id = '$brandId' AND 
                    product_id = '$productId' AND 
                    barcode = '$order_id'";
                var_dump($insertQuery);
                $insertQuery2 = "INSERT INTO $tblname2 (brand_id, product_id, product_category, stock_out_date, stock_out_person_in_charges, stock_out_customer_purchase_id, remark, create_date, create_time, create_by) VALUES ('$brandId', '$productId', '$stockInDate', '$stockInPersonInCharges','$orderid', '$remark', '$createDate', '$createTime', '$createBy')";
                $result = mysqli_query($connect, $insertQuery);
                $result2 = mysqli_query($connect, $insertQuery2);
                if ($result) {
                    $logMessage = "$usr_name added a data <b>Stock Out - [ Barcode: $barcode ], [ Product ID: $productId ], [ Warehouse ID: $warehouseId ], [ User ID: $createBy ] </b> under <i><b>$tblname</b></i>." ;
                  

                        $log = [
                            'log_act'      => 'add',
                            'act_msg'      => $logMessage,
                            'cdate'        => $cdate,
                            'ctime'        => $ctime,
                            'uid'          => USER_ID,
                            'cby'          => USER_ID,
                            'query_rec'    => $insertQuery,
                            'query_table'  => $tblname,
                            'page'         => $pageTitle,
                            'connect'      => $connect,
                        ];
                        $log['newval'] = implodeWithComma($newvalarr);
                        audit_log($log);
                    
                   
                    
                    
                    echo "<script>showNotification('Stock In successful.');</script>";
                    $_SESSION['tempValConfirmBox'] = true;
                } else {
                    echo "<script>showNotification('Stock In failed. Please try again.');</script>";
                }
            
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   
</head>

<body>
    <div class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6">
            <form id="stockForm" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group my-5">
                            <h3>
                                Stock Out
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                  
                        <div class="col-12 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="order_id_lbl" for="order_id">Order ID</label>
                            <input class="form-control" type="text" name="order_id" id="order_id" value="">
                            <input type="hidden" name="order_id_hidden" id="order_id_hidden" value="<?php 
                                echo isset($shopee['orderID']) ? $shopee['orderID'] : (
                                    isset($fb['id']) ? $fb['id'] : (
                                        isset($lzd['oder_number']) ? $lzd['oder_number'] : (
                                            isset($web['order_id']) ? $web['order_id'] : ''
                                        )
                                    )
                                );
                            ?>">
                        </div>

                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="prod_name">Product</label>
                            <input class="form-control" type="text" name="prod_name" id="prod_name" value="<?php if ($prod_name) echo $prod_name ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="warehouse">Warehouse</label>
                            <input class="form-control" type="text" name="warehouse" id="warehouse" value="<?php if ($whse_name) echo $whse_name ?>" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="barcode">Barcode</label>
                            <input class="form-control" type="text" name="barcode" id="barcode" value="<?php if ($barcode) echo $barcode ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="expire_date">Product Expire Date</label>
                            <input class="form-control" type="date" name="expire_date" id="expire_date">
                        </div>
                    </div>
                </div>
                <?php
                if($barcode_input != ''){
                ?>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="barcode_input">Barcode Slot Input</label>
                            <?= $barcode_input ?>  
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
                <?= $usr_btn ?>
            </form>
        </div>
    </div>
</body>

</html>

<?php
 if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","I");</script>';
}
?>
<script>
    


    $(document).ready(function () {
        var barcode = '<?=$barcode?>';
        var prod_id = '<?=$prod_id?>';
        var whse_id = '<?=$whse_id?>';
        var usr_id = '<?=$usr_id?>';

        if (!barcode || !prod_id || !whse_id || !usr_id) {
            alert('Invalid request for stock-in. Please provide valid data.');
            window.location.href = '<?=$SITEURL?>/dashboard.php';
        }

        var isBarcodeRequired = <?=$prod_barcode_slot_required ? 'true' : 'false'?>;
        if (isBarcodeRequired) {
            showNotification('Barcode is required for this product.');
            <?php for ($x = 1; $x <= $prod_barcode_slot_total; $x++) : ?>
                $("#barcode_input_<?=$x?>").prop('readonly', false);
            <?php endfor; ?>
        }
    });

    <?php include "js/stock_out.js" ?>
</script>
