<?php
$pageTitle = "Cash On Hand Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = CAONHD;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

$redirect_page = $SITEURL . '/finance/cash_on_hand_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/cash_on_hand/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);

    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
        $trans_id = $row['transactionID'];
    } else {
        // If $rst is false or no data found ($act==null)
        $errorExist = 1;
        $_SESSION['tempValConfirmBox'] = true;
        $act = "F";
    }
} else { //add transaction
    // generate transaction id
    $currentDate = date('Ymd');

    $query = "SELECT MAX(id) AS max_id FROM " . $tblName  . " LIMIT 1";
    $result = mysqli_query($finance_connect, $query);
    $maxRow = mysqli_fetch_assoc($result);
    $maxRowId = $maxRow['max_id'];

    if ($maxRowId === null) {
        $nextRowId = str_pad(1, 5, '0', STR_PAD_LEFT);
    } else {
        $nextRowId = str_pad($maxRowId + 1, 5, '0', STR_PAD_LEFT);
    }

    $trans_id = "CAONHD{$currentDate}{$nextRowId}";
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for currency
$pic_list_result = getData('*', '', '', USR_USER, $connect);
$cur_list_result = getData('*', '', '', CUR_UNIT, $connect);
$bank_list_result = getData('*', '', '', BANK, $connect);

if (post('actionBtn')) {
    $action = post('actionBtn');

    $coh_type = postSpaceFilter("coh_type");
    $coh_date = postSpaceFilter("coh_date");
    $coh_pic = postSpaceFilter("coh_pic_hidden");
    $coh_bank = postSpaceFilter("coh_bank");
    $coh_curr = postSpaceFilter('coh_currency');
    $coh_amt = postSpaceFilter('coh_amt');
    $coh_desc = postSpaceFilter('coh_desc');
    $coh_remark = postSpaceFilter('coh_remark');
    $coh_prev_amt = 0;
    $coh_final_amt = 0;
    $coh_amt = floatval(str_replace(',', '', $coh_amt));
    $coh_attach = null;
    if (isset($_FILES["coh_attach"]) && $_FILES["coh_attach"]["size"] != 0) {
        $coh_attach = $_FILES["coh_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $coh_attach = $_POST['existing_attachment'];
    }



    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["coh_attach"]["size"] != 0) {
                // move file
                $coh_file_name = $_FILES["coh_attach"]["name"];
                $coh_file_tmp_name = $_FILES["coh_attach"]["tmp_name"];
                $img_ext = pathinfo($coh_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $trans_id . '_*.' . $img_ext);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (preg_match('/' . preg_quote($trans_id, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $trans_id . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($coh_file_tmp_name, $img_path . $new_file_name)) {
                        $coh_attach = $new_file_name; // Update $coh_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (!$coh_type && $coh_type < 1) {
                $type_err = "Please specify the type of transaction.";
                break;
            } else if (!$coh_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$coh_pic && $coh_pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if (!$coh_bank && $coh_bank < 1) {
                $bank_err = "Please specify the bank.";
                break;
            } else if (!$coh_curr && $coh_curr < 1) {
                $curr_err = "Please specify the currency.";
                break;
            } else if (!$coh_amt) {
                $amt_err = "Please specify the amount.";
                break;
            } else if (!$coh_desc) {
                $desc_err = "Please specify the description.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //get final_amt from prev row
                    $query = "SELECT
                    final_amt,
                    LAG(final_amt) OVER (ORDER BY id DESC) AS prev_final_amt
                    FROM
                        " . $tblName  . "
                    WHERE
                        bank = '$coh_bank'
                        AND currency = '$coh_curr'
                        AND pic = '$coh_pic'
                    ORDER BY
                        id DESC
                    LIMIT 1";

                    $result = mysqli_query($finance_connect, $query);

                    if (!$result) {
                        die("Query failed: " . mysqli_error($finance_connect));
                    }

                    $prev_row = mysqli_fetch_assoc($result);

                    if (isset($prev_row['final_amt'])) {
                        $coh_prev_amt = $prev_row['final_amt'];
                    } else {
                        $coh_prev_amt = 0;
                    }

                    if ($coh_type == 'Add') {
                        $coh_final_amt = number_format($coh_prev_amt + $coh_amt, 2, '.', '');
                    } else if ($coh_type == 'Deduct') {
                        $coh_final_amt = number_format($coh_prev_amt - $coh_amt, 2, '.', '');
                    }
                    //check values
                    if ($coh_type) {
                        array_push($newvalarr, $coh_type[0]);
                        array_push($datafield, 'type');
                    }

                    if ($coh_date) {
                        array_push($newvalarr, $coh_date);
                        array_push($datafield, 'date');
                    }

                    if ($coh_pic) {
                        array_push($newvalarr, $coh_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($coh_bank) {
                        array_push($newvalarr, $coh_bank);
                        array_push($datafield, 'bank');
                    }

                    if ($coh_curr) {
                        array_push($newvalarr, $coh_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($coh_amt) {
                        array_push($newvalarr, $coh_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($coh_desc) {
                        array_push($newvalarr, $coh_desc);
                        array_push($datafield, 'description');
                    }

                    if ($coh_attach) {
                        array_push($newvalarr, $coh_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($coh_remark) {
                        array_push($newvalarr, $coh_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(transactionID,type,pic,date,bank,currency,amount,prev_amt,final_amt,description,remark,attachment,create_by,create_date,create_time) VALUES ('$trans_id','$coh_type','$coh_pic','$coh_date','$coh_bank','$coh_curr','$coh_amt','$coh_prev_amt','$coh_final_amt','$coh_desc','$coh_remark','$coh_attach','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['type'] != $coh_type) {
                        array_push($oldvalarr, $row['type']);
                        array_push($chgvalarr, $coh_type);
                        array_push($datafield, 'type');
                    }
                    if ($row['date'] != $coh_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $coh_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['pic'] != $coh_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $coh_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['bank'] != $coh_bank) {
                        array_push($oldvalarr, $row['bank']);
                        array_push($chgvalarr, $coh_bank);
                        array_push($datafield, 'bank');
                    }

                    if ($row['currency'] != $coh_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $coh_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($row['amount'] != $coh_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $coh_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($row['prev_amt'] != $coh_prev_amt) {
                        array_push($oldvalarr, $row['prev_amt']);
                        array_push($chgvalarr, $coh_prev_amt);
                        array_push($datafield, 'prev_amt');
                    }

                    if ($row['final_amt'] != $coh_final_amt) {
                        array_push($oldvalarr, $row['final_amt']);
                        array_push($chgvalarr, $coh_final_amt);
                        array_push($datafield, 'final_amt');
                    }

                    if ($row['description'] != $coh_desc) {
                        array_push($oldvalarr, $row['description']);
                        array_push($chgvalarr, $coh_desc);
                        array_push($datafield, 'description');
                    }

                    $coh_attach = isset($coh_attach) ? $coh_attach : '';
                    if (($row['attachment'] != $coh_attach) && ($coh_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $coh_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $coh_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $coh_remark == '' ? 'Empty Value' : $coh_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        //get final_amt from prev row
                        $query = "SELECT
                        final_amt,
                        LAG(final_amt) OVER (ORDER BY id DESC) AS prev_final_amt
                        FROM
                            " . $tblName  . "
                        WHERE
                            bank = '$coh_bank'
                            AND currency = '$coh_curr'
                            AND pic = '$coh_pic'
                            AND id < '$dataID'
                            AND `status` != 'D'
                        ORDER BY
                            id DESC
                        LIMIT 1";

                        $result = mysqli_query($finance_connect, $query);

                        if (!$result) {
                            die("Query failed: " . mysqli_error($finance_connect));
                        }

                        $prev_row = mysqli_fetch_assoc($result);

                        if (isset($prev_row['final_amt'])) {
                            $coh_prev_amt = $prev_row['final_amt'];
                        } else {
                            $coh_prev_amt = 0;
                        }

                        if ($coh_type == 'Add') {
                            $coh_final_amt = number_format($coh_prev_amt + $coh_amt, 2, '.', '');
                        } else if ($coh_type == 'Deduct') {
                            $coh_final_amt = number_format($coh_prev_amt - $coh_amt, 2, '.', '');
                        }

                        $query = "UPDATE " . $tblName  . " SET type = '$coh_type', date = '$coh_date', pic = '$coh_pic', bank = '$coh_bank', currency = '$coh_curr', amount = '$coh_amt', prev_amt = '$coh_prev_amt', final_amt = '$coh_final_amt', description = '$coh_desc', attachment ='$coh_attach', remark ='$coh_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);

                        updateTransAmt($finance_connect, $tblName, ['pic', 'bank', 'currency'], ['pic', 'bank', 'currency']);
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            $trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord($tblName, $dataID, $trans_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
    updateTransAmt($finance_connect, $tblName, ['pic', 'bank', 'currency'], ['pic', 'bank', 'currency']);
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $trans_id = isset($dataExisted) ? $row['transactionID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['transactionID'] . "</b> from <b><i>$tblName Table</i></b>.";
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
    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                                                                                                                    echo displayPageAction($act, 'Transaction');
                                                                                                                    ?>
        </p>

    </div>

    <div id="CAOHFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="CAOHForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, 'Transaction');
                        ?>
                    </h2>
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="coh_trans_id_lbl" for="coh_trans_id">Transaction
                                ID</label>
                            <p>
                                <input class="form-control" type="text" name="coh_trans_id" id="coh_trans_id" disabled value="<?php echo $trans_id ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="coh_type_label" for="coh_type">Type
                                <span class="requireRed">*</span></label>
                            <select class="form-select" name="coh_type" id="coh_type" required <?php if ($act == '') echo 'disabled' ?>>
                                <option disabled selected>Select transaction type</option>
                                <option value="Add" <?php
                                                    if (isset($dataExisted, $row['type'])  && $row['type'] == 'Add'  && (!isset($coh_type) ||  $coh_type == 'Add')) {
                                                        echo "selected";
                                                    } else {
                                                        echo "";
                                                    }

                                                    ?>>
                                    Add</option>
                                <option value="Deduct" <?php
                                                        if (isset($dataExisted, $row['type']) && $row['type'] == 'Deduct' && (!isset($coh_type) || $coh_type == 'Deduct')) {
                                                            echo "selected";
                                                        } else {
                                                            echo "";
                                                        }

                                                        ?>>
                                    Deduct</option>
                            </select>
                            <?php if (isset($type_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $type_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="coh_date_label" for="coh_date">Date<span class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="coh_date" id="coh_date" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['date']) && !isset($coh_date)) {
                                                                                                                echo $row['date'];
                                                                                                            } else if (isset($coh_date)) {
                                                                                                                echo $coh_date;
                                                                                                            } else {
                                                                                                                echo date('Y-m-d');
                                                                                                            }
                                                                                                            ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($date_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $date_err; ?></span>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-3 autocomplete">
                            <label class="form-label form_lbl" id="coh_pic_lbl" for="coh_pic">Person-In-Charge<span class="requireRed">*</span></label>
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
                            <input class="form-control" type="text" name="coh_pic" id="coh_pic" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : ''  ?>">
                            <input type="hidden" name="coh_pic_hidden" id="coh_pic_hidden" value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">


                            <?php if (isset($pic_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $pic_err; ?></span>
                                </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-3">
                            <label class="form-label form_lbl" id="coh_bank_lbl" for="coh_bank">Bank<span class="requireRed">*</span></label>
                            <select class="form-select" id="coh_bank" name="coh_bank" <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Bank</option>
                                <?php
                                if ($bank_list_result->num_rows >= 1) {
                                    $bank_list_result->data_seek(0);
                                    while ($row3 = $bank_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted, $row['bank']) && (!isset($coh_bank))) {
                                            $selected = $row['bank'] == $row3['id'] ? "selected" : "";
                                        } else if (isset($coh_bank)) {
                                            $selected = $coh_curr == $row3['id'] ? "selected" : "";
                                        }
                                        echo "<option value=\"" . $row3['id'] . "\" $selected>" . $row3['name'] . "</option>";
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
                        <div class="col-md-3">
                            <label class="form-label form_lbl" id="coh_currency_lbl" for="coh_currency">Currency<span class="requireRed">*</span></label>
                            <select class="form-select" id="coh_currency" name="coh_currency" <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Currency</option>
                                <?php
                                if ($cur_list_result->num_rows >= 1) {
                                    $cur_list_result->data_seek(0);
                                    while ($row2 = $cur_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted, $row['currency']) && (!isset($coh_curr))) {
                                            $selected = $row['currency'] == $row2['id'] ? "selected" : "";
                                        } else if (isset($coh_curr)) {
                                            $selected = $coh_curr == $row2['id'] ? "selected" : "";
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
                        <div class="col-md-3">
                            <label class="form-label form_lbl" id="coh_amt_lbl" for="coh_amt">Amount<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="coh_amt" id="coh_amt" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['amount']) && !isset($coh_amt)) {
                                                                                                            echo $row['amount'];
                                                                                                        } else if (isset($coh_amt)) {
                                                                                                            echo $coh_amt;
                                                                                                        }
                                                                                                        ?>" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($amt_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $amt_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="coh_desc_lbl" for="coh_desc">Description*</label>
                    <input class="form-control" type="text" name="coh_desc" id="coh_desc" value="<?php
                                                                                                    if (isset($dataExisted) && isset($row['description']) && !isset($coh_desc)) {
                                                                                                        echo $row['description'];
                                                                                                    } else if (isset($coh_desc)) {
                                                                                                        echo $coh_desc;
                                                                                                    }
                                                                                                    ?>" <?php if ($act == '') echo 'disabled' ?>>
                    <?php if (isset($desc_err)) { ?>
                        <div id="err_msg">
                            <span class="mt-n1"><?php echo $desc_err; ?></span>
                        </div>
                    <?php } ?>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="coh_remark_lbl" for="coh_remark">Remark</label>
                    <textarea class="form-control" name="coh_remark" id="coh_remark" rows="3" <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="coh_attach_lbl" for="coh_attach">Attachment</label>
                            <input class="form-control" type="file" name="coh_attach" id="coh_attach" value="" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($err2)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $err2; ?></span>
                                </div>
                            <?php } ?>
                            <?php if (isset($row['attachment']) && $row['attachment']) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?></span>
                                </div>
                                <input type="hidden" name="existing_attachment" value="<?php echo htmlspecialchars($row['attachment']); ?>">
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <?php
                                $attachmentSrc = '';
                                if (isset($row['attachment']))
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                ?>
                                <img id="coh_attach_preview" name="coh_attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="coh_attachmentValue" value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
                            </div>
                        </div>
                    </div>
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
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
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

        <?php include "../js/cash_on_hand_trans.js" ?>
    </script>

</body>

</html>