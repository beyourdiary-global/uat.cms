<?php
$pageTitle = "Website Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = WEB_ORDER_REQ;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


$redirect_page = $SITEURL . '/finance/website_order_request_table.php';
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

if (post('actionBtn')) {
    $action = post('actionBtn');

    $wor_order_id = postSpaceFilter('wor_order_id');
    $wor_brand = postSpaceFilter('wor_brand');
    $wor_series = postSpaceFilter('wor_series');
    $wor_pkg = postSpaceFilter('wor_pkg_hidden');
    $wor_country = postSpaceFilter('wor_country_hidden');
    $wor_currency = postSpaceFilter('wor_currency_hidden');
    $wor_price = postSpaceFilter('wor_price');
    $wor_shipping = postSpaceFilter('wor_shipping');
    $wor_discount = postSpaceFilter('wor_discount');
    $wor_total = postSpaceFilter('wor_total');
    $wor_pay = postSpaceFilter('wor_pay_hidden');
    $wor_pic = postSpaceFilter('wor_pic');
    $wor_cust_id = postSpaceFilter('wor_cust_id_hidden');
    $wor_customer_id = postSpaceFilter('wor_customer_id');
    $wor_cust_brand = postSpaceFilter('wor_cust_brand_hidden');
    $wor_cust_series = postSpaceFilter('wor_cust_series_hidden');
    $wor_cust_ship_name = postSpaceFilter('wor_cust_ship_name');
    $wor_cust_ship_address = postSpaceFilter('wor_cust_ship_address');
    $wor_cust_ship_contact = postSpaceFilter('wor_cust_ship_contact');
    $wor_cust_name = postSpaceFilter('wor_cust_name');
    $wor_cust_email = postSpaceFilter('wor_cust_email');
    $wor_cust_birthday = postSpaceFilter('wor_cust_birthday');
    $wor_shipping_name = postSpaceFilter('wor_shipping_name');
    $wor_shipping_address = postSpaceFilter('wor_shipping_address');
    $wor_shipping_contact = postSpaceFilter('wor_shipping_contact');
    $wor_remark = postSpaceFilter('wor_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':
        case 'updRecord':

            if ($wor_cust_email && !isEmail($wor_cust_email)) {
                $cust_email_err = "Wrong email format!";
                $error = 1;
                break;
            }

            if (!$wor_order_id) {
                $order_id_err = "Order ID cannot be empty.";
                break;
            } else if (!$wor_brand) {
                $brand_err = "Brand cannot be empty.";
                break;
            } else if (!$wor_series) {
                $series_err = "Series cannot be empty.";
                break;
            } else if (!$wor_pkg && $wor_pkg < 1) {
                $pkg_err = "Package cannot be empty.";
                break;
            } else if (!$wor_country && $wor_country < 1) {
                $country_err = "Country cannot be empty.";
                break;
            } else if (!$wor_currency && $wor_currency < 1) {
                $currency_err = "Currency cannot be empty.";
                break;
            } else if (!$wor_price) {
                $price_err = "Price cannot be empty.";
                break;
            } else if (!$wor_shipping) {
                $shipping_err = "Shipping cannot be empty.";
                break;
            } else if (!$wor_discount) {
                $discount_err = "Discount cannot be empty.";
                break;

            } else if (!$wor_pic) {
                $pic_err = "Person In Charge cannot be empty.";
                break;

            }else if (($wor_cust_id == 'Create New Customer ID') && !isset($wor_customer_id)) {
                    $customer_id_err = "Customer ID is required!";
                break;
            } else if (($wor_cust_id == 'Create New Customer ID') && isDuplicateRecord("cust_id", $wor_customer_id, WEB_CUST_RCD, $connect, '')) {
                    $customer_id_err = "Duplicate record found for Customer ID.";
                    $isDuplicateCustID = true;
                break;
            } else if (!$wor_cust_name) {
                $cust_name_err = "Customer Name cannot be empty.";
                break;
            } else if (!$wor_cust_email) {
                $cust_email_err = "Customer Email cannot be empty.";
                break;
            } else if (!$wor_cust_birthday) {
                $cust_birthday_err = "Customer Birthday cannot be empty.";
                break;
            } else if (!$wor_shipping_name) {
                $shipping_name_err = "Customer Name cannot be empty.";
                break;
            } else if (!$wor_shipping_address) {
                $shipping_address_err = "Customer Address cannot be empty.";
                break;
            } else if (!$wor_shipping_contact) {
                $shipping_contact_err = "Customer Contact cannot be empty.";
                break;
               
            } else if ($action == 'addRecord') {
                try {
                    //check values

                    if (($wor_cust_id == 'Create New Customer ID') && !($isDuplicateCustID)) {
                            try {
                                $wor_cust_id = insertNewCustID($wor_customer_id, USER_ID, $finance_connect);
                                generateDBData(WEB_CUST_RCD, $connect);
                            } catch (Exception $e) {
                                $errorMsg = $e->getMessage();
                            }
                    }

                    if ($wor_order_id) {
                        array_push($newvalarr, $wor_order_id);
                        array_push($datafield, 'order_id');
                    }

                    if ($wor_brand) {
                        array_push($newvalarr, $wor_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($wor_series) {
                        array_push($newvalarr, $wor_series);
                        array_push($datafield, 'series');
                    }

                    if ($wor_pkg) {
                        array_push($newvalarr, $wor_pkg);
                        array_push($datafield, 'pkg');
                    }

                    if ($wor_country) {
                        array_push($newvalarr, $wor_country);
                        array_push($datafield, 'country');
                    }

                    if ($wor_currency) {
                        array_push($newvalarr, $wor_currency);
                        array_push($datafield, 'currency');
                    }
                  
                    if ($wor_price) {
                        array_push($newvalarr, $wor_price);
                        array_push($datafield, 'price');
                    }

                    if ($wor_shipping) {
                        array_push($newvalarr, $wor_shipping);
                        array_push($datafield, 'shipping');
                    }

                    if ($wor_discount) {
                        array_push($newvalarr, $wor_discount);
                        array_push($datafield, 'discount');
                    }

                    if ($wor_total) {
                        array_push($newvalarr, $wor_total);
                        array_push($datafield, 'total');
                    }

                    if ($wor_pay) {
                        array_push($newvalarr, $wor_pay);
                        array_push($datafield, 'pay_method');
                    }

                    if ($wor_pic) {
                        array_push($newvalarr, $wor_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($wor_cust_id) {
                        array_push($newvalarr, $wor_cust_id);
                        array_push($datafield, 'cust_id');
                    }

                    if ($wor_cust_name) {
                        array_push($newvalarr, $wor_cust_name);
                        array_push($datafield, 'cust_name');
                    }

                    if ($wor_cust_email) {
                        array_push($newvalarr, $wor_cust_email);
                        array_push($datafield, 'cust_email');
                    }

                    if ($wor_cust_birthday) {
                        array_push($newvalarr, $wor_cust_birthday);
                        array_push($datafield, 'cust_birthday');
                    }

                    if ($wor_shipping_name) {
                        array_push($newvalarr, $wor_shipping_name);
                        array_push($datafield, 'shipping_name');
                    }

                    if ($wor_shipping_address) {
                        array_push($newvalarr, $wor_shipping_address);
                        array_push($datafield, 'shipping_address');
                    }

                    if ($wor_shipping_contact) {
                        array_push($newvalarr, $wor_shipping_contact);
                        array_push($datafield, 'shipping_contact');
                    }

                    if ($wor_remark) {
                        array_push($newvalarr, $wor_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . " (order_id,brand,series,pkg,country,currency,price,shipping,discount,total,pay_method,pic,cust_id,cust_name,cust_email,cust_birthday,shipping_name,shipping_address,shipping_contact,remark,create_by,create_date,create_time) VALUES ('$wor_order_id','$wor_brand','$wor_series','$wor_pkg','$wor_country','$wor_currency','$wor_price','$wor_shipping','$wor_discount','$wor_total','$wor_pay','$wor_pic','$wor_cust_id','$wor_cust_name','$wor_cust_email','$wor_cust_birthday','$wor_shipping_name','$wor_shipping_address','$wor_shipping_contact','$wor_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {

                    if (($wor_cust_id == 'Create New Customer ID') && !($isDuplicateCustID)) {
                        try {
                            $wor_cust_id = insertNewCustID($wor_customer_id, USER_ID, $finance_connect);
                        } catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }
                    }

                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['order_id'] != $wor_order_id) {
                        array_push($oldvalarr, $row['order_id']);
                        array_push($chgvalarr, $wor_order_id);
                        array_push($datafield, 'order_id');
                    }

                    if ($row['brand'] != $wor_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $wor_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['series'] != $wor_series) {
                        array_push($oldvalarr, $row['series']);
                        array_push($chgvalarr, $wor_series);
                        array_push($datafield, 'series');
                    }

                    if ($row['pkg'] != $wor_pkg) {
                        array_push($oldvalarr, $row['pkg']);
                        array_push($chgvalarr, $wor_pkg);
                        array_push($datafield, 'pkg');
                    }

                    if ($row['country'] != $wor_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $wor_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['currency'] != $wor_currency) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $wor_currency);
                        array_push($datafield, 'currency');
                    }

                    if ($row['price'] != $wor_price) {
                        array_push($oldvalarr, $row['price']);
                        array_push($chgvalarr, $wor_price);
                        array_push($datafield, 'price');
                    }

                    if ($row['shipping'] != $wor_shipping) {
                        array_push($oldvalarr, $row['shipping']);
                        array_push($chgvalarr, $wor_shipping);
                        array_push($datafield, 'shipping');
                    }

                    if ($row['discount'] != $wor_discount) {
                        array_push($oldvalarr, $row['discount']);
                        array_push($chgvalarr, $wor_discount);
                        array_push($datafield, 'discount');
                    }

                    if ($row['total'] != $wor_total) {
                        array_push($oldvalarr, $row['total']);
                        array_push($chgvalarr, $wor_total);
                        array_push($datafield, 'total');
                    }

                    if ($row['pay_method'] != $wor_pay) {
                        array_push($oldvalarr, $row['pay_method']);
                        array_push($chgvalarr, $wor_pay);
                        array_push($datafield, 'pay_method');
                    }

                    if ($row['pic'] != $wor_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $wor_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['cust_id'] != $wor_cust_id) {
                        array_push($oldvalarr, $row['cust_id']);
                        array_push($chgvalarr, $wor_cust_id);
                        array_push($datafield, 'cust_id');
                    }

                    if ($row['cust_name'] != $wor_cust_name) {
                        array_push($oldvalarr, $row['cust_name']);
                        array_push($chgvalarr, $wor_cust_name);
                        array_push($datafield, 'cust_name');
                    }
                    
                    if ($row['cust_email'] != $wor_cust_email) {
                        array_push($oldvalarr, $row['cust_email']);
                        array_push($chgvalarr, $wor_cust_email);
                        array_push($datafield, 'cust_email');
                    }

                    if ($row['cust_birthday'] != $wor_cust_birthday) {
                        array_push($oldvalarr, $row['cust_birthday']);
                        array_push($chgvalarr, $wor_cust_birthday);
                        array_push($datafield, 'cust_birthday');
                    }

                    if ($row['shipping_name'] != $wor_shipping_name) {
                        array_push($oldvalarr, $row['shipping_name']);
                        array_push($chgvalarr, $wor_shipping_name);
                        array_push($datafield, 'shipping_name');
                    }
                
                    if ($row['shipping_address'] != $wor_shipping_address) {
                    array_push($oldvalarr, $row['shipping_address']);
                    array_push($chgvalarr, $wor_shipping_address);
                    array_push($datafield, 'shipping_address');
                    }

                    if ($row['shipping_contact'] != $wor_shipping_contact) {
                        array_push($oldvalarr, $row['shipping_contact']);
                        array_push($chgvalarr, $wor_shipping_contact);
                        array_push($datafield, 'shipping_contact');
                        }

                    if ($row['remark'] != $wor_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $for_remark == '' ? 'Empty Value' : $wor_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET order_id = '$wor_order_id', brand = '$wor_brand', series = '$wor_series', pkg = '$wor_pkg', country = '$wor_country', currency = '$wor_currency', price = '$wor_price', shipping = '$wor_shipping', discount = '$wor_discount', pay_method = '$wor_pay', pic = '$wor_pic', cust_id = '$wor_cust_id', cust_name = '$wor_cust_name', cust_email = '$wor_cust_email', cust_birthday = '$wor_cust_birthday', shipping_name = '$wor_shipping_name', shipping_address = '$wor_shipping_address', shipping_contact = '$wor_shipping_contact', remark ='$wor_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            deleteRecord($tblName, '', $dataID, $for_name, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['order_id'] . "</b> from <b><i>$tblName Table</i></b>.";
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
        <div class="col-md-4 mb-3">
            <label class="form-label form_lbl" id="wor_order_id_lbl" for="wor_order_id">Order ID<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="wor_order_id" id="wor_order_id" value="<?php
            if (isset($dataExisted) && isset($row['order_id']) && !isset($wor_order_id)) {
                echo $row['order_id'];
            } else if (isset($wor_order_id)) {
                echo $wor_order_id;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($order_id_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $order_id_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label form_lbl" id="wor_brand_lbl" for="wor_brand">Brand<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="wor_brand" id="wor_brand" value="<?php
            if (isset($dataExisted) && isset($row['brand']) && !isset($wor_brand)) {
                echo $row['brand'];
            } else if (isset($wor_brand)) {
                echo $wor_brand;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($brand_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $brand_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label form_lbl" id="wor_series_lbl" for="wor_series">Series<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="wor_series" id="wor_series" value="<?php
            if (isset($dataExisted) && isset($row['series']) && !isset($wor_series)) {
                echo $row['series'];
            } else if (isset($wor_series)) {
                echo $wor_series;
            }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($series_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $series_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>


                        <div class="form-group">
    <div class="row">
        <div class="col-md-4 mb-3 autocomplete">
            <label class="form-label form_lbl" id="wor_pkg_lbl" for="wor_pkg">Package<span class="requireRed">*</span></label>
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
            <input class="form-control" type="text" name="wor_pkg" id="wor_pkg" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $pkg_row['name'] : '' ?>">
            <input type="hidden" name="wor_pkg_hidden" id="wor_pkg_hidden" value="<?php echo (isset($row['pkg'])) ? $row['pkg'] : ''; ?>">
            <?php if (isset($pkg_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $pkg_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-4 mb-3 autocomplete">
            <label class="form-label form_lbl" id="wor_country_lbl" for="wor_country">Country<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['country']))
                $echoVal = $row['country'];

            if (isset($echoVal)) {
                $country_rst = getData('nicename', "id = '$echoVal'", '', COUNTRIES, $connect);
                if (!$country_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $country_row = $country_rst->fetch_assoc();
            }
            ?>
            <input class="form-control" type="text" name="wor_country" id="wor_country" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $country_row['nicename'] : '' ?>">
            <input type="hidden" name="wor_country_hidden" id="wor_country_hidden" value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">
            <?php if (isset($country_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $country_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-4 mb-3 autocomplete">
            <label class="form-label form_lbl" id="wor_currency_lbl" for="wor_currency">Currency<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['currency']))
                $echoVal = $row['currency'];

            if (isset($echoVal)) {
                $currency_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                if (!$currency_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $currency_row = $currency_rst->fetch_assoc();
            }
            ?>
            <input class="form-control" type="text" name="wor_currency" id="wor_currency" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $currency_row['unit'] : '' ?>">
            <input type="hidden" name="wor_currency_hidden" id="wor_currency_hidden" value="<?php echo (isset($row['currency'])) ? $row['currency'] : ''; ?>">
            <?php if (isset($currency_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $currency_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>


                           
    <div class="form-group">
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label form_lbl" id="wor_price_lbl" for="wor_price">Price<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="wor_price" id="wor_price" value="<?php if (isset($dataExisted) && isset($row['price']) && !isset($wor_price)) { echo $row['price']; } else if (isset($wor_price)) { echo $wor_price; } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($price_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $price_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label form_lbl" id="wor_shipping_lbl" for="wor_shipping">Shipping<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="wor_shipping" id="wor_shipping" value="<?php if (isset($dataExisted) && isset($row['shipping']) && !isset($wor_shipping)) { echo $row['shipping']; } else if (isset($wor_shipping)) { echo $wor_shipping; } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($shipping_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $shipping_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label form_lbl" id="wor_discount_lbl" for="wor_discount">Discount Price<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="wor_discount" id="wor_discount" value="<?php if (isset($dataExisted) && isset($row['discount']) && !isset($wor_discount)) { echo $row['discount']; } else if (isset($wor_discount)) { echo $wor_discount; } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($discount_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $discount_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label form_lbl" id="wor_total_lbl" for="wor_total">Total<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="wor_total" id="wor_total" value="<?php if (isset($dataExisted) && isset($row['total']) && !isset($wor_total)) { echo $row['total']; } else if (isset($wor_total)) { echo $wor_total; } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($total_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $total_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>


<div class="form-group">
    <div class="row">
        <div class="col-md-6 mb-3 autocomplete">
        <label class="form-label form_lbl" id="wor_pay_lbl" for="wor_pay">Payment Method<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['pay_method']))
                $echoVal = $row['pay_method'];

            if (isset($echoVal)) {
                $pay_rst = getData('name', "id = '$echoVal'", '', FIN_PAY_METH, $finance_connect);
                if (!$pay_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $pay_row = $pay_rst->fetch_assoc();
            }
            ?>
            <input class="form-control" type="text" name="wor_pay" id="wor_pay" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $pay_row['name'] : '' ?>">
            <input type="hidden" name="wor_pay_hidden" id="wor_pay_hidden" value="<?php echo (isset($row['pay_method'])) ? $row['pay_method'] : ''; ?>">
            <?php if (isset($pay_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $pay_err; ?></span>
                </div>
            <?php } ?>
        </div>

                    

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="wor_pic_lbl" for="wor_pic">Person In Charge<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="wor_pic" id="wor_pic" value="<?php if (isset($dataExisted) && isset($row['pic']) && !isset($wor_pic)) { echo $row['pic']; } else if (isset($wor_pic)) { echo $wor_pic; } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($pic_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $pic_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>


<fieldset class="border p-2 mb-3" style="border-radius: 3px;">
    <legend class="float-none w-auto p-2">Customer Info</legend>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6 autocomplete mb-3">
                <label class="form-label form_lbl" id="wor_cust_id_lbl" for="wor_cust_id">Customer ID<span class="requireRed">*</span></label>
                <?php
                unset($echoVal);

                if (isset($row['cust_id']))
                    $echoVal = $row['cust_id'];

                if (isset($echoVal)) {
                    $cust_id_rst = getData('cust_id', "id = '$echoVal'", '', WEB_CUST_RCD, $connect);
                    if (!$cust_id_rst) {
                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                    }
                    $cust_id_row = $cust_id_rst->fetch_assoc();
                }
                ?>
                <input class="form-control" type="text" name="wor_cust_id" id="wor_cust_id" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $cust_id_row['cust_id'] : '' ?>">
                <input type="hidden" name="wor_cust_id_hidden" id="wor_cust_id_hidden" value="<?php echo (isset($row['cust_id'])) ? $row['cust_id'] : ''; ?>">

                <?php if (isset($cust_id_err)) { ?>
                    <div id="err_msg">
                        <span class="mt-n1">
                            <?php echo $cust_id_err; ?>
                        </span>
                    </div>
                <?php } ?>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label form_lbl" id="wor_cust_name_lbl" for="wor_cust_name">Customer Name<span class="requireRed">*</span></label>
                <input class="form-control" type="text" name="wor_cust_name" id="wor_cust_name" value="<?php
                if (isset($dataExisted) && isset($row['cust_name']) && !isset($wor_cust_name)) {
                    echo $row['cust_name'];
                } else if (isset($wor_cust_name)) {
                    echo $wor_cust_name;
                }
                ?>" <?php if ($act == '') echo 'disabled' ?>>
                <?php if (isset($cust_name_err)) { ?>
                    <div id="err_msg">
                        <span class="mt-n1">
                            <?php echo $cust_name_err; ?>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label form_lbl" id="wor_cust_email_lbl" for="wor_cust_email">Customer Email<span class="requireRed">*</span></label>
                <input class="form-control" type="text" name="wor_cust_email" id="wor_cust_email" value="<?php
                if (isset($dataExisted) && isset($row['cust_email']) && !isset($wor_cust_email)) {
                    echo $row['cust_email'];
                } else if (isset($wor_cust_email)) {
                    echo $wor_cust_email;
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

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="wor_cust_birthday_label" for="wor_cust_birthday">Customer Birthday<span class="requireRed">*</span></label>
                    <input class="form-control" type="date" name="wor_cust_birthday" id="wor_cust_birthday" value="<?php
                        if (isset($dataExisted) && isset($row['cust_birthday']) && !isset($wor_cust_birthday)) {
                            echo $row['cust_birthday'];
                        } else if (isset($wor_cust_birthday)) {
                            echo $wor_cust_birthday;
                        } else {
                            echo date('Y-m-d');
                        }
                    ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '') echo 'disabled' ?>>
                    <?php if (isset($cust_birthday_err)) { ?>
                        <div id="err_msg">
                            <span class="mt-n1"><?php echo $cust_birthday_err; ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <div class="row">
                <div id="WOR_CreateCustID" hidden>
                    <div class="col-md-6">
                        <label class="form-label form_lbl" id="wor_customer_id_lbl" for="wor_customer_id">Customer ID*</label>
                        <input class="form-control" type="text" name="wor_customer_id" id="wor_customer_id" <?php if ($act == '') echo 'readonly' ?>>
                        <?php if (isset($customer_id_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1">
                                    <?php echo $customer_id_err; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div id="WOR_CreateCustBrand" hidden>
                    <div class="col-md-6 mb-3 autocomplete">
                        <label class="form-label form_lbl" id="wor_cust_brand_lbl" for="wor_cust_brand">Brand<span class="requireRed">*</span></label>
                        <?php
                        unset($echoVal);

                        if (isset($row['brand']))
                            $echoVal = $row['brand'];

                        if (isset($echoVal)) {
                            $cust_brand_rst = getData('name', "id = '$echoVal'", '', BRAND, $connect);
                            if (!$cust_brand_rst) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $cust_brand_row = $cust_brand_rst->fetch_assoc();
                        }
                        ?>
                        <input class="form-control" type="text" name="wor_cust_brand" id="wor_cust_brand" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $cust_brand_row['name'] : '' ?>">
                        <input type="hidden" name="wor_cust_brand_hidden" id="wor_cust_brand_hidden" value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">

                        <?php if (isset($cust_brand_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1">
                                    <?php echo $cust_brand_err; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div id="WOR_CreateCustSeries" hidden>
                    <div class="col-md-6 mb-3 autocomplete">
                        <label class="form-label form_lbl" id="wor_cust_series_lbl" for="wor_cust_series">Series<span class="requireRed">*</span></label>
                        <?php
                        unset($echoVal);

                        if (isset($row['series']))
                            $echoVal = $row['series'];

                        if (isset($echoVal)) {
                            $cust_series_rst = getData('name', "id = '$echoVal'", '', BRD_SERIES, $connect);
                            if (!$cust_series_rst) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $cust_series_row = $cust_series_rst->fetch_assoc();
                        }
                        ?>
                        <input class="form-control" type="text" name="wor_cust_series" id="wor_cust_series" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $cust_series_row['name'] : '' ?>">
                        <input type="hidden" name="wor_cust_series_hidden" id="wor_cust_series_hidden" value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">

                        <?php if (isset($cust_series_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1">
                                    <?php echo $cust_series_err; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div id="WOR_CreateCustShipName" hidden>
                    <div class="col-md-6">
                        <label class="form-label form_lbl" id="wor_cust_ship_name_lbl" for="wor_cust_ship_name">Customer Shipping Name*</label>
                        <input class="form-control" type="text" name="wor_cust_ship_name" id="wor_cust_ship_name" <?php if ($act == '') echo 'readonly' ?>>
                        <?php if (isset($cust_ship_name_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1">
                                    <?php echo $cust_ship_name_err; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div id="WOR_CreateCustShipAddress" hidden>
                    <div class="col-md-6">
                        <label class="form-label form_lbl" id="wor_cust_ship_address_lbl" for="wor_cust_ship_address">Customer Shipping Address*</label>
                        <input class="form-control" type="text" name="wor_cust_ship_address" id="wor_cust_ship_address" <?php if ($act == '') echo 'readonly' ?>>
                        <?php if (isset($cust_ship_address_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1">
                                    <?php echo $cust_ship_address_err; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div id="WOR_CreateCustShipContact" hidden>
                    <div class="col-md-6">
                        <label class="form-label form_lbl" id="wor_cust_ship_contact_lbl" for="wor_cust_ship_contact">Customer Shipping Contact*</label>
                        <input class="form-control" type="text" name="wor_cust_ship_contact" id="wor_cust_ship_contact" <?php if ($act == '') echo 'readonly' ?>>
                        <?php if (isset($cust_ship_contact_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1">
                                    <?php echo $cust_ship_contact_err; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</fieldset>


<fieldset class="border p-2 mb-3" style="border-radius: 3px;">
    <legend class="float-none w-auto p-2">Shipping Address</legend>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label form_lbl" id="wor_shipping_name_lbl" for="wor_shipping_name">Shipping Name<span class="requireRed">*</span></label>
                <input class="form-control" type="text" name="wor_shipping_name" id="wor_shipping_name" value="<?php
                if (isset($dataExisted) && isset($row['shipping_name']) && !isset($wor_shipping_name)) {
                    echo $row['shipping_name'];
                } else if (isset($wor_shipping_name)) {
                    echo $wor_shipping_name;
                }
                ?>" <?php if ($act == '') echo 'disabled' ?>>
                <?php if (isset($shipping_name_err)) { ?>
                    <div id="err_msg">
                        <span class="mt-n1">
                            <?php echo $shipping_name_err; ?>
                        </span>
                    </div>
                <?php } ?>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label form_lbl" id="wor_shipping_address_lbl" for="wor_shipping_address">Shipping Address<span class="requireRed">*</span></label>
                <input class="form-control" type="text" name="wor_shipping_address" id="wor_shipping_address" value="<?php
                if (isset($dataExisted) && isset($row['shipping_address']) && !isset($wor_shipping_address)) {
                    echo $row['shipping_address'];
                } else if (isset($wor_shipping_address)) {
                    echo $wor_shipping_address;
                }
                ?>" <?php if ($act == '') echo 'disabled' ?>>
                <?php if (isset($shipping_address_err)) { ?>
                    <div id="err_msg">
                        <span class="mt-n1">
                            <?php echo $shipping_address_err; ?>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label form_lbl" id="wor_shipping_contact_lbl" for="wor_shipping_contact">Shipping Contact<span class="requireRed">*</span></label>
                <input class="form-control" type="number" name="wor_shipping_contact" id="wor_shipping_contact" value="<?php
                if (isset($dataExisted) && isset($row['shipping_contact']) && !isset($wor_shipping_contact)) {
                    echo $row['shipping_contact'];
                } else if (isset($wor_shipping_contact)) {
                    echo $wor_shipping_contact;
                }
                ?>" <?php if ($act == '') echo 'disabled' ?>>
                <?php if (isset($shipping_contact_err)) { ?>
                    <div id="err_msg">
                        <span class="mt-n1">
                            <?php echo $shipping_contact_err; ?>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</fieldset>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="wor_remark_lbl" for="wor_remark">Remark</label>
                    <textarea class="form-control" name="wor_remark" id="wor_remark" rows="3" <?php if ($act == '')
                        echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark']))
                        echo $row['remark'] ?></textarea>
                    </div>
                

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
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);

        <?php
        include "../js/website_order_request.js"
            ?>
    </script>

</body>

</html>