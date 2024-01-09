<?php
$pageTitle = "Current Bank Account Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = CURR_BANK_TRANS;

$row_id = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/curr_bank_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/current_bank_account/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($row_id) { //edit/remove/view
    $rst = getData('*', "id = '$row_id'", 'LIMIT 1', $tblName, $finance_connect);

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

    //format "CBA+YEARMONTHDATE+00001"
    $trans_id = "CBA{$currentDate}{$nextRowId}";
}

if (!($row_id) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for currency
$cur_list_result = getData('*', '', '', CUR_UNIT, $connect);

//dropdown list for bank
$bank_list_result = getData('*', '', '', BANK, $connect);

if (post('actionBtn')) {
    $cba_type = postSpaceFilter("cba_type");
    $cba_date = postSpaceFilter("cba_date");
    $cba_bank = postSpaceFilter('cba_bank');
    $cba_curr = postSpaceFilter('cba_currency');
    $cba_amt = postSpaceFilter('cba_amt');

    $cba_attach = null;
    if (isset($_FILES["cba_attach"]) && $_FILES["cba_attach"]["size"] != 0) {
        $cba_attach = $_FILES["cba_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $cba_attach = $_POST['existing_attachment'];
    }

    $cba_prev_amt = 0;
    $cba_final_amt = 0;
    $cba_remark = postSpaceFilter('cba_remark');
    $action = post('actionBtn');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["cba_attach"]["size"] != 0) {
                // move file
                $cba_file_name = $_FILES["cba_attach"]["name"];
                $cba_file_tmp_name = $_FILES["cba_attach"]["tmp_name"];
                $img_ext = pathinfo($cba_file_name, PATHINFO_EXTENSION);
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
                    if (move_uploaded_file($cba_file_tmp_name, $img_path . $new_file_name)) {
                        $cba_attach = $new_file_name; // Update $cba_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (!$cba_type && $cba_type < 1) {
                $type_err = "Please specify the type of transaction.";
                break;
            } else if (!$cba_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$cba_bank && $cba_bank < 1) {
                $bank_err = "Please specify the bank.";
                break;
            } else if (!$cba_curr && $cba_curr < 1) {
                $curr_err = "Please specify the currency.";
                break;
            } else if (!$cba_amt) {
                $amt_err = "Please specify the amount.";
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
                        bank = '$cba_bank'
                        AND currency = '$cba_curr'
                    ORDER BY
                        id DESC
                    LIMIT 1";

                    $result = mysqli_query($finance_connect, $query);

                    if (!$result) {
                        die("Query failed: " . mysqli_error($finance_connect));
                    }

                    $prev_row = mysqli_fetch_assoc($result);

                    if (isset($prev_row['final_amt'])) {
                        $cba_prev_amt = $prev_row['final_amt'];
                    } else {
                        $cba_prev_amt = 0;
                    }

                    $cba_amt = floatval(str_replace(',', '', $cba_amt));

                    if ($cba_type == 'Add') {
                        $cba_final_amt = number_format($cba_prev_amt + $cba_amt, 2, '.', '');
                    } else if ($cba_type == 'Deduct') {
                        $cba_final_amt = number_format($cba_prev_amt - $cba_amt, 2, '.', '');
                    }

                    // check value
                    if ($cba_type) {
                        array_push($newvalarr, $cba_type[0]);
                        array_push($datafield, 'type');
                    }

                    if ($cba_date) {
                        array_push($newvalarr, $cba_date);
                        array_push($datafield, 'date');
                    }

                    if ($cba_bank) {
                        array_push($newvalarr, $cba_bank);
                        array_push($datafield, 'bank');
                    }

                    if ($cba_curr) {
                        array_push($newvalarr, $cba_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($cba_final_amt) {
                        array_push($newvalarr, $cba_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($cba_attach) {
                        array_push($newvalarr, $cba_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($cba_prev_amt) {
                        array_push($newvalarr, $cba_prev_amt);
                        array_push($datafield, 'prev_amt');
                    }

                    if ($cba_final_amt) {
                        array_push($newvalarr, $cba_final_amt);
                        array_push($datafield, 'final_amt');
                    }

                    if ($cba_remark) {
                        array_push($newvalarr, $cba_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(transactionID,type,date,bank,currency,amount,prev_amt,final_amt,attachment,remark,create_by,create_date,create_time) VALUES ('$trans_id','$cba_type','$cba_date','$cba_bank','$cba_curr','$cba_amt','$cba_prev_amt','$cba_final_amt','$cba_attach','$cba_remark','" . USER_ID . "',curdate(),curtime())";
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
                    $rst = getData('*', "id = '$row_id'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['type'] != $cba_type) {
                        array_push($oldvalarr, $row['type']);
                        array_push($chgvalarr, $cba_type);
                        array_push($datafield, 'type');
                    }

                    if ($row['date'] != $cba_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $cba_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['bank'] != $cba_bank) {
                        array_push($oldvalarr, $row['bank']);
                        array_push($chgvalarr, $cba_bank);
                        array_push($datafield, 'bank');
                    }

                    if ($row['currency'] != $cba_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $cba_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($row['amount'] != $cba_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $cba_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($row['prev_amt'] != $cba_prev_amt) {
                        array_push($oldvalarr, $row['prev_amt']);
                        array_push($chgvalarr, $cba_prev_amt);
                        array_push($datafield, 'prev_amt');
                    }

                    if ($row['final_amt'] != $cba_final_amt) {
                        array_push($oldvalarr, $row['final_amt']);
                        array_push($chgvalarr, $cba_final_amt);
                        array_push($datafield, 'final_amt');
                    }


                    $cba_attach = isset($cba_attach) ? $cba_attach : '';
                    if (($row['attachment'] != $cba_attach) && ($cba_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $cba_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $cba_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $cba_remark == '' ? 'Empty Value' : $cba_remark);
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
                            bank = '$cba_bank'
                            AND currency = '$cba_curr'
                            AND id < '$row_id'
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
                            $cba_prev_amt = $prev_row['final_amt'];
                        } else {
                            $cba_prev_amt = 0;
                        }

                        if ($cba_type == 'Add') {
                            $cba_final_amt = number_format($cba_prev_amt + $cba_amt, 2, '.', '');
                        } else if ($cba_type == 'Deduct') {
                            $cba_final_amt = number_format($cba_prev_amt - $cba_amt, 2, '.', '');
                        }

                        $query = "UPDATE " . $tblName  . " SET type = '$cba_type',date = '$cba_date',bank = '$cba_bank', currency = '$cba_curr',amount = '$cba_amt', prev_amt ='$cba_prev_amt', final_amt ='$cba_final_amt', attachment ='$cba_attach', remark ='$cba_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$row_id'";
                        $returnData = mysqli_query($finance_connect, $query);

                        updateTransAmt($finance_connect, $tblName, ['bank', 'currency'], ['bank', 'currency']);
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
                    $log['act_msg'] = actMsgLog($row_id, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
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

            $row_id = $row['id'];
            $trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord($tblName, $row_id, $trans_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
    updateTransAmt($finance_connect, $tblName, ['bank', 'currency'], ['bank', 'currency']);
}

//view
if (($row_id) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $trans_id = isset($dataExisted) ? $row['transactionID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $row_id . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $row_id . "</b> ] <b>" . $row['transactionID'] . "</b> from <b><i>$tblName Table</i></b>.";
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
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php
                echo displayPageAction($act, 'Transaction');
                ?>
            </p>
        </div>

        <div id="CBAFormContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="CBAForm" method="post" action="" enctype="multipart/form-data">
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
                                <label class="form-label form_lbl" id="cba_trans_id_lbl" for="cba_trans_id">Transaction
                                    ID</label>
                                <p>
                                    <input class="form-control" type="text" name="cba_trans_id" id="cba_trans_id" disabled value="<?php echo $trans_id ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form_lbl" id="cba_type_label" for="cba_type">Type
                                    <span class="requireRed">*</span></label>
                                <select class="form-select" name="cba_type" id="cba_type" required <?php if ($act == '') echo 'disabled' ?>>
                                    <option disabled selected>Select transaction type</option>
                                    <option value="Add" <?php
                                                        if (isset($dataExisted, $row['type'])  && $row['type'] == 'Add'  && (!isset($cba_type) ||  $cba_type == 'Add')) {
                                                            echo "selected";
                                                        } else {
                                                            echo "";
                                                        }

                                                        ?>>
                                        Add</option>
                                    <option value="Deduct" <?php
                                                            if (isset($dataExisted, $row['type']) && $row['type'] == 'Deduct' && (!isset($cba_type) || $cba_type == 'Deduct')) {
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
                                <label class="form-label form_lbl" id="cba_date_label" for="cba_date">Date<span class="requireRed">*</span></label>
                                <input class="form-control" type="date" name="cba_date" id="cba_date" value="<?php
                                                                                                                if (isset($dataExisted) && isset($row['date']) && !isset($cba_date)) {
                                                                                                                    echo $row['date'];
                                                                                                                } else if (isset($cba_date)) {
                                                                                                                    echo $cba_date;
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
                            <div class="col-md-4">
                                <label class="form-label form_lbl" id="cba_bank_lbl" for="cba_bank">Bank<span class="requireRed">*</span></label>
                                <select class="form-select" id="cba_bank" name="cba_bank" <?php if ($act == '') echo 'disabled' ?>>
                                    <option value="0" disabled selected>Select Bank</option>
                                    <?php
                                    if ($bank_list_result->num_rows >= 1) {
                                        $bank_list_result->data_seek(0);
                                        while ($row3 = $bank_list_result->fetch_assoc()) {
                                            $selected = "";
                                            if (isset($dataExisted, $row['bank']) && !isset($cba_bank)) {
                                                $selected = $row['bank'] == $row3['id'] ? " selected" : "";
                                            } else if (isset($cba_bank)) {
                                                $selected = $cba_bank == $row3['id'] ? " selected" : "";
                                            }
                                            echo "<option value=\"" . $row3['id'] . "\"$selected>" . $row3['name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=\"0\">None</option>";
                                    }

                                    ?>
                                </select>

                                <?php if (isset($bank_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $bank_err; ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form_lbl" id="cba_currency_lbl" for="cba_currency">Currency<span class="requireRed">*</span></label>
                                <select class="form-select" id="cba_currency" name="cba_currency" <?php if ($act == '') echo 'disabled' ?>>
                                    <option value="0" disabled selected>Select Currency</option>
                                    <?php
                                    if ($cur_list_result->num_rows >= 1) {
                                        $cur_list_result->data_seek(0);
                                        while ($row2 = $cur_list_result->fetch_assoc()) {
                                            $selected = "";
                                            if (isset($dataExisted, $row['currency']) && (!isset($cba_curr))) {
                                                $selected = $row['currency'] == $row2['id'] ? "selected" : "";
                                            } else if (isset($cba_curr)) {
                                                list($cba_curr_id, $cba_curr_unit) = explode(':', $cba_curr);
                                                $selected = $cba_curr == $row2['id'] ? "selected" : "";
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
                            <div class="col-md-4">
                                <label class="form-label form_lbl" id="cba_amt_lbl" for="cba_amt">Amount<span class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="cba_amt" id="cba_amt" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['amount']) && !isset($cba_amt)) {
                                                                                                                echo $row['amount'];
                                                                                                            } else if (isset($cba_amt)) {
                                                                                                                echo $cba_amt;
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
                        <label class="form-label form_lbl" id="cba_remark_lbl" for="cba_remark">Transaction Remark</label>
                        <textarea class="form-control" name="cba_remark" id="cba_remark" rows="3" <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label form_lbl" id="cba_attach_lbl" for="cba_attach">Attachment</label>
                                <input class="form-control" type="file" name="cba_attach" id="cba_attach" value="" <?php if ($act == '') echo 'disabled' ?>>
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
                                    <img id="cba_attach_preview" name="cba_attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                    <input type="hidden" name="cba_attachmentValue" value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
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
        <?php include "../js/curr_bank_trans.js" ?>
        
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);
    </script>

</body>

</html>