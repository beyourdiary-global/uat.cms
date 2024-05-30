<?php
$pageTitle = "Shopee SG Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = SHOPEE_SG_ORDER_REQ;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/shopee_order_req_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);

    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } else {
        // If $rst is false or no data found ($act==null)
        $errorExist = 1;
        $_SESSION['tempValConfirmBox'] = true;
        $act = "F";
    }
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $scr_username = $_POST['scr_username']; 
    $scr_pic = $_POST['scr_pic'];
    $scr_country = $_POST['scr_country'];
    $scr_brand = $_POST['scr_brand'];
    $scr_series = $_POST['scr_series'];
    
    $duplicate_check_query = "SELECT * FROM shopee_customer_info WHERE buyer_username = '$scr_username'";
    $duplicate_result = mysqli_query($finance_connect, $duplicate_check_query);
    if( $scr_username != ''){
       
    if (mysqli_num_rows($duplicate_result) > 0) {
        echo "<script>alert('Error: Duplicate Customer ID found!');</script>";
    } else {
        $insert_query = "INSERT INTO shopee_customer_info (buyer_username, pic, country, brand, series) 
                         VALUES ('$scr_username', '$scr_pic', '$scr_country', '$scr_brand', '$scr_series')";
    
        if (mysqli_query($finance_connect, $insert_query)) {
            echo "<script>alert('New record created successfully');</script>";
            generateDBData(SHOPEE_CUST_INFO, $finance_connect);
        } else {
            echo "<script>alert('Error: " . $insert_query . "<br>" . mysqli_error($connect) . "');</script>";
        }
    }
    }
}
if (post('actionBtn')) {
    $action = post('actionBtn');

    $sor_acc = postSpaceFilter('sor_acc');
    $sor_curr = postSpaceFilter('sor_curr_hidden');
    $sor_order = postSpaceFilter('sor_order');
    $sor_date = postSpaceFilter('sor_date');
    $sor_time = postSpaceFilter('sor_time');
    $sor_pkg = postSpaceFilter('sor_pkg_hidden');
    $sor_brand = postSpaceFilter('sor_brand_hidden');
    $sor_user = postSpaceFilter('sor_user_hidden');
    $sor_pay = postSpaceFilter('sor_pay');
    $sor_pic = postSpaceFilter('sor_pic_hidden');
    $sor_price = postSpaceFilter('sor_price');
    $sor_voucher = postSpaceFilter('sor_voucher');
    $sor_shipping = postSpaceFilter('sor_shipping');
    $sor_serv = postSpaceFilter('sor_serv');
    $sor_trans = postSpaceFilter('sor_trans');
    $sor_ams = postSpaceFilter('sor_ams');
    $sor_fees = postSpaceFilter('sor_fees');
    $sor_final = postSpaceFilter('sor_final');
    $sor_remark = postSpaceFilter('sor_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':
        case 'updRecord':
            if (!$sor_acc) {
                $acc_err = "Shopee Account cannot be empty.";
                $error = 1;
            }
            if (!$sor_curr) {
                $curr_err = "Currency cannot be empty.";
                $error = 1;
            }
            if (!$sor_order) {
                $order_err = "Order ID cannot be empty.";
                $error = 1;
            }
            if (!$sor_date) {
                $date_err = "Date cannot be empty.";
                $error = 1;
            }
            if (!$sor_time) {
                $time_err = "Time cannot be empty.";
                $error = 1;
            }
            if (!$sor_pkg) {
                $pkg_err = "Package cannot be empty.";
                $error = 1;
            }
            if (!$sor_brand) {
                $brand_err = "Brand cannot be empty.";
                $error = 1;
            }
            if (!$sor_user) {
                $user_err = "Shopee Buyer Username cannot be empty.";
                $error = 1;
            }
            if (!$sor_pay) {
                $pay_err = "Buyer Payment Method cannot be empty.";
                $error = 1;
            }
            if (!$sor_pic) {
                $pic_err = "Person In Charge cannot be empty.";
                $error = 1;
            }
            if (!$sor_price) {
                $price_err = "Product Price cannot be empty.";
                $error = 1;
            }
            if (isset($error)) {
                break;
            }
            if ($action == 'addRecord') {
                try {
                    //check values
                    if ($sor_acc) {
                        array_push($newvalarr, $sor_acc);
                        array_push($datafield, 'shopee account');
                    }
                    if ($sor_curr) {
                        array_push($newvalarr, $sor_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($sor_order) {
                        array_push($newvalarr, $sor_order);
                        array_push($datafield, 'order ID');
                    }

                    if ($sor_date) {
                        array_push($newvalarr, $sor_date);
                        array_push($datafield, 'date');
                    }

                    if ($sor_time) {
                        array_push($newvalarr, $sor_time);
                        array_push($datafield, 'time');
                    }

                    if ($sor_pkg) {
                        array_push($newvalarr, $sor_pkg);
                        array_push($datafield, 'package');
                    }

                    if ($sor_brand) {
                        array_push($newvalarr, $sor_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($sor_user) {
                        array_push($newvalarr, $sor_user);
                        array_push($datafield, 'buyer username');
                    }

                    if ($sor_pay) {
                        array_push($newvalarr, $sor_pay);
                        array_push($datafield, 'buyer payment method');
                    }

                    if ($sor_pic) {
                        array_push($newvalarr, $sor_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($sor_price) {
                        array_push($newvalarr, $sor_price);
                        array_push($datafield, 'price');
                    }

                    if ($sor_voucher) {
                        array_push($newvalarr, $sor_voucher);
                        array_push($datafield, 'voucher');
                    }

                    if ($sor_shipping) {
                        array_push($newvalarr, $sor_shipping);
                        array_push($datafield, 'actual shipping');
                    }

                    if ($sor_serv) {
                        array_push($newvalarr, $sor_serv);
                        array_push($datafield, 'service fee');
                    }

                    if ($sor_trans) {
                        array_push($newvalarr, $sor_trans);
                        array_push($datafield, 'transaction fee');
                    }

                    if ($sor_ams) {
                        array_push($newvalarr, $sor_ams);
                        array_push($datafield, 'AMS fee');
                    }

                    if ($sor_fees) {
                        array_push($newvalarr, $sor_fees);
                        array_push($datafield, 'fees and charges');
                    }

                    if ($sor_final) {
                        array_push($newvalarr, $sor_final);
                        array_push($datafield, 'final amount');
                    }

                    if ($sor_remark) {
                        array_push($newvalarr, $sor_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . " (shopee_acc,currency,orderID,date,time,package,brand,buyer,buyer_pay_meth,pic,price,voucher,act_shipping_fee,service_fee,trans_fee,ams_fee,fees,final_amt,remark,create_by,create_date,create_time) VALUES ('$sor_acc','$sor_curr','$sor_order','$sor_date','$sor_time','$sor_pkg','$sor_brand','$sor_user','$sor_pay','$sor_pic','$sor_price','$sor_voucher','$sor_shipping','$sor_serv','$sor_trans','$sor_ams','$sor_fees','$sor_final','$sor_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['shopee_acc'] != $sor_acc) {
                        array_push($oldvalarr, $row['shopee_acc']);
                        array_push($chgvalarr, $sor_acc);
                        array_push($datafield, 'shopee_acc');
                    }
                    if ($row['currency'] != $sor_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $sor_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($row['orderID'] != $sor_order) {
                        array_push($oldvalarr, $row['orderID']);
                        array_push($chgvalarr, $sor_order);
                        array_push($datafield, 'orderID');
                    }

                    if ($row['date'] != $sor_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $sor_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['time'] != $sor_time) {
                        array_push($oldvalarr, $row['time']);
                        array_push($chgvalarr, $sor_time);
                        array_push($datafield, 'time');
                    }

                    if ($row['package'] != $sor_pkg) {
                        array_push($oldvalarr, $row['package']);
                        array_push($chgvalarr, $sor_pkg);
                        array_push($datafield, 'package');
                    }

                    if ($row['brand'] != $sor_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $sor_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['buyer'] != $sor_user) {
                        array_push($oldvalarr, $row['buyer']);
                        array_push($chgvalarr, $sor_user);
                        array_push($datafield, 'buyer');
                    }

                    if ($row['buyer_pay_meth'] != $sor_pay) {
                        array_push($oldvalarr, $row['buyer_pay_meth']);
                        array_push($chgvalarr, $sor_pay);
                        array_push($datafield, 'buyer_pay_meth');
                    }

                    if ($row['pic'] != $sor_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $sor_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['price'] != $sor_price) {
                        array_push($oldvalarr, $row['price']);
                        array_push($chgvalarr, $sor_price);
                        array_push($datafield, 'price');
                    }

                    if ($row['voucher'] != $sor_voucher) {
                        array_push($oldvalarr, $row['voucher']);
                        array_push($chgvalarr, $sor_voucher);
                        array_push($datafield, 'voucher');
                    }

                    if ($row['act_shipping_fee'] != $sor_shipping) {
                        array_push($oldvalarr, $row['act_shipping_fee']);
                        array_push($chgvalarr, $sor_shipping);
                        array_push($datafield, 'act_shipping_fee');
                    }

                    if ($row['service_fee'] != $sor_serv) {
                        array_push($oldvalarr, $row['service_fee']);
                        array_push($chgvalarr, $sor_serv);
                        array_push($datafield, 'service fee');
                    }

                    if ($row['trans_fee'] != $sor_trans) {
                        array_push($oldvalarr, $row['trans_fee']);
                        array_push($chgvalarr, $sor_trans);
                        array_push($datafield, 'transaction fee');
                    }

                    if ($row['ams_fee'] != $sor_ams) {
                        array_push($oldvalarr, $row['ams_fee']);
                        array_push($chgvalarr, $sor_ams);
                        array_push($datafield, 'ams_fee');
                    }

                    if ($row['fees'] != $sor_fees) {
                        array_push($oldvalarr, $row['fees']);
                        array_push($chgvalarr, $sor_fees);
                        array_push($datafield, 'fees n charges');
                    }

                    if ($row['final_amt'] != $sor_final) {
                        array_push($oldvalarr, $row['final_amt']);
                        array_push($chgvalarr, $sor_final);
                        array_push($datafield, 'final amount');
                    }

                    if ($row['remark'] != $sor_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $sor_remark == '' ? 'Empty Value' : $sor_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET ";
                        $query .= "shopee_acc = '$sor_acc', ";
                        $query .= "currency = '$sor_curr', ";
                        $query .= "orderID = '$sor_order', ";
                        $query .= "date = '$sor_date', ";
                        $query .= "time = '$sor_time', ";
                        $query .= "package = '$sor_pkg', ";
                        $query .= "brand = '$sor_brand', ";
                        $query .= "buyer = '$sor_user', ";
                        $query .= "buyer_pay_meth = '$sor_pay', ";
                        $query .= "pic = '$sor_pic', ";
                        $query .= "price = '$sor_price', ";
                        $query .= "voucher = '$sor_voucher', ";
                        $query .= "act_shipping_fee = '$sor_shipping', ";
                        $query .= "service_fee = '$sor_serv', ";
                        $query .= "trans_fee = '$sor_trans', ";
                        $query .= "ams_fee = '$sor_ams', ";
                        $query .= "fees = '$sor_fees', ";
                        $query .= "final_amt = '$sor_final', ";
                        $query .= "remark = '$sor_remark', ";
                        $query .= "update_by = '" . USER_ID . "', ";
                        $query .= "update_date = curdate(), ";
                        $query .= "update_time = curtime() ";
                        $query .= "WHERE id = '$dataID'"; // Specify your condition here

                        $returnData = mysqli_query($finance_connect, $query);

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


if (post('act') == 'D') {
    $id = post('id');
    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];

            //SET the record status to 'D'
            deleteRecord($tblName, '', $dataID, $sor_name, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
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
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">

</head>

<body>
    <!-- <div class="pre-load-center">
        <div class="preloader"></div>
    </div> -->
    <!-- <div class="page-load-cover"> -->
    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>">
                <?= $pageTitle ?>
            </a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php
            echo displayPageAction($act, $pageTitle);
            ?>
        </p>

    </div>

    <div id="formContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="FORForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, $pageTitle);
                        ?>
                     
                     <?php if ($act == 'E'): ?>
                        <?php
                         $status = $row['order_status'];
                         if ($status == 'P') {
                             $status = 'Processing';
                         }else  if ($status == 'SP') {
                             $status = 'Shipped';
                         }else  if ($status == 'WP') {
                             $status = 'Waiting Packing';
                         }
                        ?>
                        <span style="float: right;" id="order-status">Order Status: <?php echo $status; ?></span>
                        
                    <?php endif; ?>
               
                    </h2>
                  
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;">
                        <?php if (isset($err1))
                            echo $err1; ?>
                    </span>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-md mb-3">
                            <label class="form-label form_lbl" id="sor_acc_label" for="sor_acc">Shopee Account
                                <span class="requireRed">*</span></label>
                            <select class="form-select" id="sor_acc" name="sor_acc" <?php if ($act == '')
                                echo 'disabled' ?>>
                                    <option value="0" disabled selected>Select Shopee Account</option>
                                    <?php
                            $query = "SELECT * FROM " . SHOPEE_ACC . " WHERE `status` = 'A' ORDER BY `name` ASC";
                            $acc_result = $finance_connect->query($query);
                            if ($acc_result->num_rows >= 1) {
                                $acc_result->data_seek(0);
                                while ($row3 = $acc_result->fetch_assoc()) {
                                    $selected = "";
                                    if (isset($dataExisted, $row['shopee_acc']) && !isset($sor_acc)) {
                                        $selected = $row['shopee_acc'] == $row3['id'] ? " selected" : "";
                                    } else if (isset($sor_acc)) {
                                        $selected = $sor_acc == $row3['id'] ? " selected" : "";
                                    }
                                    echo "<option value=\"" . $row3['id'] . "\"$selected>" . $row3['name'] . "</option>";
                                }
                            } else {
                                echo "<option value=\"0\">None</option>";
                            }

                            ?>
                            </select>
                            <?php if (isset($acc_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $acc_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="sor_curr_lbl" for="sor_curr">Currency<span
                                    class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);
                            if (isset($row['currency']))
                                $echoVal = $row['currency'];

                            if (isset($echoVal)) {
                                $curr_rst = getData('*', "id = '$echoVal'", '', CUR_UNIT, $connect);
                                if (!$curr_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $curr_row = $curr_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="sor_curr" id="sor_curr" disabled value="<?php echo !empty($echoVal) ? $curr_row['unit'] : '' ?>">
                            <input type="hidden" name="sor_curr_hidden" id="sor_curr_hidden" value="<?php echo (isset($row['currency'])) ? $row['currency'] : ''; ?>">
                            <?php if (isset($curr_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $curr_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="sor_order_lbl" for="sor_order">Order ID<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="sor_order" id="sor_order" value="<?php
                            if (isset($dataExisted) && isset($row['orderID']) && !isset($sor_order)) {
                                echo $row['orderID'];
                            } else if (isset($sor_order)) {
                                echo $sor_order;
                            }
                            ?>" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($order_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $order_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md mb-3">
                            <label class="form-label form_lbl" id="sor_date_label" for="sor_date">Date<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="sor_date" id="sor_date" value="<?php
                            if (isset($dataExisted) && isset($row['date']) && !isset($sor_date)) {
                                echo $row['date'];
                            } else if (isset($sor_date)) {
                                echo $sor_date;
                            } else {
                                echo date('Y-m-d');
                            }
                            ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($date_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $date_err; ?>
                                    </span>
                                </div>
                            <?php } ?>

                        </div>
                        <div class="col-md mb-3">
                            <label class="form-label form_lbl" id="sor_time_label" for="sor_time">Time<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="time" name="sor_time" id="sor_time" value="<?php
                            if (isset($dataExisted) && isset($row['time']) && !isset($sor_time)) {
                                echo $row['time'];
                            } else if (isset($sor_time)) {
                                echo $sor_time;
                            } else {
                                echo date('H:i');
                            }
                            ?>" placeholder="HH:MM" pattern="[0-9]{2}:[0-9]{2}" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($time_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $time_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="sor_pkg_lbl" for="sor_pkg">Package<span
                                    class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);
                            if (isset($row['package']))
                                $echoVal = $row['package'];

                            if (isset($echoVal)) {
                                $pkg_rst = getData('*', "id = '$echoVal'", '', PKG, $connect);
                                if (!$pkg_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $pkg_row = $pkg_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="sor_pkg" id="sor_pkg" <?php if ($act == '')
                                echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $pkg_row['name'] : '' ?>">
                            <input type="hidden" name="sor_pkg_hidden" id="sor_pkg_hidden"
                                value="<?php echo (isset($row['package'])) ? $row['package'] : ''; ?>">
                            <?php if (isset($pkg_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $pkg_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="sor_brand_lbl" for="sor_brand">Brand<span
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
                            <input class="form-control" type="text" name="sor_brand" id="sor_brand" <?php if ($act == '')
                                echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
                            <input type="hidden" name="sor_brand_hidden" id="sor_brand_hidden"
                                value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">


                            <?php if (isset($brand_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $brand_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="sor_user_lbl" for="sor_user">Shopee Buyer
                                Username<span class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);
                            if (isset($row['buyer']))
                                $echoVal = $row['buyer'];

                            if (isset($echoVal)) {
                                $user_rst = getData('*', "id = '$echoVal'", '', SHOPEE_CUST_INFO, $finance_connect);
                                if (!$user_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $user_row = $user_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="sor_user" id="sor_user" <?php if ($act == '')
                                echo 'disabled' ?>
                                    value="<?php echo !empty($echoVal) ? $user_row['buyer_username'] : '' ?>">
                            <input type="hidden" name="sor_user_hidden" id="sor_user_hidden"
                                value="<?php echo (isset($row['buyer'])) ? $row['buyer'] : ''; ?>">
                            <?php if (isset($user_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $user_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md mb-3">
                            <label class="form-label form_lbl" id="sor_pay_label" for="sor_pay">Buyer Payment Method
                                <span class="requireRed">*</span></label>
                            <select class="form-select" id="sor_pay" name="sor_pay" <?php if ($act == '')
                                echo 'disabled' ?>>
                                    <option value="0" disabled selected>Select Payment Method</option>
                                    <?php
                            $query = "SELECT * FROM " . PAY_MTHD_SHOPEE . " ORDER BY `name` ASC ";
                            $acc_result = $finance_connect->query($query);
                            if ($acc_result->num_rows >= 1) {
                                $acc_result->data_seek(0);
                                while ($row4 = $acc_result->fetch_assoc()) {
                                    $selected = "";
                                    if (isset($dataExisted, $row['buyer_pay_meth']) && !isset($sor_pay)) {
                                        $selected = $row['buyer_pay_meth'] == $row4['id'] ? " selected" : "";
                                    } else if (isset($sor_pay)) {
                                        $selected = $sor_pay == $row4['id'] ? " selected" : "";
                                    }
                                    echo "<option value=\"" . $row4['id'] . "\"$selected>" . $row4['name'] . "</option>";
                                }
                            } else {
                                echo "<option value=\"0\">None</option>";
                            }

                            ?>
                            </select>
                            <?php if (isset($pay_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $pay_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php if ($act != ''){ ?>
                <div class="col-md-4 mb-3">
                    <button type="button" onclick="toggleNewBuyer()">Create New Customer ID</button>
                </div>
                <form id="myForm" method="POST">
                <div id="new_customer_section" style="display: none;">

                <div class="row">
                    <div class="col-md-4 mb-3 autocomplete">
                        <label class="form-label form_lbl" for="scr_username">Shopee Buyer Username<span class="requireRed">*</span></label>
                        <input class="form-control" type="text" id="scr_username" name="scr_username">
                    </div>

                    <div class="col-md-4 mb-3 autocomplete">
                        <label class="form-label form_lbl" for="scr_pic">Sales Person In Charge<span class="requireRed">*</span></label>
                        <input class="form-control" type="text" id="scr_pic" name="scr_pic">
                    </div>

                    <div class="col-md-4 mb-3 autocomplete">
                        <label class="form-label form_lbl" for="scr_country">Country<span class="requireRed">*</span></label>
                        <input class="form-control" type="text" id="scr_country" name="scr_country">
                    </div>
                    <div class="col-md-4 mb-3 autocomplete">
                        <label class="form-label form_lbl" for="scr_brand">Brand<span class="requireRed">*</span></label>
                        <input class="form-control" type="text" id="scr_brand" name="scr_brand">
                    </div>

                    <div class="col-md-4 mb-3 autocomplete">
                        <label class="form-label form_lbl" for="scr_series">Series<span class="requireRed">*</span></label>
                        <input class="form-control" type="text" id="scr_series" name="scr_series">
                    </div>
                </div>
                <input type="submit" name="submit" value="Submit">
                    </form>
                </div>
                <?php }?>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="sor_pic_lbl" for="sor_pic">Person In
                                Charge<span class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);

                            if (isset($row['pic']))
                                $echoVal = $row['pic'];

                            if (isset($echoVal)) {
                                $user_rst = getData('name', "id = '$echoVal'", '', USR_USER, $connect);
                                if (!$user_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $user_row = $user_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="sor_pic" id="sor_pic" <?php if ($act == '')
                                echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : '' ?>">
                            <input type="hidden" name="sor_pic_hidden" id="sor_pic_hidden"
                                value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">


                            <?php if (isset($pic_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $pic_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="sor_price_lbl" for="sor_price">Price<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="number" step="0.01" name="sor_price" id="sor_price" value="<?php
                            if (isset($dataExisted) && isset($row['price']) && !isset($sor_price)) {
                                echo $row['price'];
                            } else if (isset($sor_price)) {
                                echo $sor_price;
                            }
                            ?>" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($price_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $price_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="sor_voucher_lbl" for="sor_voucher">Voucher </label>
                            <input class="form-control" type="number" step="1" name="sor_voucher" id="sor_voucher"
                                value="<?php
                                if (isset($dataExisted) && isset($row['voucher']) && !isset($sor_voucher)) {
                                    echo $row['voucher'];
                                } else if (isset($sor_voucher)) {
                                    echo $sor_voucher;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                            <?php if (isset($voucher_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $voucher_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="sor_shipping_lbl" for="sor_shipping">Actual Shipping
                            </label>
                            <input class="form-control" type="number" step="0.01" name="sor_shipping" id="sor_shipping"
                                value="<?php
                                if (isset($dataExisted) && isset($row['act_shipping_fee']) && !isset($sor_shipping)) {
                                    echo $row['act_shipping_fee'];
                                } else if (isset($sor_shipping)) {
                                    echo $sor_shipping;
                                } else {
                                    echo '0';
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                            <?php if (isset($shipping_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $shipping_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="form-group mb-4">
                    <h3>
                        Commission Fees
                    </h3>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_serv_lbl" for="sor_serv">Service Fee
                                (incl. GST)</label>
                            <input class="form-control" type="number" step="0.01" name="sor_serv" id="sor_serv" value="<?php
                            if (isset($dataExisted) && isset($row['service_fee']) && !isset($sor_serv)) {
                                echo $row['service_fee'];
                            } else if (isset($sor_serv)) {
                                echo $sor_serv;
                            } else {
                                echo '0';
                            }
                            ?>" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($service_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $service_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_trans_lbl" for="sor_trans">Transaction Fee
                                (incl. GST)</label>
                            <input class="form-control" type="number" step="0.01" name="sor_trans" id="sor_trans" value="<?php
                            if (isset($dataExisted) && isset($row['trans_fee']) && !isset($sor_trans)) {
                                echo $row['trans_fee'];
                            } else if (isset($sor_trans)) {
                                echo $sor_trans;
                            } else {
                                echo '0';
                            }
                            ?>" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($trans_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $trans_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_ams_lbl" for="sor_ams">AMS Commission
                                Fee</label>
                            <input class="form-control" type="number" step="0.01" name="sor_ams" id="sor_ams" value="<?php
                            if (isset($dataExisted) && isset($row['ams_fee']) && !isset($sor_ams)) {
                                echo $row['ams_fee'];
                            } else if (isset($sor_ams)) {
                                echo $sor_ams;
                            } else {
                                echo '0';
                            }
                            ?>" <?php if ($act == '')
                                echo 'disabled' ?>>
                            <?php if (isset($ams_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $ams_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                <hr />
                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md mb-3">
                            <label class="form-label form_lbl" id="sor_fees_lbl" for="sor_fees">Charges &
                                Fees</label>
                            <input class="form-control" type="number" step="0.01" name="sor_fees" id="sor_fees" value="<?php
                            if (isset($dataExisted) && isset($row['fees']) && !isset($sor_fees)) {
                                echo $row['fees'];
                            } else if (isset($sor_fees)) {
                                echo $sor_fees;
                            } else {
                                echo '0';
                            }
                            ?>" readonly>
                            <?php if (isset($fees_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $fees_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md mb-3">
                            <label class="form-label form_lbl" id="sor_final_lbl" for="sor_final">Final
                                Amount</label>
                            <input class="form-control" type="number" step="0.01" name="sor_final" id="sor_final" value="<?php
                            if (isset($dataExisted) && isset($row['final_amt']) && !isset($sor_final)) {
                                echo $row['final_amt'];
                            } else if (isset($sor_final)) {
                                echo $sor_final;
                            } else {
                                echo '0';
                            }
                            ?>">
                            <?php if (isset($final_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $final_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="sor_remark_lbl" for="sor_remark">Remark</label>
                    <textarea class="form-control" name="sor_remark" id="sor_remark" rows="3" <?php if ($act == '')
                        echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark']))
                        echo $row['remark'] ?></textarea>
                    </div>
                    <?php
                    if(isset($row['order_status'])){
                    if($row['order_status'] == 'SP'){
                    ?>
                <div class="form-group mb-4">
                    <h3>
                        Tracking Details
                    </h3>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_courier_lbl" for="sor_courier">Courier</label>
                            <?php
                           
                            if (isset($row['orderID']))
                            $echoVal = $row['orderID'];
                            $courier_rst2 = getData('courier_id', "order_id = '$echoVal'", '', OFFICIAL_PROCESS_ORDER, $connect);

                            if (!$courier_rst2) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $courier_row2 = $courier_rst2->fetch_assoc();
                            if ($courier_row2['courier_id'])
                            $echoVal2 = $courier_row2['courier_id'];
                       
                            $courier_rst = getData('name', "id = '$echoVal2'", '', COURIER, $connect);
                            $courier_row = $courier_rst->fetch_assoc();
                      
                            if (isset($courier_row['name'])) {
                                $courier_name = $courier_row['name'];
                            } else {
                                $courier_name = '';
                            }
                            ?>
                            <input class="form-control" type="text" name="sor_courier" id="sor_courier" value="<?php echo !empty($echoVal2) ? $courier_name : ''; ?>" disabled ?>

                            <?php if (isset($courier_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $courier_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_track_lbl" for="sor_track">Tracking Number</label>
                            
                            <?php
                             $tracking_rst = getData('tracking_id', "order_id = '$echoVal'", '', OFFICIAL_PROCESS_ORDER, $connect);
                             if (!$tracking_rst) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $tracking_row = $tracking_rst->fetch_assoc();
                            if (isset($tracking_row['tracking_id'])) {
                                $tracking_id = $tracking_row['tracking_id'];
                            } else {
                                $tracking_id = '';
                            }
                             ?>
                             <input class="form-control" type="text"  name="sor_track" id="sor_track" value="<?php echo !empty($echoVal) ? $tracking_id : ''; ?>" disabled ?>
                            <?php if (isset($tracking_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $tracking_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-4 d-flex align-items-end">
                            <label>&nbsp;</label><br>
                            <?php
                   
                            $tracking_rst2 = getData('tracking_link', "id = '$echoVal2'", '', COURIER, $connect);
                            if (!$tracking_rst2) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $track_row = $tracking_rst2->fetch_assoc();
                      
                            if (isset($track_row['tracking_link'])) {
                                $tracking_link = $track_row['tracking_link'];
                                
                            } else {
                                $tracking_link = '';
                            }
                            ?>
                            
                            <a href="<?php echo $tracking_link; ?>" id="trackOrderBtn" class="track-order-btn" data-tracking-id="<?php echo $tracking_id; ?>" >Track Order</a>
                            
                        </div>
                    </div>
                </div>
                <?php }} ?>
                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addRecord">Add Record</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updRecord">Edit Record</button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn"
                        value="back">Back</button>
                </div>
            </form>
        </div>
    </div>
    <!-- </div> -->

    <?php
    
    /*
        oufei 20231014
        common.fun.js
        function(title, subtitle, page name, ajax url path, redirect path, action)
        to show action dialog after finish certain action (eg. edit)
    */
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
    $(document).ready(function() {
    $('#myForm').on('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting the traditional way

        var formData = $(this).serialize(); // Serialize the form data

        $.ajax({
            url: 'shopee_order_req.php', // The URL to your PHP script
            type: 'post',
            data: formData,
            success: function(response) {
                var responseObject = JSON.parse(response);
            },
            error: function(xhr, status, error) {
                $('#responseMessage').html('<p>An error occurred: ' + error + '</p>');
            }
        });
    });
});


        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);

        <?php
        include "../js/shopee_order_req.js"
            ?>
    </script>

</body>

</html>