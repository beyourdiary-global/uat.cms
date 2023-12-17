<?php
$pageTitle = "Current Bank Account Transaction";
$isFinance = 1;

include '../menuHeader.php';
$row_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/finance/curr_bank_trans_table.php';

// to display data to input
if ($row_id) { //edit/remove
    
    $rst = getData('*', "id = '$row_id'", CURR_BANK_TRANS, $finance_connect);
    
    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
        $trans_id = $row['transactionID'];
    } else {
        // If $rst is false or no data found ($act==null)
        echo '<script>
                alert("Data not found or an error occurred.");
                window.location.href = "' . $redirect_page . '"; // Redirect to previous page
              </script>';
        exit(); // Stop script execution
    }
} else { //add transaction
    // generate transaction id
    $currentYear = date('Y');
    $currentMonth = date('m');
    $currentDate = date('d');

    $query = "SELECT MAX(id) AS max_id FROM " . CURR_BANK_TRANS;
    $result = mysqli_query($finance_connect, $query);
    $maxRow = mysqli_fetch_assoc($result);
    $maxRowId = $maxRow['max_id'];

    if ($maxRowId === null) {
        $nextRowId = str_pad(1, 5, '0', STR_PAD_LEFT);
    } else {
        $nextRowId = str_pad($maxRowId + 1, 5, '0', STR_PAD_LEFT);
    }

    //format "CBA+YEARMONTHDATE+00001"
    $trans_id = "CBA{$currentYear}{$currentMonth}{$currentDate}{$nextRowId}";

}

if (!($row_id) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for currency
$cur_list_result = getData('*', '', CUR_UNIT, $connect);

// currency unit
$cur_unit_arr = array();
if ($cur_list_result != false) {
    while ($row2 = $cur_list_result->fetch_assoc()) {
        $x = $row2['id'];
        $y = $row2['unit'];
        $cur_unit_arr[$x] = $y;
    }
}

//dropdown list for bank
$bank_list_result = getData('*', '', BANK, $connect);

$bank_arr = array();
if ($bank_list_result != false) {
    while ($row3 = $bank_list_result->fetch_assoc()) {
        $x = $row3['id'];
        $y = $row3['name'];
        $bank_arr[$x] = $y;
    }
}

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            $cba_type = postSpaceFilter("cba_type");
            $cba_date = postSpaceFilter("cba_date");
            $cba_bank = postSpaceFilter('cba_bank');
            $cba_curr = postSpaceFilter('cba_currency');
            $cba_amt = postSpaceFilter('cba_amt');
            $cba_attach = '';
            $cba_prev_amt = 0;
            $cba_final_amt = 0;
            $cba_remark = postSpaceFilter('cba_remark');

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
                        " . CURR_BANK_TRANS . "
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
                    $query = "INSERT INTO " . CURR_BANK_TRANS . "(transactionID,type,date,bank,currency,amount,prev_amt,final_amt,attachment,remark) VALUES ('$trans_id','$cba_type','$cba_date','$cba_bank','$cba_curr','$cba_amt','$cba_prev_amt','$cba_final_amt','helo','$cba_remark')";
                    // Execute the query
                    $queryResult = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                    
                    if ($queryResult) {
                        $newvalarr = array();
                        // check value
                        if ($cba_type != '')
                        array_push($newvalarr, $cba_type[0]);

                        if ($cba_date != '')
                        array_push($newvalarr, $cba_date);

                        if ($cba_bank != '')
                        array_push($newvalarr, $cba_bank);

                        if ($cba_curr != '')
                        array_push($newvalarr, $cba_curr);

                        if ($cba_amt != '')
                        array_push($newvalarr, $cba_amt);

                        if ($cba_attach != '')
                        array_push($newvalarr, $cba_attach);

                        if ($cba_prev_amt != '')
                        array_push($newvalarr, $cba_prev_amt);
                    
                        if ($cba_final_amt != '')
                        array_push($newvalarr, $cba_final_amt);

                        if ($cba_remark != '')
                        array_push($newvalarr, $cba_remark);

                        $newval = implode(",", $newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$trans_id</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = CURR_BANK_TRANS;
                        $log['page'] = 'Merchant';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } else{ // Query failed
                        $act = 'F';
                    }
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$row_id'", CURR_BANK_TRANS, $finance_connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if ($row['type'] != $cba_type) {
                        array_push($oldvalarr, $row['type']);
                        array_push($chgvalarr, $cba_type);
                    }
                    if ($row['date'] != $cba_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $cba_date);
                    }
                    if ($row['bank'] != $cba_bank) {
                        array_push($oldvalarr, $row['bank']);
                        array_push($chgvalarr, $cba_bank);
                    }
                    if ($row['currency'] != $cba_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $cba_curr);
                    }
                    if ($row['amount'] != $cba_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $cba_amt);
                    }
                    if ($row['prev_amt'] != $cba_prev_amt) {
                        array_push($oldvalarr, $row['prev_amt']);
                        array_push($chgvalarr, $cba_prev_amt);
                    }
                    if ($row['final_amt'] != $cba_final_amt) {
                        array_push($oldvalarr, $row['final_amt']);
                        array_push($chgvalarr, $cba_final_amt);
                    }
                    if ($row['attachment'] != $cba_attach) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $cba_attach);
                    }

                    if ($row['remark'] != $cba_remark) {
                        if ($row['remark'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remark'];

                        array_push($oldvalarr, $old_remark);

                        if ($cba_remark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $cba_remark;

                        array_push($chgvalarr, $new_remark);
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;
                    error_log("Old Values Array: " . print_r($oldvalarr, true));

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {

                        //get final_amt from prev row
                        $query = "SELECT
                        final_amt,
                        LAG(final_amt) OVER (ORDER BY id DESC) AS prev_final_amt
                        FROM
                            " . CURR_BANK_TRANS . "
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

                        $query = "UPDATE " . CURR_BANK_TRANS . " SET type = '$cba_type',date = '$cba_date',bank = '$cba_bank', currency = '$cba_curr',amount = '$cba_amt', prev_amt ='$cba_prev_amt', final_amt ='$cba_final_amt', attachment ='helo', remark ='$cba_remark' WHERE id = '$row_id'";
                        $update_result = mysqli_query($finance_connect, $query);

                        if ($update_result) {
                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = USER_ID;

                            $log['act_msg'] = USER_NAME . " edited the data";
                            for ($i = 0; $i < sizeof($oldvalarr); $i++) {
                                if ($i == 0)
                                    $log['act_msg'] .= " from <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
                            }
                            $log['act_msg'] .= "  from <b><i>Merchant Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = CURR_BANK_TRANS;
                            $log['page'] = $pageTitle;
                            $log['oldval'] = $oldval;
                            $log['changes'] = $chgval;
                            $log['connect'] = $connect;
                            audit_log($log);
                            
                        }else{
                            $act = 'F';
                        }
                        updateTransactionAmounts($finance_connect, CURR_BANK_TRANS);
                    } else $act = 'NC';
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            }
            break;
        case 'back':
            echo ("<script>location.href = '$redirect_page';</script>");
            break;
    }
}


if (post('act') == 'D') {
    $id = post('id');
    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", CURR_BANK_TRANS, $finance_connect);
            $row = $rst->fetch_assoc();

            $row_id = $row['id'];
            $trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord(CURR_BANK_TRANS, $row_id, $trans_id, $finance_connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
            
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
        updateTransactionAmounts($finance_connect, CURR_BANK_TRANS);
    }
}

if (!($row_id) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $trans_id = isset($dataExisted) ? $row['transactionID'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$trans_id</b> from <b><i>$pageTitle Table</i></b>.";
    $log['page'] = $pageTitle;
    $log['connect'] = $connect;
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
        <p><a href="<?= $redirect_page ?>"><?=$pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
        echo displayPageAction($act, 'Transaction');
        ?></p>

    </div>


    <div id="merchantFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="merchantForm" method="post" action="">
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
                            <input class="form-control" type="text" name="cba_trans_id" id="cba_trans_id"
                                value="<?php echo $trans_id ?>" <?php echo 'readonly' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="cba_type_label" for="cba_type">Type
                                <span class="requireRed">*</span></label>
                            <select class="form-select" name="cba_type" id="cba_type" required>
                                <option disabled selected>Select transaction type</option>
                                <option value="Add" <?php
                                        if (isset($dataExisted, $row['type'])  && $row['type'] == 'Add'  && (!isset($cba_type) ||  $cba_type == 'Add')) {
                                            echo "selected";
                                        }else {
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
                            <?php if (isset($bank_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $type_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="cba_date_label" for="cba_date">Date<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="cba_date" id="cba_date" value="<?php 
                                if (isset($dataExisted) && isset($row['date']) && !isset($cba_date)) {
                                    echo $row['date'];
                                } else if (isset($cba_date)) {
                                    echo $cba_date;
                                } else {
                                    echo date('Y-m-d');
                                }
                                ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}">
                            <?php if (isset($date_err)) {?>
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
                            <label class="form-label form_lbl" id="cba_bank_lbl" for="cba_bank">Bank<span
                                    class="requireRed">*</span></label>
                            <select class="form-select" id="cba_bank" name="cba_bank"
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Bank</option>
                                <?php
                                    if ($bank_list_result->num_rows >= 1) {
                                        $bank_list_result->data_seek(0);
                                        while ($row3 = $bank_list_result->fetch_assoc()) {
                                            $selected = "";
                                            if (isset($dataExisted,$row['bank']) && !isset($cba_bank)) {
                                                $selected = $row['bank'] == $row3['id'] ? " selected" : "";
                                            }else if (isset($cba_bank)) {
                                            $selected = $cba_bank == $row3['id'] ? " selected" : "";
                                            }
                                            echo "<option value=\"" . $row3['id'] . ":" . $row3['name'] . "\"$selected>" . $row3['name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=\"0\">None</option>";
                                    }
                                    ?>
                            </select>

                            <?php if (isset($bank_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $bank_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="cba_currency_lbl" for="cba_currency">Currency<span
                                    class="requireRed">*</span></label>
                            <select class="form-select" id="cba_currency" name="cba_currency"
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Currency</option>
                                <?php
                                if ($cur_list_result->num_rows >= 1) {
                                    $cur_list_result->data_seek(0);
                                    while ($row2 = $cur_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted,$row['currency']) && (!isset($cba_curr))) {
                                            $selected = $row['currency'] == $row2['id'] ? " selected" : "";
                                        }else if (isset($cba_curr)) {
                                            $selected = $cba_curr == $row2['id'] ? " selected" : "";
                                        }
                                        echo "<option value=\"" . $row2['id'] . ":" . $row2['unit'] . "\"$selected>" . $row2['unit'] . "</option>";
                                        
                                    }
                                } else {
                                    echo "<option value=\"0\">None</option>";
                                }
                            ?>
                            </select>

                            <?php if (isset($curr_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $curr_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="cba_amt_lbl" for="cba_amt">Amount<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="cba_amt" id="cba_amt"
                                value="<?php 
                                if (isset($dataExisted) && isset($row['amount']) && !isset($cba_amt)){
                                    echo $row['amount'];
                                }else if (isset($cba_amt)) {
                                    echo $cba_amt;
                                }else{
                                    echo '';
                                }
                                ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                            <?php if (isset($amt_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $amt_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="cba_attach_lbl" for="cba_attach">Attachment</label>
                            <input class="form-control" type="file" name="cba_attach" id="cba_attach"
                                value="<?php if (isset($dataExisted) && isset($row['amount'])) echo $row['amount'] ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                            
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <img id="cba_attach_preview" name="cba_attach_preview"
                                    src="<?php echo ($row['attachment'] == '' || $row['attachment'] == NULL) ? '../images_server/finance/current_bank_account/' : $img_path . $row['attachment']; ?>"
                                    class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="cba_attachmentValue" value="<?= $row['attachment'] ?>">
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="cba_remark_lbl" for="cba_remark">Transaction Remark</label>
                    <textarea class="form-control" name="cba_remark" id="cba_remark" rows="3"
                        <?php if ($act == '') echo 'readonly' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
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
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn"
                        value="back">Back</button>
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
        echo '<script>confirmationDialog("","","Transaction","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
    $("#cba_type").on("input", function() {
        $(".cba-type-err").remove();
    });

    $("#cba_date").on("input", function() {
        $(".cba-date-err").remove();
    });

    $("#cba_bank").on("input", function() {
        $(".cba-bank-err").remove();
    });

    $("#cba_currency").on("input", function() {
        $(".cba-curr-err").remove();
    });

    $("#cba_amt").on("input", function() {
        $(".cba-amt-err").remove();
    });


    $('.submitBtn').on('click', () => {
        $(".error-message").remove();
        //event.preventDefault();
        var type_chk = 1;
        var date_chk = 1;
        var bank_chk = 1;
        var currency_chk = 1;
        var amt_chk = 1;

        if ($('#cba_type').val() === '' || $('#cba_type').val() === null || $('#cba_type')
            .val() === undefined) {
            type_chk = 0;
            $("#cba_type").after(
                '<span class="error-message cba-type-err">Type is required!</span>');
        } else {
            $(".cba-type-err").remove();
            type_chk = 1;
        }

        if (($('#cba_date').val() === '' || $('#cba_date').val() === null || $('#cba_date')
                .val() === undefined)) {
            date_chk = 0;
            $("#cba_date").after(
                '<span class="error-message cba-date-err">Date is required!</span>');
        } else {
            $(".cba-date-err").remove();
            date_chk = 1;
        }

        if (($('#cba_bank').val() === '' || $('#cba_bank').val() === null || $('#cba_bank')
                .val() === undefined)) {
            bank_chk = 0;
            $("#cba_bank").after(
                '<span class="error-message cba-bank-err">Bank is required!</span>');
        } else {
            $(".cba-bank-err").remove();
            bank_chk = 1;
        }

        if (($('#cba_currency').val() === '' || $('#cba_currency').val() === null || $('#cba_currency')
                .val() === undefined)) {
            currency_chk = 0;
            $("#cba_currency").after(
                '<span class="error-message cba-curr-err">Currency is required!</span>');
        } else {
            $(".cba-curr-err").remove();
            currency_chk = 1;
        }

        if (($('#cba_amt').val() === '' || $('#cba_amt').val() === null || $('#cba_amt')
                .val() === undefined)) {
            amount_chk = 0;
            $("#cba_amt").after(
                '<span class="error-message cba-amt-err">Amount is required!</span>');
        } else {
            $(".cba-amt-err").remove();
            amount_chk = 1;
        }

        if (type_chk == 1 && date_chk == 1 && bank_chk == 1 && currency_chk == 1 && amt_chk == 1)
            $(this).closest('form').submit();
        else
            return false;

    })
    </script>
</body>

</html>