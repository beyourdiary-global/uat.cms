<?php
$pageTitle = "Inventories Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = INVTR_TRANS;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/invtr_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';
$errorMsgAlert = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";

$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");
$img_path = '../' . img_server . 'finance/inventory/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", '', $tblName, $finance_connect);

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

    $query = "SELECT MAX(id) AS max_id FROM " . $tblName . " LIMIT 1";
    $result = mysqli_query($finance_connect, $query);
    $maxRow = mysqli_fetch_assoc($result);
    $maxRowId = $maxRow['max_id'];

    if ($maxRowId === null) {
        $nextRowId = str_pad(1, 5, '0', STR_PAD_LEFT);
    } else {
        $nextRowId = str_pad($maxRowId + 1, 5, '0', STR_PAD_LEFT);
    }

    //format "INVTR+YEARMONTHDATE+00001"
    $trans_id = "INVTR{$currentDate}{$nextRowId}";
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for merchant
//$mrcht_list_result = getData('*', '', '', MERCHANT, $finance_connect);
$item_list_result = getData('*', '', '', PROD, $connect);


if (post('actionBtn')) {
    $action = post('actionBtn');

    $invtr_date = postSpaceFilter("invtr_date");
    $invtr_mrcht = postSpaceFilter('invtr_mrcht_hidden');
    $mrcht_other = postSpaceFilter('invtr_mrcht_other');
    $invtr_item = postSpaceFilter('invtr_item');
    $invtr_unit_price = postSpaceFilter('invtr_unit_price');
    $invtr_bal_qty = postSpaceFilter('invtr_bal_qty');
    $invtr_amt = postSpaceFilter('invtr_amt');
    $invtr_attach = null;
    $invtr_remark = postSpaceFilter('invtr_remark');

    $isDuplicateMerchant = false;

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    if (isset($_FILES["invtr_attach"]) && $_FILES["invtr_attach"]["size"] != 0) {
        $invtr_attach = $_FILES["invtr_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $invtr_attach = $_POST['existing_attachment'];
    }

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["invtr_attach"]["size"] != 0) {
                // move file
                $invtr_file_name = $_FILES["invtr_attach"]["name"];
                $invtr_file_tmp_name = $_FILES["invtr_attach"]["tmp_name"];
                $img_ext = pathinfo($invtr_file_name, PATHINFO_EXTENSION);
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
                    if (move_uploaded_file($invtr_file_tmp_name, $img_path . $new_file_name)) {
                        $invtr_attach = $new_file_name; // Update $invtr_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (($invtr_mrcht == 'Create New Merchant') && !isset($mrcht_other)) {
                $mrcht_other_err = "Merchant is required!";
                break;
            } else if(($invtr_mrcht == 'Create New Merchant') && isDuplicateRecord("name", $mrcht_other, MERCHANT, $finance_connect, '')) {
                $mrcht_other_err = "Duplicate record found for Merchant name.";
                $isDuplicateMerchant = true;
                break;
            } else if (!$invtr_date) {
                $date_err = "Please select the date.";
                break;
            } else if (!$invtr_mrcht && $invtr_mrcht < 1) {
                $mrcht_err = "Please select a merchant.";
                //check user selection for merchant dropdown list
                break;
            } else if (!$invtr_item) {
                $item_err = "Please enter the item.";
                break;
            } else if (!$invtr_amt) {
                $amt_err = "Please enter the amount.";
                break;
            } else if ($action == 'addTransaction') {

                try {
                    if (($invtr_mrcht == 'Create New Merchant') && !($isDuplicateMerchant)) {
                        try {
                            $invtr_mrcht = insertNewMerchant($mrcht_other, USER_ID, $finance_connect);
                            generateDBData(MERCHANT, $finance_connect);
                        }catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }
                    }

                    if ($trans_id) {
                        array_push($newvalarr, $trans_id);
                        array_push($datafield, 'transactionID');
                    }

                    if ($invtr_date) {
                        array_push($newvalarr, $invtr_date);
                        array_push($datafield, 'date');
                    }

                    if ($invtr_mrcht) {
                        array_push($newvalarr, $invtr_mrcht);
                        array_push($datafield, 'merchantID');
                    }

                    if ($invtr_item) {
                        array_push($newvalarr, $invtr_item);
                        array_push($datafield, 'itemID');
                    }

                    if ($invtr_unit_price) {
                        array_push($newvalarr, $invtr_unit_price);
                        array_push($datafield, 'unit_price');
                    }

                    if ($invtr_bal_qty) {
                        array_push($newvalarr, $invtr_bal_qty);
                        array_push($datafield, 'bal_qty');
                    }

                    if ($invtr_amt) {
                        array_push($newvalarr, $invtr_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($invtr_attach) {
                        array_push($newvalarr, $invtr_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($invtr_remark) {
                        array_push($newvalarr, $invtr_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(transactionID,date,merchantID,itemID,unit_price,bal_qty,amount,remark,attachment,create_by,create_date,create_time) VALUES ('$trans_id','$invtr_date','$invtr_mrcht','$invtr_item','$invtr_unit_price','$invtr_bal_qty','$invtr_amt','$invtr_remark','$invtr_attach','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $finance_connect->insert_id;
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if (($invtr_mrcht == 'Create New Merchant') && !($isDuplicateMerchant)) {
                        try {
                            $invtr_mrcht = insertNewMerchant($mrcht_other, USER_ID, $finance_connect);
                        } catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }
                    }
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['date'] != $invtr_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $invtr_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['merchantID'] != $invtr_mrcht) {
                        array_push($oldvalarr, $row['merchantID']);
                        array_push($chgvalarr, $invtr_mrcht);
                        array_push($datafield, 'merchantID');
                    }

                    if ($row['itemID'] != $invtr_item) {
                        array_push($oldvalarr, $row['itemID']);
                        array_push($chgvalarr, $invtr_item);
                        array_push($datafield, 'itemID');
                    }

                    if ($row['unit_price'] != $invtr_unit_price) {
                        array_push($oldvalarr, $row['unit_price']);
                        array_push($chgvalarr, $invtr_unit_price);
                        array_push($datafield, 'unit_price');
                    }

                    if ($row['bal_qty'] != $invtr_bal_qty) {
                        array_push($oldvalarr, $row['bal_qty']);
                        array_push($chgvalarr, $invtr_bal_qty);
                        array_push($datafield, 'bal_qty');
                    }


                    if ($row['amount'] != $invtr_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $invtr_amt);
                        array_push($datafield, 'amount');
                    }

                    $invtr_attach = isset($invtr_attach) ? $invtr_attach : '';
                    if (($row['attachment'] != $invtr_attach) && ($invtr_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $invtr_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $invtr_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $invtr_remark == '' ? 'Empty Value' : $invtr_remark);
                        array_push($datafield, 'remark');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET date = '$invtr_date',amount = '$invtr_amt', merchantID = '$invtr_mrcht', attachment ='$invtr_attach', remark ='$invtr_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);
                        updateTransAmt($finance_connect, $tblName, ['merchant'], ['merchant']);
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
                    $log['act_msg'] = actMsgLog($trans_id, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                }
                audit_log($log);
            }

            break;
        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
    }
}


if ($act == 'D') {
    //SET the record status to 'D'
    deleteRecord($tblName, $dataID, $row['transactionID'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//view
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {
    $trans_id = isset($dataExisted) ? $row['transactionID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $trans_id . "</b> from <b><i>$tblName Table</i></b>.";
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
                                                                                                                    ?></p>

    </div>

    <div id="IVTRFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="IVTRForm" method="post" action="" enctype="multipart/form-data">
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
                            <label class="form-label form_lbl" id="invtr_trans_id_lbl" for="invtr_trans_id">Transaction
                                ID</label>
                            <input class="form-control" type="text" name="invtr_trans_id" id="invtr_trans_id" disabled value="<?php echo $trans_id ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="invtr_date_label" for="invtr_date">Date<span class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="invtr_date" id="invtr_date" value="<?php
                                                                                                                if (isset($dataExisted) && isset($row['date']) && !isset($invtr_date)) {
                                                                                                                    echo $row['date'];
                                                                                                                } else if (isset($invtr_date)) {
                                                                                                                    echo $invtr_date;
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
                        <div class="col-md-4 autocomplete">
                            <label class="form-label form_lbl" id="invtr_mrcht_lbl" for="invtr_mrcht">Merchant<span
                                    class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);

                            if (isset($row['merchantID']))
                                $echoVal = $row['merchantID'];

                            if (isset($echoVal)) {
                                $mrcht_rst = getData('name', "id = '$echoVal'", '', MERCHANT, $finance_connect);
                                if (!$mrcht_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $mrcht_row = $mrcht_rst->fetch_assoc();
                            }

                            ?>
                            <input class="form-control" type="text" name="invtr_mrcht" id="invtr_mrcht"
                                <?php if ($act == '') echo 'readonly' ?>
                                value="<?php echo !empty($echoVal) ? $mrcht_row['name'] : ''  ?>">
                            <input type="hidden" name="invtr_mrcht_hidden" id="invtr_mrcht_hidden"
                                value="<?php echo (isset($row['merchantID'])) ? $row['merchantID'] : ''; ?>">

                            <?php if (isset($mrcht_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $mrcht_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <div class="row">
                        <div id="INVTR_CreateMerchant" hidden>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label form_lbl" id="invtr_mrcht_other_lbl" for="invtr_mrcht_other">Merchant
                                            Name*</label>
                                        <input class="form-control" type="text" name="invtr_mrcht_other" id="invtr_mrcht_other" <?php if ($act == '') echo 'readonly' ?>>
                                        <?php if (isset($mrcht_other_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1"><?php echo $mrcht_other_err; ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="invtr_item_lbl" for="invtr_item">Item<span
                                    class="requireRed">*</span></label>
                            <select class="form-select" id="invtr_item" name="invtr_item"
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Item</option>

                                <?php
                                if ($item_list_result->num_rows >= 1) {
                                    $item_list_result->data_seek(0);
                                    while ($row3 = $item_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted, $row['itemID']) && !isset($invtr_item)) {
                                            $selected = $row['itemID'] == $row3['id'] ? " selected" : "";
                                        } else if (isset($invtr_item)) {
                                            $selected = $invtr_item == $row3['id'] ? " selected" : "";
                                        }
                                        echo "<option value=\"" . $row3['id'] . "\"$selected>" . $row3['name'] . "</option>";
                                    }
                                } else {
                                    echo "<option value=\"0\">None</option>";
                                }
                                ?>
                            </select>

                            <?php if (isset($item_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $item_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="invtr_unit_price_lbl" for="invtr_unit_price">Unit
                                Price</label>
                            <input class="form-control" type="text" name="invtr_unit_price" id="invtr_unit_price" value="<?php
                                                                                                                            if (isset($dataExisted) && isset($row['unit_price']) && !isset($invtr_unit_price)) {
                                                                                                                                echo $row['unit_price'];
                                                                                                                            } else if (isset($invtr_unit_price)) {
                                                                                                                                echo $invtr_unit_price;
                                                                                                                            }
                                                                                                                            ?>" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($unit_price_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $unit_price_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="invtr_qty_lbl" for="invtr_qty">Balance
                                Quantity</label>
                            <input class="form-control" type="text" name="invtr_qty" id="invtr_qty" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['bal_qty']) && !isset($invtr_qty)) {
                                                                                                                echo $row['bal_qty'];
                                                                                                            } else if (isset($invtr_qty)) {
                                                                                                                echo $invtr_qty;
                                                                                                            }
                                                                                                            ?>" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($qty_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $qty_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="invtr_amt_lbl" for="invtr_amt">Amount<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="invtr_amt" id="invtr_amt" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['amount']) && !isset($invtr_amt)) {
                                                                                                                echo $row['amount'];
                                                                                                            } else if (isset($invtr_amt)) {
                                                                                                                echo $invtr_amt;
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
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label form_lbl" id="invtr_remark_lbl" for="invtr_remark">Transaction
                                Remark</label>
                            <textarea class="form-control" name="invtr_remark" id="invtr_remark" rows="3" <?php if ($act == '') echo 'disabled' ?>><?php
                                                                                                                                                    if (isset($dataExisted) && isset($row['remarks']) && !isset($invtr_remark))
                                                                                                                                                        echo $row['remark'];
                                                                                                                                                    else if (isset($invtr_remark))
                                                                                                                                                        echo $invtr_remark;
                                                                                                                                                    ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="invtr_attach_lbl" for="invtr_attach">Attachment</label>
                            <input class="form-control" type="file" name="invtr_attach" id="invtr_attach" value="" <?php if ($act == '') echo 'disabled' ?>>
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
                                <img id="invtr_attach_preview" name="invtr_attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="invtr_attachmentValue" value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
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
        <?php include "../js/invtr_trans.js" ?>
    </script>

</body>

</html>