<?php
$pageTitle = "Delivery Fees Claim Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = DEL_FEES_CLAIM;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/finance/del_fees_claim_table.php';
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

    $dfc_courier = postSpaceFilter("dfc_courier_hidden");
    $dfc_curr = postSpaceFilter("dfc_curr_hidden");
    $dfc_subtotal = postSpaceFilter("dfc_subtotal");
    $dfc_tax = postSpaceFilter("dfc_tax");
    $dfc_total = postSpaceFilter("dfc_total");
    $dfc_remark = postSpaceFilter("dfc_remark");

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':

            if (!$dfc_courier) {
                $courier_err = "Courier cannot be empty.";
                break;
            } else if (!$dfc_curr) {
                $curr_err = "Currency cannot be empty.";
                break;
            } else if (!(isset($dfc_subtotal) && $dfc_subtotal > 0.00)) {
                $sub_err = "Subtotal cannot be empty.";
                break;
            } else if (!(isset($dfc_total) && $dfc_total > 0.00)) {
                $total_err = "Total cannot be empty.";
                break;
            } else if ($action == 'addTransaction') {
                try {

                    // check value
                    if ($dfc_courier) {
                        array_push($newvalarr, $dfc_courier);
                        array_push($datafield, 'courier');
                    }
                    if ($dfc_curr) {
                        array_push($newvalarr, $dfc_curr);
                        array_push($datafield, 'currency');
                    }
                    if ($dfc_subtotal) {
                        array_push($newvalarr, $dfc_subtotal);
                        array_push($datafield, 'subtotal');
                    }
                    if ($dfc_tax) {
                        array_push($newvalarr, $dfc_tax);
                        array_push($datafield, 'tax');
                    }
                    if ($dfc_total) {
                        array_push($newvalarr, $dfc_total);
                        array_push($datafield, 'total');
                    }
                    if ($dfc_remark) {
                        array_push($newvalarr, $dfc_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(courier,currency,subtotal,tax,total,remark,create_by,create_date,create_time) VALUES ('$dfc_courier','$dfc_curr','$dfc_subtotal','$dfc_tax','$dfc_total','$dfc_remark','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['courier'] != $dfc_courier) {
                        array_push($oldvalarr, $row['courier']);
                        array_push($chgvalarr, $dfc_courier);
                        array_push($datafield, 'courier');
                    }
                    if ($row['currency'] != $dfc_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $dfc_curr);
                        array_push($datafield, 'currency');
                    }
                    if ($row['subtotal'] != $dfc_subtotal) {
                        array_push($oldvalarr, $row['subtotal']);
                        array_push($chgvalarr, $dfc_subtotal);
                        array_push($datafield, 'subtotal');
                    }
                    if ($row['tax'] != $dfc_tax) {
                        array_push($oldvalarr, $row['tax']);
                        array_push($chgvalarr, $dfc_tax);
                        array_push($datafield, 'tax');
                    }
                    if ($row['total'] != $dfc_total) {
                        array_push($oldvalarr, $row['total']);
                        array_push($chgvalarr, $dfc_total);
                        array_push($datafield, 'total');
                    }
                    if ($row['remark'] != $dfc_remark) {
                        array_push($oldvalarr, $row['remark']);
                        array_push($chgvalarr, $dfc_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET courier = '$dfc_courier',currency = '$dfc_curr', subtotal = '$dfc_subtotal', tax = '$dfc_tax', total = '$dfc_total',remark = '$dfc_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
    try {
        // take name
        $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
        $row = $rst->fetch_assoc();

        $dataID = $row['id'];
        //SET the record status to 'D'
        deleteRecord($tblName, '', $dataID, $dfc_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
        generateDBData(META_ADS_ACC, $finance_connect);
        $_SESSION['delChk'] = 1;
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
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

        <div id="DFCFormContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="DFCForm" method="post" action="" enctype="multipart/form-data">
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
                            <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="dfc_courier_lbl" for="dfc_courier">Courier<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['courier']))
                                    $echoVal = $row['courier'];

                                if (isset($echoVal)) {
                                    $courier_rst = getData('name', "id = '$echoVal'", '', COURIER, $connect);
                                    if (!$courier_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $courier_row = $courier_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="dfc_courier" id="dfc_courier" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $courier_row['name'] : '' ?>">
                                <input type="hidden" name="dfc_courier_hidden" id="dfc_courier_hidden"
                                    value="<?php echo (isset($row['courier'])) ? $row['courier'] : ''; ?>">

                                <?php if (isset($courier_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $courier_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="dfc_curr_lbl" for="dfc_curr">Currency<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['currency']))
                                    $echoVal = $row['currency'];

                                if (isset($echoVal)) {
                                    $curr_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                                    if (!$courier_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $curr_row = $curr_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="dfc_curr" id="dfc_curr" <?php if ($act == '')
                                    echo 'readonly' ?>
                                        value="<?php echo !empty($echoVal) ? $curr_row['unit'] : '' ?>">
                                <input type="hidden" name="dfc_curr_hidden" id="dfc_curr_hidden"
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="dfc_subtotal_lbl"
                                    for="dfc_subtotal">Subtotal<span class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="dfc_subtotal" step="0.01"
                                    id="dfc_subtotal" value="<?php
                                    if (isset($dataExisted) && isset($row['subtotal']) && !isset($dfc_subtotal)) {
                                        echo $row['subtotal'];
                                    } else if (isset($dataExisted) && isset($row['subtotal']) && isset($dfc_subtotal)) {
                                        echo $dfc_subtotal;
                                    } else {
                                        echo '';
                                    } ?>" <?php if ($act == '')
                                         echo 'disabled' ?>>
                                <?php if (isset($sub_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $sub_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="dfc_tax_lbl" for="dfc_tax">Tax<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" step="0.01" name="dfc_tax" id="dfc_tax" value="<?php
                                if (isset($dataExisted) && isset($row['tax']) && !isset($dfc_tax)) {
                                    echo $row['tax'];
                                } else if (isset($dataExisted) && isset($row['tax']) && isset($dfc_tax)) {
                                    echo $dfc_tax;
                                } else {
                                    echo '';
                                } ?>" <?php if ($act == '')
                                     echo 'disabled' ?>>
                                <?php if (isset($tax_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $tax_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="dfc_total_lbl" for="dfc_total">Total<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="dfc_total" step="0.01" id="dfc_total"
                                    value="<?php
                                    if (isset($dataExisted) && isset($row['total']) && !isset($dfc_total)) {
                                        echo $row['total'];
                                    } else if (isset($dataExisted) && isset($row['total']) && isset($dfc_total)) {
                                        echo $dfc_total;
                                    } else {
                                        echo '';
                                    } ?>" <?php if ($act == '')
                                         echo 'disabled' ?>>
                                <?php if (isset($total_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $total_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="dfc_remark_lbl" for="dfc_remark">Remark</label>
                        <textarea class="form-control" name="dfc_remark" id="dfc_remark" rows="3" <?php if ($act == '')
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
        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        setButtonColor();
        setAutofocus(action);
        preloader(300, action);

        <?php include "../js/del_fees_claim.js" ?>
    </script>

</body>

</html>