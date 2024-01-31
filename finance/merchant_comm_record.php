<?php
$pageTitle = "Merchant Commission Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = MRCHT_COMM;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/merchant_comm_record_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/merchant_commission/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);

    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
        $trans_id = $row['merchantID'];
    } else {
        // If $rst is false or no data found ($act==null)
        $errorExist = 1;
        $_SESSION['tempValConfirmBox'] = true;
        $act = "F";
    }
} else { //add transaction
    // generate transaction id
    $id = date('Ymd_His');

    $trans_id = "MCR{$id}";
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for currency
$cur_list_result = getData('*', '', '', CUR_UNIT, $connect);

if (post('actionBtn')) {
    $action = post('actionBtn');

    $mcr_date = postSpaceFilter("mcr_date");
    $mcr_curr = postSpaceFilter('mcr_currency');
    $mcr_amt = postSpaceFilter('mcr_amt');
    $mcr_amt = floatval(str_replace(',', '', $mcr_amt));
    $mcr_remark = postSpaceFilter('mcr_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':

            if (!$mcr_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$mcr_curr && $mcr_curr < 1) {
                $curr_err = "Please specify the currency.";
                break;
            } else if (!$mcr_amt) {
                $amt_err = "Please specify the amount.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    
                    //check values
                    if ($mcr_date) {
                        array_push($newvalarr, $mcr_date);
                        array_push($datafield, 'date');
                    }

                    if ($mcr_curr) {
                        array_push($newvalarr, $mcr_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($mcr_amt) {
                        array_push($newvalarr, $mcr_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($mcr_remark) {
                        array_push($newvalarr, $mcr_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(merchantID,date,currency,amount,remark,create_by,create_date,create_time) VALUES ('$trans_id','$mcr_date','$mcr_curr','$mcr_amt','$mcr_remark','" . USER_ID . "',curdate(),curtime())";
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
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['date'] != $mcr_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $mcr_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['currency'] != $mcr_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $mcr_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($row['amount'] != $mcr_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $mcr_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($row['remark'] != $mcr_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $mcr_remark == '' ? 'Empty Value' : $mcr_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        
                        $query = "UPDATE " . $tblName  . " SET date = '$mcr_date', currency = '$mcr_curr', amount = '$mcr_amt', remark ='$mcr_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                    'log_act'      => $pageAction,
                    'cdate'        => $cdate,
                    'ctime'        => $ctime,
                    'uid'          => USER_ID,
                    'cby'          => USER_ID,
                    'query_rec'    => $query,
                    'query_table'  => $tblName,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($pageAction == 'Edit') {
                    $log['oldval']  = implodeWithComma($oldvalarr);
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName , $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            $trans_id = $row['merchantID'];

            //SET the record status to 'D'
            deleteRecord($tblName ,'', $dataID, $trans_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $trans_id = isset($dataExisted) ? $row['merchantID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['merchantID'] . "</b> from <b><i>$tblName Table</i></b>.";
    }

    $log = [
        'log_act' => $pageAction,
        'cdate'   => $cdate,
        'ctime'   => $ctime,
        'uid'     => USER_ID,
        'cby'     => USER_ID,
        'act_msg' => $viewActMsg,
        'page'    => $pageTitle,
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
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                                                                                                                    echo displayPageAction($act, $pageTitle);
                                                                                                                    ?>
            </p>

        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="MCRForm" method="post" action="" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                            <?php
                        echo displayPageAction($act, $pageTitle);
                        ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="mcr_trans_id_lbl" for="mcr_trans_id">Merchant
                                    ID</label>
                                <p>
                                    <input class="form-control" type="text" name="mcr_trans_id" id="mcr_trans_id"
                                        disabled value="<?php echo $trans_id ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="mcr_date_label" for="mcr_date">Date<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="date" name="mcr_date" id="mcr_date" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['date']) && !isset($mcr_date)) {
                                                                                                                echo $row['date'];
                                                                                                            } else if (isset($mcr_date)) {
                                                                                                                echo $mcr_date;
                                                                                                            } else {
                                                                                                                echo date('Y-m-d');
                                                                                                            }
                                                                                                            ?>"
                                    placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($date_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $date_err; ?></span>
                                </div>
                                <?php } ?>

                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="mcr_currency_lbl"
                                    for="mcr_currency">Currency<span class="requireRed">*</span></label>
                                <select class="form-select" id="mcr_currency" name="mcr_currency"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                    <option value="0" disabled selected>Select Currency</option>
                                    <?php
                                if ($cur_list_result->num_rows >= 1) {
                                    $cur_list_result->data_seek(0);
                                    while ($row2 = $cur_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted, $row['currency']) && (!isset($mcr_curr))) {
                                            $selected = $row['currency'] == $row2['id'] ? "selected" : "";
                                        } else if (isset($mcr_curr)) {
                                            $selected = $mcr_curr == $row2['id'] ? "selected" : "";
                                        }
                                        echo "<option value=\"" . $row2['id'] . "\" $selected>" . $row2['unit'] . "</option>";
                                    }
                                } else {
                                    echo "<option value=\"0\">None</option>";
                                }
                                ?>
                                </select>

                                <?php if (isset($curr_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $curr_err; ?></span>
                                </div>
                                <?php } ?>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="mcr_amt_lbl" for="mcr_amt">Amount<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="mcr_amt" id="mcr_amt" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['amount']) && !isset($mcr_amt)) {
                                                                                                            echo $row['amount'];
                                                                                                        } else if (isset($mcr_amt)) {
                                                                                                            echo $mcr_amt;
                                                                                                        }
                                                                                                        ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($amt_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $amt_err; ?></span>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="mcr_remark_lbl" for="mcr_remark">Remark</label>
                        <textarea class="form-control" name="mcr_remark" id="mcr_remark" rows="3"
                            <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
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
    <?php include "../js/merchant_comm.js" ?>
    </script>

</body>

</html>