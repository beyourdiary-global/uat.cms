<?php
$pageTitle = "Investment Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$row_id = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/investment_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/investment/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($row_id) { //edit/remove/view
    $rst = getData('*', "id = '$row_id'", INV_TRANS, $finance_connect);
    
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

    $query = "SELECT MAX(id) AS max_id FROM " . INV_TRANS . " LIMIT 1";
    $result = mysqli_query($finance_connect, $query);
    $maxRow = mysqli_fetch_assoc($result);
    $maxRowId = $maxRow['max_id'];
    
    if ($maxRowId === null) {
        $nextRowId = str_pad(1, 5, '0', STR_PAD_LEFT);
    } else {
        $nextRowId = str_pad($maxRowId + 1, 5, '0', STR_PAD_LEFT);
    }

    //format "IVS+YEARMONTHDATE+00001"
    $trans_id = "IVS{$currentDate}{$nextRowId}";
}

if (!($row_id) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for merchant
$mrcht_list_result = getData('*', '', MERCHANT, $finance_connect);


if (post('actionBtn')) {
    $ivs_type = postSpaceFilter("ivs_type");
    $ivs_date = postSpaceFilter("ivs_date");
    $ivs_mrcht = postSpaceFilter('ivs_mrcht');
    $mrcht_other = postSpaceFilter('mrcht_other');
    $ivs_amt = postSpaceFilter('ivs_amt');
    $ivs_attach = null;

    if (isset($_FILES["ivs_attach"]) && $_FILES["ivs_attach"]["size"] != 0) {
        $ivs_attach = $_FILES["ivs_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $ivs_attach = $_POST['existing_attachment'];
    }
    
    $ivs_prev_amt = 0;
    $ivs_final_amt = 0;
    $ivs_remark = postSpaceFilter('ivs_remark');
    $action = post('actionBtn');
    $isDuplicateMerchant = false;

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["ivs_attach"]["size"] != 0) {
                // move file
                $ivs_file_name = $_FILES["ivs_attach"]["name"];
                $ivs_file_tmp_name = $_FILES["ivs_attach"]["tmp_name"];
                $img_ext = pathinfo($ivs_file_name, PATHINFO_EXTENSION);
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
                    if (move_uploaded_file($ivs_file_tmp_name, $img_path . $new_file_name)) {
                        $ivs_attach = $new_file_name; // Update $ivs_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (($ivs_mrcht == 'other') && !isset($mrcht_other)) {
                $mrcht_other_err = "Merchant name is required!";
                break;
            } else if(($ivs_mrcht == 'other') && isDuplicateRecord("name", $mrcht_other, MERCHANT, $finance_connect, '')) {
                $mrcht_other_err = "Duplicate record found for Merchant name.";
                $isDuplicateMerchant = true;
                break;
            }else if (!$ivs_type && $ivs_type < 1) {
                $type_err = "Please select the type of transaction.";
                break;
            } else if (!$ivs_date) {
                $date_err = "Please seelct the date.";
                break;
            } else if (!$ivs_mrcht && $ivs_mrcht < 1) {
                $mrcht_err = "Please select a merchant.";
                //check user selection for merchant dropdown list
                break;
            } else if (!$ivs_amt) {
                $amt_err = "Please enter the amount.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    if (($ivs_mrcht == 'other') && !($isDuplicateMerchant)) {
                        try {
                            $ivs_mrcht = insertNewMerchant($mrcht_other, USER_ID, $finance_connect);
                        }catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }                     
                    }
                    //get final_amt from prev row
                    $query = "SELECT
                    final_amt,
                    LAG(final_amt) OVER (ORDER BY id DESC) AS prev_final_amt
                    FROM
                        " . INV_TRANS . "
                    WHERE
                        merchant = '$ivs_mrcht'
                    ORDER BY
                        id DESC
                    LIMIT 1";
            
                    $result = mysqli_query($finance_connect, $query);
                    
                    if (!$result) {
                        die("Query failed: " . mysqli_error($finance_connect));
                    }

                    $prev_row = mysqli_fetch_assoc($result);

                    if (isset($prev_row['final_amt'])) {
                        $ivs_prev_amt = $prev_row['final_amt'];
                    } else {
                        $ivs_prev_amt = 0; 
                    }
                    
                    $ivs_amt = floatval(str_replace(',', '', $ivs_amt));

                    if ($ivs_type == 'Add') {
                        $ivs_final_amt = number_format($ivs_prev_amt + $ivs_amt, 2, '.', '');
                    } else if ($ivs_type == 'Deduct') {
                        $ivs_final_amt = number_format($ivs_prev_amt - $ivs_amt, 2, '.', '');
                    }

                    $newvalarr = array();
                    // check value
                    if ($ivs_type)
                    array_push($newvalarr, $ivs_type[0]);

                    if ($ivs_date)
                    array_push($newvalarr, $ivs_date);

                    if ($ivs_mrcht)
                    array_push($newvalarr, $ivs_mrcht);

                    if ($ivs_final_amt)
                    array_push($newvalarr, $ivs_amt);

                    if ($ivs_attach)
                    array_push($newvalarr, $ivs_attach);

                    if ($ivs_prev_amt)
                    array_push($newvalarr, $ivs_prev_amt);
                
                    if ($ivs_final_amt)
                    array_push($newvalarr, $ivs_final_amt);

                    if ($ivs_remark)
                    array_push($newvalarr, $ivs_remark);

                    $query = "INSERT INTO " . INV_TRANS . "(transactionID,type,date,amount,prev_amt,final_amt,merchant,remarks,attachment,create_by,create_date,create_time) VALUES ('$trans_id','$ivs_type','$ivs_date','$ivs_amt','$ivs_prev_amt','$ivs_final_amt','$ivs_mrcht','$ivs_remark','$ivs_attach','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                    
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            } else {
                try {
                    if (($ivs_mrcht == 'other') && !($isDuplicateMerchant)) {
                        try {
                            $ivs_mrcht = insertNewMerchant($mrcht_other, USER_ID, $finance_connect);
                        }catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }                     
                    }
                    // take old value
                    //$rst = getData('*', "id = '$row_id'", 'LIMIT 1',INV_TRANS, $finance_connect);
                    $rst = getData('*', "id = '$row_id'",INV_TRANS, $finance_connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if ($row['type'] != $ivs_type) {
                        array_push($oldvalarr, $row['type']);
                        array_push($chgvalarr, $ivs_type);
                    }
                    if ($row['date'] != $ivs_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $ivs_date);
                    }
                    if ($row['merchant'] != $ivs_mrcht) {
                        array_push($oldvalarr, $row['merchant']);
                        array_push($chgvalarr, $ivs_mrcht);
                    }
                    if ($row['amount'] != $ivs_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $ivs_amt);
                    }
                    if ($row['prev_amt'] != $ivs_prev_amt) {
                        array_push($oldvalarr, $row['prev_amt']);
                        array_push($chgvalarr, $ivs_prev_amt);
                    }
                    if ($row['final_amt'] != $ivs_final_amt) {
                        array_push($oldvalarr, $row['final_amt']);
                        array_push($chgvalarr, $ivs_final_amt);
                    }

                    $ivs_attach = isset($ivs_attach) ? $ivs_attach : '';
                    if (($row['attachment'] != $ivs_attach) && ($ivs_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $ivs_attach);
                    }

                    if ($row['remarks'] != $ivs_remark) {
                        if ($row['remarks'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remarks'];

                        array_push($oldvalarr, $old_remark);

                        if ($ivs_remark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $ivs_remark;

                        array_push($chgvalarr, $new_remark);
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
                            " . INV_TRANS . "
                        WHERE
                            merchant = '$ivs_mrcht'
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
                            $ivs_prev_amt = $prev_row['final_amt'];
                        } else {
                            $ivs_prev_amt = 0; 
                        }

                        if ($ivs_type == 'Add') {
                            $ivs_final_amt = number_format($ivs_prev_amt + $ivs_amt, 2, '.', '');
                        } else if ($ivs_type == 'Deduct') {
                            $ivs_final_amt = number_format($ivs_prev_amt - $ivs_amt, 2, '.', '');
                        }

                        $query = "UPDATE " . INV_TRANS . " SET type = '$ivs_type',date = '$ivs_date',amount = '$ivs_amt', prev_amt ='$ivs_prev_amt', final_amt ='$ivs_final_amt', merchant = '$ivs_mrcht', attachment ='$ivs_attach', remarks ='$ivs_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$row_id'";
                        $returnData = mysqli_query($finance_connect, $query);

                        updateTransAmt($finance_connect, INV_TRANS, ['merchant'], ['merchant']);
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            }
            
            if (isset($errorMsg)) {
                $act = "F";
                $errorMsg = str_replace('\'', '', $errorMsg);
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
                    'query_table'  => INV_TRANS,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($pageAction == 'Add') {

                    $log['newval'] = implodeWithComma($newvalarr);

                    if (isset($returnData)) {
                        $log['act_msg'] = USER_NAME . " added <b>$trans_id</b> into <b><i>" . INV_TRANS . " Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>$trans_id</b> into <b><i>" . INV_TRANS . " Table</i></b> ( $errorMsg )";
                    }
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($oldvalarr, $chgvalarr, INV_TRANS, (isset($returnData) ? '' : $errorMsg));
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
            //$rst = getData('*', "id = '$id'", 'LIMIT 1', INV_TRANS, $finance_connect);
            $rst = getData('*', "id = '$id'", INV_TRANS, $finance_connect);
            $row = $rst->fetch_assoc();

            $row_id = $row['id'];
            $trans_id = $row['transactionID'];
            $_SESSION['delChk'] = 1;
            //SET the record status to 'D'
            deleteRecord(INV_TRANS, $row_id, $trans_id, $finance_connect, $cdate, $ctime, $pageTitle);
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
        
    }
    //update rows after deletion
    updateTransAmt($finance_connect, INV_TRANS, ['merchant'], ['merchant']);
}

//view
if (($row_id) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $trans_id = isset($dataExisted) ? $row['transactionID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data ";
    } else {
        $viewActMsg = USER_NAME . " viewed the data <b>$trans_id</b> from <b><i>$pageTitle Table</i></b>.";
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
        <p><a href="<?= $redirect_page ?>"><?=$pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
        echo displayPageAction($act, 'Transaction');
        ?></p>

    </div>

    <div id="merchantFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="merchantForm" method="post" action="" enctype="multipart/form-data">
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
                            <label class="form-label form_lbl" id="ivs_trans_id_lbl" for="ivs_trans_id">Transaction
                                ID</label>
                            <p>
                                <input class="form-control" type="text" name="ivs_trans_id" id="ivs_trans_id" disabled
                                    value="<?php echo $trans_id ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="ivs_type_label" for="ivs_type">Type
                                <span class="requireRed">*</span></label>
                            <select class="form-select" name="ivs_type" id="ivs_type" required
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option disabled selected>Select transaction type</option>
                                <option value="Add" <?php
                                        if (isset($dataExisted, $row['type'])  && $row['type'] == 'Add'  && (!isset($ivs_type) ||  $ivs_type == 'Add')) {
                                            echo "selected";
                                        }else {
                                            echo "";
                                        }
                                        
                                    ?>>
                                    Add</option>
                                <option value="Deduct" <?php 
                                        if (isset($dataExisted, $row['type']) && $row['type'] == 'Deduct' && (!isset($ivs_type) || $ivs_type == 'Deduct')) {
                                            echo "selected";
                                        } else {
                                            echo "";
                                        }
                                        
                                    ?>>
                                    Deduct</option>
                            </select>
                            <?php if (isset($type_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $type_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="ivs_date_label" for="ivs_date">Date<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="ivs_date" id="ivs_date" value="<?php 
                                if (isset($dataExisted) && isset($row['date']) && !isset($ivs_date)) {
                                    echo $row['date'];
                                } else if (isset($ivs_date)) {
                                    echo $ivs_date;
                                } else {
                                    echo date('Y-m-d');
                                }
                                ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}"
                                <?php if ($act == '') echo 'disabled' ?>>
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
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="ivs_amt_lbl" for="ivs_amt">Amount<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="ivs_amt" id="ivs_amt" value="<?php 
                                if (isset($dataExisted) && isset($row['amount']) && !isset($ivs_amt)){
                                    echo $row['amount'];
                                }else if (isset($ivs_amt)) {
                                    echo $ivs_amt;
                                }
                                ?>" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($amt_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $amt_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="ivs_mrcht_lbl" for="ivs_mrcht">Merchant<span
                                    class="requireRed">*</span></label>
                            <select class="form-select" id="ivs_mrcht" name="ivs_mrcht"
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Merchant</option>
                                <option value="other" <?php if (isset($ivs_mrcht) && $ivs_mrcht == 'other') echo 'selected'; ?>>Create New Merchant</option>
                                <?php
                                    if ($mrcht_list_result->num_rows >= 1) {
                                        $mrcht_list_result->data_seek(0);
                                        while ($row2 = $mrcht_list_result->fetch_assoc()) {
                                            $selected = "";
                                            if (isset($dataExisted,$row['merchant']) && !isset($ivs_mrcht)) {
                                                $selected = $row['merchant'] == $row2['id'] ? " selected" : "";
                                            }else if (isset($ivs_mrcht) && ($ivs_mrcht != 'other')) {
                                            $selected = $ivs_mrcht == $row2['id'] ? " selected" : "";
                                            }
                                            echo "<option value=\"" . $row2['id'] . "\"$selected>" . $row2['name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=\"0\">None</option>";
                                    }
                                    ?>
                            </select>

                            <?php if (isset($mrcht_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $mrcht_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                <div id="createMerchant" hidden>
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label form_lbl" id="mrcht_other_lbl" for="mrcht_other">Merchant
                                    Name</label>
                                <input class="form-control" type="text" name="mrcht_other" id="mrcht_other"
                                    <?php if ($act == '') echo 'readonly' ?>>
                                <?php if (isset($mrcht_other_err)) {?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $mrcht_other_err; ?></span>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="ivs_remark_lbl" for="ivs_remark">Transaction Remark</label>
                    <textarea class="form-control" name="ivs_remark" id="ivs_remark" rows="3"
                        <?php if ($act == '') echo 'disabled' ?>><?php 
                        if (isset($dataExisted) && isset($row['remarks']) && !isset($ivs_remark))
                            echo $row['remarks'];
                        else if (isset($ivs_remark))
                            echo $ivs_remark;
                        ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="ivs_attach_lbl" for="ivs_attach">Attachment</label>
                            <input class="form-control" type="file" name="ivs_attach" id="ivs_attach" value=""
                                <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($err2)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $err2; ?></span>
                            </div>
                            <?php } ?>
                            <?php if (isset($row['attachment']) && $row['attachment']) {?>
                            <div id="err_msg">
                                <span
                                    class="mt-n1"><?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?></span>
                            </div>
                            <input type="hidden" name="existing_attachment"
                                value="<?php echo htmlspecialchars($row['attachment']); ?>">
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <?php
                                $attachmentSrc = '';
                                if (isset($row['attachment'])) 
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                ?>
                                <img id="ivs_attach_preview" name="ivs_attach_preview"
                                    src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="ivs_attachmentValue"
                                    value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
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
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
        <?php include "../js/inv_trans.js" ?>
    </script>

</body>

</html>