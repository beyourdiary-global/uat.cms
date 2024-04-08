<?php
$pageTitle = "Lazada Order Request";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';

$tblName = LAZADA_ORDER_REQ;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


$redirect_page = $SITEURL . '/lazada_order_req_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $connect);

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

$pay_meth_list_result = getData('*', '', '', FIN_PAY_METH, $finance_connect);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $shipping_name = $_POST['shipping_name'];
    $shipping_address = $_POST['shipping_address'];
    $shipping_contact = $_POST['shipping_contact'];
    $sales_pic = $_POST['sales_pic'];
    
    $duplicate_check_query = "SELECT * FROM customer_lazada_deals_transaction WHERE lcr_id = '$customer_id'";
    $duplicate_result = mysqli_query($connect, $duplicate_check_query);
    
    if (mysqli_num_rows($duplicate_result) > 0) {
        echo "<script>alert('Error: Duplicate record found!');</script>";
    } else {
        $insert_query = "INSERT INTO customer_lazada_deals_transaction (lcr_id, name, email, phone, ship_rec_name, ship_rec_add, ship_rec_contact, sales_pic) 
                         VALUES ('$customer_id', '$customer_name', '$customer_email', '$customer_phone', '$shipping_name', '$shipping_address', '$shipping_contact', '$sales_pic')";
    
        if (mysqli_query($connect, $insert_query)) {
            echo "<script>alert('New record created successfully');</script>";
        } else {
            echo "<script>alert('Error: " . $insert_query . "<br>" . mysqli_error($connect) . "');</script>";
        }
    }
}


if (post('actionBtn')) {
    $action = post('actionBtn');

    $lor_lazada_acc = postSpaceFilter('lor_lazada_acc_hidden');
    $lor_curr_unit = postSpaceFilter('lor_curr_unit_hidden');
    $lor_lzd_country = postSpaceFilter('lor_lzd_country_hidden');
    $lor_cust_id = postSpaceFilter('lor_cust_id_hidden');
    $lor_cust_name = postSpaceFilter('lor_cust_name');
    $lor_cust_email = postSpaceFilter('lor_cust_email');
    $lor_cust_phone = postSpaceFilter('lor_cust_phone');
    $lor_country = postSpaceFilter('lor_country_hidden');
    $lor_oder_number = postSpaceFilter('lor_oder_number');
    $lor_sales_pic = postSpaceFilter('lor_sales_pic');
    $lor_ship_rec_name = postSpaceFilter('lor_ship_rec_name');
    $lor_ship_rec_address = postSpaceFilter('lor_ship_rec_address');
    $lor_ship_rec_contact = postSpaceFilter('lor_ship_rec_contact');
    $lor_brand = postSpaceFilter('lor_brand_hidden');
    $lor_series = postSpaceFilter('lor_series_hidden');
    $lor_pkg = postSpaceFilter('lor_pkg_hidden');
    $lor_item_price_credit = postSpaceFilter('lor_item_price_credit');
    $lor_commision = postSpaceFilter('lor_commision');
    $lor_other_discount = postSpaceFilter('lor_other_discount');
    $lor_pay_fee = postSpaceFilter('lor_pay_fee');
    $lor_final_income = postSpaceFilter('lor_final_income');
    $lor_pay_meth = postSpaceFilter('lor_pay_meth');
    $lor_remark = postSpaceFilter('lor_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRequest':
        case 'updRequest':

            if ($lor_cust_email && !isEmail($lor_cust_email)) {
                $cust_email_err = "Wrong email format!";
                $error = 1;
                break;

            } else if ($action == 'addRequest') {
                try {
                    //check values
                    if ($lor_lazada_acc) {
                        array_push($newvalarr, $lor_lazada_acc);
                        array_push($datafield, 'lazada_acc');
                    }

                    if ($lor_curr_unit) {
                        array_push($newvalarr, $lor_curr_unit);
                        array_push($datafield, 'curr_unit');
                    }

                    if ($lor_lzd_country) {
                        array_push($newvalarr, $lor_lzd_country);
                        array_push($datafield, 'lzd_country');
                    }

                    if ($lor_cust_id) {
                        array_push($newvalarr, $lor_cust_id);
                        array_push($datafield, 'cust_id');
                    }

                    if ($lor_cust_name) {
                        array_push($newvalarr, $lor_cust_name);
                        array_push($datafield, 'cust_name');
                    }

                    if ($lor_cust_email) {
                        array_push($newvalarr, $lor_cust_email);
                        array_push($datafield, 'cust_email');
                    }

                    if ($lor_cust_phone) {
                        array_push($newvalarr, $lor_cust_phone);
                        array_push($datafield, 'cust_phone');
                    }

                    if ($lor_country) {
                        array_push($newvalarr, $lor_country);
                        array_push($datafield, 'country');
                    }

                    if ($lor_oder_number) {
                        array_push($newvalarr, $lor_oder_number);
                        array_push($datafield, 'oder_number');
                    }

                    if ($lor_sales_pic) {
                        array_push($newvalarr, $lor_sales_pic);
                        array_push($datafield, 'sales_pic');
                    }

                    if ($lor_ship_rec_name) {
                        array_push($newvalarr, $lor_ship_rec_name);
                        array_push($datafield, 'ship_rec_name');
                    }

                    if ($lor_ship_rec_address) {
                        array_push($newvalarr, $lor_ship_rec_address);
                        array_push($datafield, 'ship_rec_address');
                    }

                    if ($lor_ship_rec_contact) {
                        array_push($newvalarr, $lor_ship_rec_contact);
                        array_push($datafield, 'ship_rec_contact');
                    }

                    if ($lor_brand) {
                        array_push($newvalarr, $lor_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($lor_series) {
                        array_push($newvalarr, $lor_series);
                        array_push($datafield, 'series');
                    }

                    if ($lor_pkg) {
                        array_push($newvalarr, $lor_pkg);
                        array_push($datafield, 'pkg');
                    }

                    if ($lor_item_price_credit) {
                        array_push($newvalarr, $lor_item_price_credit);
                        array_push($datafield, 'item_price_credit');
                    }

                    if ($lor_commision) {
                        array_push($newvalarr, $lor_commision);
                        array_push($datafield, 'commision');
                    }

                    if ($lor_other_discount) {
                        array_push($newvalarr, $lor_other_discount);
                        array_push($datafield, 'other_discount');
                    }

                    if ($lor_pay_fee) {
                        array_push($newvalarr, $lor_pay_fee);
                        array_push($datafield, 'pay_fee');
                    }

                    if ($lor_pay_meth) {
                        array_push($newvalarr, $lor_pay_meth);
                        array_push($datafield, 'pay_meth');
                    }
             
                    if ($lor_remark) {
                        array_push($newvalarr, $lor_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(lazada_acc,curr_unit,lzd_country,cust_id,cust_name,cust_email,cust_phone,country,oder_number,sales_pic,ship_rec_name,ship_rec_address,ship_rec_contact,brand,series,pkg,item_price_credit,commision,other_discount,pay_fee,final_income,pay_meth,remark,create_by,create_date,create_time) VALUES ('$lor_lazada_acc','$lor_curr_unit','$lor_lzd_country','$lor_cust_id','$lor_cust_name','$lor_cust_email','$lor_cust_phone','$lor_country','$lor_oder_number','$lor_sales_pic','$lor_ship_rec_name','$lor_ship_rec_address','$lor_ship_rec_contact','$lor_brand','$lor_series','$lor_pkg','$lor_item_price_credit','$lor_commision','$lor_other_discount','$lor_pay_fee','$lor_final_income','$lor_pay_meth','$lor_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['lazada_acc'] != $lor_lazada_acc) {
                        array_push($oldvalarr, $row['lazada_acc']);
                        array_push($chgvalarr, $lor_lazada_acc);
                        array_push($datafield, 'lazada_acc');
                    }

                    if ($row['curr_unit'] != $lor_curr_unit) {
                        array_push($oldvalarr, $row['curr_unit']);
                        array_push($chgvalarr, $lor_curr_unit);
                        array_push($datafield, 'curr_unit');
                    }

                    if ($row['lzd_country'] != $lor_lzd_country) {
                        array_push($oldvalarr, $row['lzd_country']);
                        array_push($chgvalarr, $lor_lzd_country);
                        array_push($datafield, 'lzd_country');
                    }

                    if ($row['cust_id'] != $lor_cust_id) {
                        array_push($oldvalarr, $row['cust_id']);
                        array_push($chgvalarr, $lor_cust_id);
                        array_push($datafield, 'cust_id');
                    }

                    if ($row['cust_name'] != $lor_cust_name) {
                        array_push($oldvalarr, $row['cust_name']);
                        array_push($chgvalarr, $lor_cust_name);
                        array_push($datafield, 'cust_name');
                    }

                    if ($row['cust_email'] != $lor_cust_email) {
                        array_push($oldvalarr, $row['cust_email']);
                        array_push($chgvalarr, $lor_cust_email);
                        array_push($datafield, 'cust_email');
                    }

                    if ($row['cust_phone'] != $lor_cust_phone) {
                        array_push($oldvalarr, $row['cust_phone']);
                        array_push($chgvalarr, $lor_cust_phone);
                        array_push($datafield, 'cust_phone');
                    }

                    if ($row['country'] != $lor_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $lor_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['oder_number'] != $lor_oder_number) {
                        array_push($oldvalarr, $row['oder_number']);
                        array_push($chgvalarr, $lor_oder_number);
                        array_push($datafield, 'oder_number');
                    }

                    if ($row['sales_pic'] != $lor_sales_pic) {
                        array_push($oldvalarr, $row['sales_pic']);
                        array_push($chgvalarr, $lor_sales_pic);
                        array_push($datafield, 'sales_pic');
                    }

                    if ($row['ship_rec_name'] != $lor_ship_rec_name) {
                        array_push($oldvalarr, $row['ship_rec_name']);
                        array_push($chgvalarr, $lor_ship_rec_name);
                        array_push($datafield, 'ship_rec_name');
                    }

                    if ($row['ship_rec_address'] != $lor_ship_rec_address) {
                        array_push($oldvalarr, $row['ship_rec_address']);
                        array_push($chgvalarr, $lor_ship_rec_address);
                        array_push($datafield, 'ship_rec_address');
                    }

                    if ($row['ship_rec_contact'] != $lor_ship_rec_contact) {
                        array_push($oldvalarr, $row['ship_rec_contact']);
                        array_push($chgvalarr, $lor_ship_rec_contact);
                        array_push($datafield, 'ship_rec_contact');
                    }

                    if ($row['brand'] != $lor_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $lor_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['series'] != $lor_series) {
                        array_push($oldvalarr, $row['series']);
                        array_push($chgvalarr, $lor_series);
                        array_push($datafield, 'series');
                    }

                    if ($row['pkg '] != $lor_pkg ) {
                        array_push($oldvalarr, $row['pkg']);
                        array_push($chgvalarr, $lor_pkg );
                        array_push($datafield, 'pkg ');
                    }

                    if ($row['item_price_credit'] != $lor_item_price_credit) {
                        array_push($oldvalarr, $row['item_price_credit']);
                        array_push($chgvalarr, $lor_item_price_credit);
                        array_push($datafield, 'item_price_credit');
                    }

                    if ($row['commision'] != $lor_commision) {
                        array_push($oldvalarr, $row['commision']);
                        array_push($chgvalarr, $lor_commision);
                        array_push($datafield, 'commision');
                    }

                    if ($row['other_discount'] != $lor_other_discount) {
                        array_push($oldvalarr, $row['other_discount']);
                        array_push($chgvalarr, $lor_other_discount);
                        array_push($datafield, 'other_discount');
                    }

                    if ($row['pay_fee'] != $lor_pay_fee) {
                        array_push($oldvalarr, $row['pay_fee']);
                        array_push($chgvalarr, $lor_pay_fee);
                        array_push($datafield, 'pay_fee');
                    }

                    if ($row['final_income'] != $lor_final_income) {
                        array_push($oldvalarr, $row['final_income']);
                        array_push($chgvalarr, $lor_final_income);
                        array_push($datafield, 'final_income');
                    }

                    if ($row['pay_meth'] != $lor_pay_meth) {
                        array_push($oldvalarr, $row['pay_meth']);
                        array_push($chgvalarr, $lor_pay_meth);
                        array_push($datafield, 'pay_meth');
                    }

                    if ($row['remark'] != $lor_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $lor_remark == '' ? 'Empty Value' : $lor_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET lazada_acc = '$lor_lazada_acc', curr_unit = '$lor_curr_unit', lzd_country = '$lor_lzd_country', cust_id = '$lor_cust_id', cust_name = '$lor_cust_name', cust_email = '$lor_cust_email', cust_phone = '$lor_cust_phone', country = '$lor_country', oder_number = '$lor_oder_number', sales_pic = '$lor_sales_pic', ship_rec_name = '$lor_ship_rec_name', ship_rec_address = '$lor_ship_rec_address', ship_rec_contact = '$lor_ship_rec_contact', brand = '$lor_brand', series = '$lor_series', pkg = '$lor_pkg', item_price_credit = '$lor_item_price_credit', commision = '$commision', other_discount = '$lor_other_discount', pay_fee = '$lor_pay_fee', final_income = '$lor_final_income', pay_meth = '$lor_pay_meth', remark ='$lor_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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


if (post('act') == 'D') {
    $id = post('id');
    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];

            //SET the record status to 'D'
            deleteRecord($tblName, '', $dataID, $fcb_name, $connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . "</b> from <b><i>$tblName Table</i></b>.";
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
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>
    <div class="page-load-cover">
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
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size: 21px;">
                            <?php if (isset($err1))
                                echo $err1; ?>
                        </span>
                    </div>
                    <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
    <legend class="float-none w-auto p-2">Customer Information</legend>
                    <div class="form-group">
                        <div class="row">
    <div class="col-md-4 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_lazada_acc_lbl" for="lor_lazada_acc">Lazada Account<span class="requireRed">*</span></label>
        <?php
        unset($echoVal);

        if (isset($row['lazada_acc']))
            $echoVal = $row['lazada_acc'];

        if (isset($echoVal)) {
            $lazada_acc_rst = getData('name', "id = '$echoVal'", '', LAZADA_ACC, $finance_connect);
            if (!$lazada_acc_rst) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }
            $lazada_acc_row = $lazada_acc_rst->fetch_assoc();

        }
        ?>
        <input class="form-control" type="text" name="lor_lazada_acc" id="lor_lazada_acc" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $lazada_acc_row['name'] : ''  ?>">
        <input type="hidden" name="lor_lazada_acc_hidden" id="lor_lazada_acc_hidden" value="<?php echo (isset($row['lazada_acc'])) ? $row['lazada_acc'] : ''; ?>">

        <?php if (isset($lazada_acc_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $lazada_acc_err; ?></span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3 autocomplete">
    <label class="form-label form_lbl" id="lor_curr_unit_lbl" for="lor_curr_unit">Currency Unit<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['curr_unit']))
                                    $echoVal = $row['curr_unit'];

                                if (isset($echoVal)) {
                                    $curr_unit_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                                    if (!$curr_unit_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $curr_unit_row = $curr_unit_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="lor_curr_unit" id="lor_curr_unit" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $curr_unit_row['name'] : '' ?>">
                                <input type="hidden" name="lor_curr_unit_hidden" id="lor_curr_unit_hidden"
                                    value="<?php echo (isset($row['curr_unit'])) ? $row['curr_unit'] : ''; ?>">

                                <?php if (isset($curr_unit_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $curr_unit_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>

    <div class="col-md-4 mb-3 autocomplete">
    <label class="form-label form_lbl" id="lor_lzd_country_lbl" for="lor_lzd_country">Country<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['lzd_country']))
                                    $echoVal = $row['lzd_country'];

                                if (isset($echoVal)) {
                                    $lzd_country_rst = getData('name', "id = '$echoVal'", '', COUNTRIES, $connect);
                                    if (!$lzd_country_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $lzd_country_row = $lzd_country_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="lor_lzd_country" id="lor_lzd_country" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $lzd_country_row['name'] : '' ?>">
                                <input type="hidden" name="lor_lzd_country_hidden" id="lor_lzd_country_hidden"
                                    value="<?php echo (isset($row['lzd_country'])) ? $row['lzd_country'] : ''; ?>">

                                <?php if (isset($lzd_country_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $lzd_country_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                </div>
                            </div>


<div class="form-group">
    <div class="row">
    <div class="col-md-6 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_cust_id_lbl" for="lor_cust_id">Customer ID<span class="requireRed">*</span></label>
        <?php
        unset($echoVal);

        if (isset($row['cust_id']))
            $echoVal = $row['cust_id'];

        if (isset($echoVal)) {
            $cust_id_rst = getData('lcr_id', "id = '$echoVal'", '', LAZADA_CUST_RCD, $connect);
            if (!$cust_id_rst) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }
            $cust_id_row = $cust_id_rst->fetch_assoc();
        }
        ?>
        <input class="form-control" type="text" name="lor_cust_id" id="lor_cust_id" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $cust_id_row['cust_id'] : '' ?>">
        <input type="hidden" name="lor_cust_id_hidden" id="lor_cust_id_hidden" value="<?php echo (isset($row['cust_id'])) ? $row['cust_id'] : ''; ?>">
        <?php if (isset($cust_id_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $cust_id_err; ?></span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label form_lbl" id="lor_cust_name_lbl" for="lor_cust_name">Customer Name<span class="requireRed">*</span></label>
        <input class="form-control" type="text" name="lor_cust_name" id="lor_cust_name" value="<?php
        if (isset($dataExisted) && isset($row['cust_name']) && !isset($lor_cust_name)) {
            echo $row['cust_name'];
        } else if (isset($lor_cust_name)) {
            echo $lor_cust_name;
        }
        ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($cust_name_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $cust_name_err; ?></span>
            </div>
        <?php } ?>
    </div>
</div>


<div class="col-md-12">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_cust_email_lbl" for="lor_cust_email">Customer Email<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="lor_cust_email" id="lor_cust_email" value="<?php
            if (isset($dataExisted) && isset($row['cust_email']) && !isset($lor_cust_email)) {
                echo $row['cust_email'];
            } else if (isset($lor_cust_email)) {
                echo $lor_cust_email;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($cust_email_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $cust_email_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_cust_phone_lbl" for="lor_cust_phone">Customer Phone<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="lor_cust_phone" id="lor_cust_phone" value="<?php
            if (isset($dataExisted) && isset($row['cust_phone']) && !isset($lor_cust_phone)) {
                echo $row['cust_phone'];
            } else if (isset($lor_cust_phone)) {
                echo $lor_cust_phone;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($cust_phone_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $cust_phone_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-4 mb-3">
        <button type="button" onclick="toggleNewCustomerSection()">Create New Customer ID</button>
        </div>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div id="new_customer_section" style="display: none;">

        <div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="customer_id">Customer ID</label>
        <input class="form-control" type="text" id="customer_id" name="customer_id">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="customer_name">Customer Name</label>
        <input class="form-control" type="text" id="customer_name" name="customer_name">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="customer_email">Customer Email</label>
        <input class="form-control" type="email" id="customer_email" name="customer_email">
    </div>

<div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="customer_phone">Customer Phone</label>
        <input class="form-control" type="number" id="customer_phone" name="customer_phone">
    </div>
    </div>

    <div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="shipping_name">Shipping Name</label>
        <input class="form-control" type="text" id="shipping_name" name="shipping_name">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="shipping_address">Shipping Address</label>
        <input class="form-control" type="text" id="shipping_address" name="shipping_address">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="shipping_contact">Shipping Contact</label>
        <input class="form-control" type="number" id="shipping_contact" name="shipping_contact">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label form_lbl" for="sales_pic">Sales Person In Charge</label>
        <input class="form-control" type="text" id="sales_pic" name="sales_pic">
    </div>
    </div>
    <input type="submit" name="submit" value="Submit">
    </div>

</fieldset>

<fieldset class="border p-2 mb-3" style="border-radius: 3px;">
    <legend class="float-none w-auto p-2">Order Information</legend>
        <div class="col-md-12">
    <div class="row">
        <div class="col-md-6 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_country_lbl" for="lor_country">Country<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['country']))
                                    $echoVal = $row['country'];

                                if (isset($echoVal)) {
                                    $country_rst = getData('name', "id = '$echoVal'", '', COUNTRIES, $connect);
                                    if (!$country_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $country_row = $country_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="lor_country" id="lor_country" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $country_row['name'] : '' ?>">
                                <input type="hidden" name="lor_country_hidden" id="lor_country_hidden"
                                    value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">

                                <?php if (isset($country_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $country_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_oder_number_lbl" for="lor_oder_number">Order Number<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="lor_oder_number" id="lor_oder_number" value="<?php
            if (isset($dataExisted) && isset($row['oder_number']) && !isset($lor_oder_number)) {
                echo $row['oder_number'];
            } else if (isset($lor_oder_number)) {
                echo $lor_oder_number;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($oder_number_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $oder_number_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_sales_pic_lbl" for="lor_sales_pic">Sales Person In Charge<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="lor_sales_pic" id="lor_sales_pic" value="<?php
            if (isset($dataExisted) && isset($row['sales_pic']) && !isset($lor_sales_pic)) {
                echo $row['sales_pic'];
            } else if (isset($lor_sales_pic)) {
                echo $lor_sales_pic;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($sales_pic_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $sales_pic_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>
        </fieldset>

        <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
    <legend class="float-none w-auto p-2">Shipping Receiver Information</legend>
    <div class="col-md-12">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_ship_rec_name_lbl" for="lor_ship_rec_name">Shipping Receiver Name<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="lor_ship_rec_name" id="lor_ship_rec_name" value="<?php
            if (isset($dataExisted) && isset($row['ship_rec_name']) && !isset($lor_ship_rec_name)) {
                echo $row['ship_rec_name'];
            } else if (isset($lor_ship_rec_name)) {
                echo $lor_ship_rec_name;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($ship_rec_name_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $ship_rec_name_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_ship_rec_address_lbl" for="lor_ship_rec_address">Shipping Receiver Address<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="lor_ship_rec_address" id="lor_ship_rec_address" value="<?php
            if (isset($dataExisted) && isset($row['ship_rec_address']) && !isset($lor_ship_rec_address)) {
                echo $row['ship_rec_address'];
            } else if (isset($lor_ship_rec_address)) {
                echo $lor_ship_rec_address;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($ship_rec_address_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $ship_rec_address_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lor_ship_rec_contact_lbl" for="lor_ship_rec_contact">Shipping Receiver Contact<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="lor_ship_rec_contact" id="lor_ship_rec_contact" value="<?php
            if (isset($dataExisted) && isset($row['ship_rec_contact']) && !isset($lor_ship_rec_contact)) {
                echo $row['ship_rec_contact'];
            } else if (isset($lor_ship_rec_contact)) {
                echo $lor_ship_rec_contact;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($ship_rec_contact_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $ship_rec_contact_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>
        </fieldset>

        <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
    <legend class="float-none w-auto p-2">Price Information</legend>
    <div class="row">
    <div class="col-md-4 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_brand_lbl" for="lor_brand">Brand<span class="requireRed">*</span></label>
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
        <input class="form-control" type="text" name="lor_brand" id="lor_brand" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
        <input type="hidden" name="lor_brand_hidden" id="lor_brand_hidden" value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">
        <?php if (isset($brand_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $brand_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_series_lbl" for="lor_series">Series<span class="requireRed">*</span></label>
        <?php
        unset($echoVal);

        if (isset($row['series']))
            $echoVal = $row['series'];

        if (isset($echoVal)) {
            $series_rst = getData('name', "id = '$echoVal'", '', BRD_SERIES, $connect);
            if (!$series_rst) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }
            $series_row = $series_rst->fetch_assoc();
        }
        ?>
        <input class="form-control" type="text" name="lor_series" id="lor_series" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $series_row['name'] : '' ?>">
        <input type="hidden" name="lor_series_hidden" id="lor_series_hidden" value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">
        <?php if (isset($series_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $series_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_pkg_lbl" for="lor_pkg">Package<span class="requireRed">*</span></label>
        <?php
        unset($echoVal);

        if (isset($row['pkg']))
            $echoVal = $row['pkg'];

        if (isset($echoVal)) {
            $pkg_rst = getData('name', "id = '$echoVal'", '', PKG, $connect);
            if (!$pkg_rst) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }
            $pkg_row = $pkg_rst->fetch_assoc();
        }
        ?>
        <input class="form-control" type="text" name="lor_pkg" id="lor_pkg" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $pkg_row['name'] : '' ?>">
        <input type="hidden" name="lor_pkg_hidden" id="lor_pkg_hidden" value="<?php echo (isset($row['pkg'])) ? $row['pkg'] : ''; ?>">
        <?php if (isset($pkg_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $pkg_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>
</div>


<div class="row">
    <div class="col-md-4 mb-3">
    <label class="form-label form_lbl" id="lor_item_price_credit_lbl" for="lor_item_price_credit">Item Price Credit<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="lor_item_price_credit" id="lor_item_price_credit" value="<?php
        if (isset($dataExisted) && isset($row['item_price_credit']) && !isset($lor_item_price_credit)) {
            echo $row['item_price_credit'];
        } else if (isset($lor_item_price_credit)) {
            echo $lor_item_price_credit;
        }
        ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($item_price_credit_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $item_price_credit_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label form_lbl" id="lor_commision_lbl" for="lor_commision">Commission<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="lor_commision" id="lor_commision" value="<?php
        if (isset($dataExisted) && isset($row['commision']) && !isset($lor_commision)) {
            echo $row['commision'];
        } else if (isset($lor_commision)) {
            echo $lor_commision;
        } else {
            echo '0';
        }
        ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($commision_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $commision_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label form_lbl" id="lor_other_discount_lbl" for="lor_other_discount">Other Discount<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="lor_other_discount" id="lor_other_discount" value="<?php
        if (isset($dataExisted) && isset($row['other_discount']) && !isset($lor_other_discount)) {
            echo $row['other_discount'];
        } else if (isset($lor_ship_rec_contact)) {
            echo $lor_other_discount;
        } else {
            echo '0';
        }
        ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($other_discount_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $other_discount_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>
</div>


<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label form_lbl" id="lor_pay_fee_lbl" for="lor_pay_fee">Payment Fee<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="lor_pay_fee" id="lor_pay_fee" value="<?php
        if (isset($dataExisted) && isset($row['pay_fee']) && !isset($lor_pay_fee)) {
            echo $row['pay_fee'];
        } else if (isset($lor_pay_fee)) {
            echo $lor_pay_fee;
        } else {
            echo '0';
        }
        ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($pay_fee_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $pay_fee_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label form_lbl" id="lor_final_income_lbl" for="lor_final_income">Final Income<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="lor_final_income" id="lor_final_income" value="<?php
        if (isset($dataExisted) && isset($row['final_income']) && !isset($lor_final_income)) {
            echo $row['final_income'];
        } else if (isset($lor_final_income)) {
            echo $lor_final_income;
        }
        ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($final_income_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $final_income_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-4 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lor_pay_meth_lbl" for="lor_pay_meth">Payment Method<span class="requireRed">*</span></label>
        <select class="form-select" id="lor_pay_meth" name="lor_pay_meth" <?php if ($act == '') echo 'disabled' ?>>
            <option value="0" disabled selected>Select Payment Method</option>
            <?php
            if ($pay_meth_list_result->num_rows >= 1) {
                $pay_meth_list_result->data_seek(0);
                while ($pay_meth = $pay_meth_list_result->fetch_assoc()) {
                    $selected = "";
                    if (isset($dataExisted, $row['pay_meth']) && (!isset($lor_pay_meth))) {
                        $selected = $row['pay_meth'] == $pay_meth['id'] ? "selected" : "";
                    } else if (isset($lor_pay_meth)) {
                        list($lor_pay_meth_id, $lor_pay_meth) = explode(':', $lor_pay_meth);
                        $selected = $lor_pay_meth == $pay_meth['id'] ? "selected" : "";
                    }
                    echo "<option value=\"" . $pay_meth['id'] . "\" $selected>" . $pay_meth['name'] . "</option>";
                }
            } else {
                echo "<option value=\"0\">None</option>";
            }
            ?>
        </select>

        <?php if (isset($pay_meth_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $pay_meth_err; ?></span>
            </div>
        <?php } ?>
    </div>
</div>
        </fieldset>
                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="lor_remark_lbl" for="lor_remark">Remark</label>
                    <textarea class="form-control" name="lor_remark" id="lor_remark" rows="3" <?php if ($act == '')
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
                           
                            if (isset($row['oder_number']))
                            $echoVal = $row['oder_number'];
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
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addRequest">Add Request</button>';
                                    break;
                                case 'E':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updRequest">Edit Request</button>';
                                    break;
                            }
                            ?>
                            <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn"
                                id="actionBtn" value="back">Back</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

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
       
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        setButtonColor();
        preloader(300, action);

        <?php
        include "./js/lazada_order_req.js"
        ?>
    </script>

</body>

</html>