<?php
$pageTitle = "Shopee Ads Top Up Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = SHOPEE_ADS_TOPUP;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


$redirect_page = $SITEURL . '/finance/shopee_ads_topup_trans_table.php';
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

    $sat_acc = postSpaceFilter("sat_shopee_acc_hidden");
    $sat_order_id = postSpaceFilter("sat_order_id");
    $sat_date = postSpaceFilter("sat_date");
    $sat_curr = postSpaceFilter("sat_curr_hidden");
    $sat_amt = postSpaceFilter('sat_amt');
    $sat_subtotal = postSpaceFilter('sat_subtotal');
    $sat_gst = postSpaceFilter('sat_gst');
    $sat_pay = postSpaceFilter('sat_pay_hidden');
    $sat_remark = postSpaceFilter('sat_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            
            if (!$sat_acc && $sat_acc < 1) {
                $acc_err = "Shopee Account cannot be empty.";
                break;
            } else if (!$sat_order_id) {
                $id_err = "Order ID cannot be empty.";
                break;
            } else if (!$sat_date) {
                $date_err = "Date cannot be empty.";
                break;
            } else if (!$sat_curr && $sat_curr < 1) {
                $curr_err = "Currency cannot be empty.";
                break;
            } else if (!$sat_amt) {
                $amt_err = "Top-up amount cannot be empty.";
                break;
            } else if (!$sat_subtotal) {
                $subtotal_err = "Subtotal cannot be empty.";
                break;
            } else if (!$sat_gst) {
                $gst_err = "GST cannot be empty.";
                break;
            } else if (!$sat_pay) {
                $pay_err = "Payment Method cannot be empty.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //check values
                    if ($sat_acc) {
                        array_push($newvalarr, $sat_acc);
                        array_push($datafield, 'account');
                    }
                    if ($sat_order_id) {
                        array_push($newvalarr, $sat_order_id);
                        array_push($datafield, 'order ID');
                    }

                    if ($sat_date) {
                        array_push($newvalarr, $sat_date);
                        array_push($datafield, 'payment date');
                    }

                    if ($sat_curr) {
                        array_push($newvalarr, $sat_curr);
                        array_push($datafield, 'curr');
                    }

                    if ($sat_amt) {
                        array_push($newvalarr, $sat_amt);
                        array_push($datafield, 'top-up amount');
                    }

                    if ($sat_amt) {
                        array_push($newvalarr, $sat_amt);
                        array_push($datafield, 'top-up amount');
                    }

                    if ($sat_gst) {
                        array_push($newvalarr, $sat_amt);
                        array_push($datafield, 'top-up amount');
                    }

                    if ($sat_pay) {
                        array_push($newvalarr, $sat_pay);
                        array_push($datafield, 'payment method');
                    }

                    if ($sat_remark) {
                        array_push($newvalarr, $sat_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(shopee_acc,orderID,payment_date,currency,topup_amt,subtotal,gst,pay_meth,remark,create_by,create_date,create_time) VALUES ('$sat_acc','$sat_order_id','$sat_date','$sat_curr','$sat_amt','$sat_subtotal','$sat_gst','$sat_pay','$sat_remark','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['shopee_acc'] != $sat_acc) {
                        array_push($oldvalarr, $row['shopee_acc']);
                        array_push($chgvalarr, $sat_acc);
                        array_push($datafield, 'shopee account');
                    }

                    if ($row['orderID'] != $sat_order_id) {
                        array_push($oldvalarr, $row['orderID']);
                        array_push($chgvalarr, $sat_order_id);
                        array_push($datafield, 'order ID');
                    }

                    if ($row['payment_date'] != $sat_date) {
                        array_push($oldvalarr, $row['payment_date']);
                        array_push($chgvalarr, $sat_date);
                        array_push($datafield, 'payment date');
                    }

                    if ($row['currency'] != $sat_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $sat_curr);
                        array_push($datafield, 'curr');
                    }

                    if ($row['topup_amt'] != $sat_amt) {
                        array_push($oldvalarr, $row['topup_amt']);
                        array_push($chgvalarr, $sat_amt);
                        array_push($datafield, 'topup_amt');
                    }

                    if ($row['subtotal'] != $sat_subtotal) {
                        array_push($oldvalarr, $row['subtotal']);
                        array_push($chgvalarr, $sat_subtotal);
                        array_push($datafield, 'subtotal');
                    }

                    if ($row['gst'] != $sat_gst) {
                        array_push($oldvalarr, $row['gst']);
                        array_push($chgvalarr, $sat_gst);
                        array_push($datafield, 'gst');
                    }

                    if ($row['pay_meth'] != $sat_pay) {
                        array_push($oldvalarr, $row['pay_meth']);
                        array_push($chgvalarr, $sat_pay);
                        array_push($datafield, 'pay meth');
                    }

                    if ($row['remark'] != $sat_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $sat_remark == '' ? 'Empty Value' : $sat_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET shopee_acc = '$sat_acc', orderID = '$sat_order_id', payment_date = '$sat_date', currency = '$sat_curr', topup_amt = '$sat_amt', subtotal = '$sat_subtotal', gst = '$sat_gst', pay_meth = '$sat_pay', remark ='$sat_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            $sat_order_id = $row['orderID'];

            //SET the record status to 'D'
            deleteRecord($tblName, '', $dataID, $sat_order_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['orderID'] . "</b> from <b><i>$tblName Table</i></b>.";
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
                <form id="FATTForm" method="post" action="" enctype="multipart/form-data">
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
                            <div class="col-md mb-3 autocomplete">
                                <label class="form-label form_lbl" id="sat_shopee_acc_lbl" for="sat_shopee_acc">Shopee
                                    Account<span class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['shopee_acc']))
                                    $echoVal = $row['shopee_acc'];

                                if (isset($echoVal)) {
                                    $shopee_rst = getData('*', "id = '$echoVal'", '', SHOPEE_ACC, $finance_connect);
                                    if (!$shopee_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $shopee_row = $shopee_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="sat_shopee_acc" id="sat_shopee_acc" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $shopee_row['name'] : '' ?>">
                                <input type="hidden" name="sat_shopee_acc_hidden" id="sat_shopee_acc_hidden"
                                    value="<?php echo (isset($row['shopee_acc'])) ? $row['shopee_acc'] : ''; ?>">


                                <?php if (isset($acc_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $acc_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="col-md mb-3">
                                <label class="form-label form_lbl" id="sat_order_lbl" for="sat_order_id">Order ID<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="sat_order_id" id="sat_order_id" value="<?php
                                if (isset($dataExisted) && isset($row['orderID']) && !isset($sat_order_id)) {
                                    echo $row['orderID'];
                                } else if (isset($sat_order_id)) {
                                    echo $sat_order_id;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($amt_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $id_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>


                        </div>

                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md mb-3">
                                <label class="form-label form_lbl" id="sat_date_label" for="sat_date">DateTime<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="datetime-local" name="sat_date" id="sat_date" value="<?php
                                if (isset($dataExisted) && isset($row['payment_date']) && !isset($sat_date)) {
                                    echo $row['payment_date'];
                                } else if (isset($sat_date)) {
                                    echo $sat_date;
                                } else {
                                    echo date('Y-m-d\TH:i');
                                }
                                ?>" placeholder="YYYY-MM-DDTHH:MM" pattern="\d{4}-\d{2}-\d{2}T\d{2}:\d{2}" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($date_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $date_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="col-md mb-3 autocomplete">
                                <label class="form-label form_lbl" id="sat_curr_lbl" for="sat_curr">Currency<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['currency']))
                                    $echoVal = $row['currency'];

                                if (isset($echoVal)) {
                                    $curr_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                                    if (!$curr_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $curr_row = $curr_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="sat_curr" id="sat_curr" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $curr_row['unit'] : '' ?>">
                                <input type="hidden" name="sat_curr_hidden" id="sat_curr_hidden"
                                    value="<?php echo (isset($row['currency'])) ? $row['currency'] : ''; ?>">


                                <?php if (isset($curr_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $curr_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>


                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md mb-3">
                                <label class="form-label form_lbl" id="sat_amt_lbl" for="sat_amt">Amount<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" step="0.01" name="sat_amt" id="sat_amt" value="<?php
                                if (isset($dataExisted) && isset($row['topup_amt']) && !isset($sat_amt)) {
                                    echo $row['topup_amt'];
                                } else if (isset($sat_amt)) {
                                    echo $sat_amt;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($amt_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $amt_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md mb-3">
                                <label class="form-label form_lbl" id="sat_subtotal_lbl" for="sat_subtotal">Subtotal<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" step="0.01" name="sat_subtotal" id="sat_subtotal" value="<?php
                                if (isset($dataExisted) && isset($row['subtotal']) && !isset($sat_subtotal)) {
                                    echo $row['subtotal'];
                                } else if (isset($sat_subtotal)) {
                                    echo $sat_subtotal;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($subtotal_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $subtotal_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md mb-3">
                                <label class="form-label form_lbl" id="sat_gst_lbl" for="sat_gst">GST (%)<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" step="0.01" name="sat_gst" id="sat_gst" value="<?php
                                if (isset($dataExisted) && isset($row['gst']) && !isset($sat_gst)) {
                                    echo $row['gst'];
                                } else if (isset($sat_gst)) {
                                    echo $sat_gst;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($gst_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $gst_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md mb-3 autocomplete">
                                <label class="form-label form_lbl" id="sat_pay_lbl" for="sat_pay">Payment Method<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['pay_meth']))
                                    $echoVal = $row['pay_meth'];

                                if (isset($echoVal)) {
                                    $pay_rst = getData('name', "id = '$echoVal'", '', FIN_PAY_METH, $finance_connect);
                                    if (!$pay_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $pay_row = $pay_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="sat_pay" id="sat_pay" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $pay_row['name'] : '' ?>">
                                <input type="hidden" name="sat_pay_hidden" id="sat_pay_hidden"
                                    value="<?php echo (isset($row['pay_meth'])) ? $row['pay_meth'] : ''; ?>">


                                <?php if (isset($pay_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $pay_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="sat_remark_lbl" for="sat_remark">Remark</label>
                        <textarea class="form-control" name="sat_remark" id="sat_remark" rows="3" <?php if ($act == '')
                            echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark']))
                            echo $row['remark'] ?></textarea>
                        </div>



                        <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                            <?php
                        switch ($act) {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addTransaction">Add Transaction</button>';
                                break;
                            case 'E':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updTransaction">Edit Transaction</button>';
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
        <?php include "../js/shopee_ads_topup_trans.js" ?>
    </script>

</body>

</html>