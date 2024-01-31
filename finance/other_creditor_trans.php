<?php
$pageTitle = "Other Creditor Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = OCR_TRANS;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

$redirect_page = $SITEURL . '/finance/other_creditor_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';
$errorMsgAlert = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";

$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");
$img_path = '../' . img_server . 'finance/other_creditor/';
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

    $query = "SELECT MAX(id) AS max_id FROM " . $tblName . " LIMIT 1";
    $result = mysqli_query($finance_connect, $query);
    $maxRow = mysqli_fetch_assoc($result);
    $maxRowId = $maxRow['max_id'];

    if ($maxRowId === null) {
        $nextRowId = str_pad(1, 5, '0', STR_PAD_LEFT);
    } else {
        $nextRowId = str_pad($maxRowId + 1, 5, '0', STR_PAD_LEFT);
    }

    //format "SDT+YEARMONTHDATE+00001"
    $trans_id = "OCRT{$currentDate}{$nextRowId}";
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

if (post('actionBtn')) {
    $action = post('actionBtn');

    $ocr_type = postSpaceFilter("ocr_type");
    $ocr_date = postSpaceFilter("ocr_date");
    $ocr_creditor = postSpaceFilter('ocr_creditor_hidden');
    $creditor_other = postSpaceFilter('creditor_other');
    $ocr_amt = postSpaceFilter('ocr_amt');
    $ocr_prev_amt = 0;
    $ocr_final_amt = 0;
    $ocr_desc = postSpaceFilter('ocr_desc');
    $ocr_attach = null;
    $ocr_remark = postSpaceFilter('ocr_remark');

    $isDuplicateMerchant = false;

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    if (isset($_FILES["ocr_attach"]) && $_FILES["ocr_attach"]["size"] != 0) {
        $ocr_attach = $_FILES["ocr_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $ocr_attach = $_POST['existing_attachment'];
    }

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["ocr_attach"]["size"] != 0) {
                // move file
                $ocr_file_name = $_FILES["ocr_attach"]["name"];
                $ocr_file_tmp_name = $_FILES["ocr_attach"]["tmp_name"];
                $img_ext = pathinfo($ocr_file_name, PATHINFO_EXTENSION);
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
                    if (move_uploaded_file($ocr_file_tmp_name, $img_path . $new_file_name)) {
                        $ocr_attach = $new_file_name; // Update $ocr_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (!$ocr_type && $ocr_type < 1) {
                $type_err = "Please specify the type of transaction.";
                break;
            } else if (!$ocr_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$ocr_creditor && $ocr_creditor < 1) {
                $debt_err = "Please specify the creditor.";
                break;
            } else if (($ocr_creditor == 'Create New Merchant') && !isset($creditor_other)) {
                $creditor_other_err = "Debtor name is required!";
                break;
            } else if (($ocr_creditor == 'Create New Merchant') && isDuplicateRecord("name", $creditor_other, MERCHANT, $finance_connect, '')) {
                $creditor_other_err = "Duplicate record found for Merchant name.";
                $isDuplicateMerchant = true;
                break;
            } else if (!$ocr_amt) {
                $amt_err = "Amount cannot be empty.";
                break;
            } else if (!$ocr_desc) {
                $ocr_desc_err = "Description cannot be empty.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //get final_amt from prev row
                    $query = "SELECT
                    final_amt,
                    LAG(final_amt) OVER (ORDER BY id DESC) AS prev_final_amt
                    FROM
                        " . $tblName . "
                    WHERE
                        creditor = '$ocr_creditor'
                    ORDER BY
                        id DESC
                    LIMIT 1";

                    $result = mysqli_query($finance_connect, $query);

                    if (!$result) {
                        die("Query failed: " . mysqli_error($finance_connect));
                    }

                    $prev_row = mysqli_fetch_assoc($result);

                    if (isset($prev_row['final_amt'])) {
                        $ocr_prev_amt = $prev_row['final_amt'];
                    } else {
                        $ocr_prev_amt = 0;
                    }

                    $ocr_amt = floatval(str_replace(',', '', $ocr_amt));

                    if ($ocr_type == 'Add') {
                        $ocr_final_amt = number_format($ocr_prev_amt + $ocr_amt, 2, '.', '');
                    } else if ($ocr_type == 'Deduct') {
                        $ocr_final_amt = number_format($ocr_prev_amt - $ocr_amt, 2, '.', '');
                    }

                    if (($ocr_creditor == 'Create New Merchant') && !($isDuplicateMerchant)) {
                        try {
                            $ocr_creditor = insertNewMerchant($creditor_other, USER_ID, $finance_connect);
                            generateDBData(MERCHANT, $finance_connect);
                        } catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }
                    }

                    // check value
                    if ($trans_id) {
                        array_push($newvalarr, $trans_id);
                        array_push($datafield, 'transactionID');
                    }
                    if ($ocr_type)
                        array_push($newvalarr, $ocr_type[0]);

                    if ($ocr_date)
                        array_push($newvalarr, $ocr_date);

                    if ($ocr_final_amt)
                        array_push($newvalarr, $ocr_amt);

                    if ($ocr_creditor)
                        array_push($newvalarr, $ocr_creditor);

                    if ($ocr_attach)
                        array_push($newvalarr, $ocr_attach);

                    if ($ocr_prev_amt)
                        array_push($newvalarr, $ocr_prev_amt);

                    if ($ocr_final_amt)
                        array_push($newvalarr, $ocr_final_amt);

                    if ($ocr_desc)
                        array_push($newvalarr, $ocr_desc);

                    if ($ocr_remark)
                        array_push($newvalarr, $ocr_remark);

                    $query = "INSERT INTO " . $tblName . "(transactionID,type,date,creditor,amount,prev_amt,final_amt,description,remark,attachment,create_by,create_date,create_time) VALUES ('$trans_id','$ocr_type','$ocr_date','$ocr_creditor','$ocr_amt','$ocr_prev_amt','$ocr_final_amt','$ocr_desc','$ocr_remark','$ocr_attach','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $finance_connect->insert_id;
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if (($ocr_creditor == 'Create New Merchant') && !($isDuplicateMerchant)) {
                        try {
                            $ocr_creditor = insertNewMerchant($creditor_other, USER_ID, $finance_connect);
                            generateDBData(MERCHANT, $finance_connect);
                        } catch (Exception $e) {
                            $errorMsg = $e->getMessage();
                        }
                    }
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if ($row['type'] != $ocr_type) {
                        array_push($oldvalarr, $row['type']);
                        array_push($chgvalarr, $ocr_type);
                    }
                    if ($row['date'] != $ocr_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $ocr_date);
                    }
                    if ($row['creditor'] != $ocr_creditor) {
                        array_push($oldvalarr, $row['creditor']);
                        array_push($chgvalarr, $ocr_creditor);
                    }
                    if ($row['amount'] != $ocr_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $ocr_amt);
                    }
                    if ($row['prev_amt'] != $ocr_prev_amt) {
                        array_push($oldvalarr, $row['prev_amt']);
                        array_push($chgvalarr, $ocr_prev_amt);
                    }
                    if ($row['final_amt'] != $ocr_final_amt) {
                        array_push($oldvalarr, $row['final_amt']);
                        array_push($chgvalarr, $ocr_final_amt);
                    }
                    if ($row['description'] != $ocr_desc) {
                        array_push($oldvalarr, $row['description']);
                        array_push($chgvalarr, $ocr_desc);
                    }
                    $ocr_attach = isset($ocr_attach) ? $ocr_attach : '';
                    if (($row['attachment'] != $ocr_attach) && ($ocr_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $ocr_attach);
                    }

                    if ($row['remark'] != $ocr_remark) {
                        if ($row['remark'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remark'];

                        array_push($oldvalarr, $old_remark);

                        if ($ocr_remark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $ocr_remark;

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
                            " . $tblName . "
                        WHERE
                            creditor = '$ocr_creditor'
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
                            $ocr_prev_amt = $prev_row['final_amt'];
                        } else {
                            $ocr_prev_amt = 0;
                        }
                        $ocr_amt = floatval(str_replace(',', '', $ocr_amt));

                        if ($ocr_type == 'Add') {
                            $ocr_final_amt = number_format($ocr_prev_amt + $ocr_amt, 2, '.', '');
                        } else if ($ocr_type == 'Deduct') {
                            $ocr_final_amt = number_format($ocr_prev_amt - $ocr_amt, 2, '.', '');
                        }

                        $query = "UPDATE " . $tblName . " SET type = '$ocr_type',date = '$ocr_date',creditor = '$ocr_creditor', amount = '$ocr_amt', prev_amt ='$ocr_prev_amt', final_amt ='$ocr_final_amt', description='$ocr_desc', attachment ='$ocr_attach', remark ='$ocr_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);

                        updateTransAmt($finance_connect, $tblName, ['creditor'], ['creditor']);
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
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
                    'query_table'  => $tblName,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($pageAction == 'Add') {

                    $log['newval'] = implodeWithComma($newvalarr);

                    if (isset($returnData)) {
                        $log['act_msg'] = USER_NAME . " added <b>$trans_id</b> into <b><i>" . $tblName . " Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>$trans_id</b> into <b><i>" . $tblName . " Table</i></b> ( $errorMsg )";
                    }
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
            $trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord($tblName,'', $dataID, $trans_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
    updateTransAmt($finance_connect, $tblName, ['creditor'], ['creditor']);
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
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
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                                                                                                                echo displayPageAction($act, 'Transaction');
                                                                                                                ?></p>

    </div>

    <div id="OCRTFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="OCRTForm" method="post" action="" enctype="multipart/form-data">
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
                            <label class="form-label form_lbl" id="ocr_trans_id_lbl" for="ocr_trans_id">Transaction
                                ID</label>
                            <p>
                                <input class="form-control" type="text" name="ocr_trans_id" id="ocr_trans_id" disabled value="<?php echo $trans_id ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="ocr_type_label" for="ocr_type">Type
                                <span class="requireRed">*</span></label>
                            <select class="form-select" name="ocr_type" id="ocr_type" required <?php if ($act == '') echo 'disabled' ?>>
                                <option disabled selected>Select transaction type</option>
                                <option value="Add" <?php
                                                    if (isset($dataExisted, $row['type'])  && $row['type'] == 'Add'  && (!isset($ocr_type) ||  $ocr_type == 'Add')) {
                                                        echo "selected";
                                                    } else {
                                                        echo "";
                                                    }

                                                    ?>>
                                    Add</option>
                                <option value="Deduct" <?php
                                                        if (isset($dataExisted, $row['type']) && $row['type'] == 'Deduct' && (!isset($ocr_type) || $ocr_type == 'Deduct')) {
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
                            <label class="form-label form_lbl" id="ocr_date_label" for="ocr_date">Date<span class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="ocr_date" id="ocr_date" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['date']) && !isset($ocr_date)) {
                                                                                                                echo $row['date'];
                                                                                                            } else if (isset($ocr_date)) {
                                                                                                                echo $ocr_date;
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
                        <div class="col-md-6 autocomplete">
                            <label class="form-label form_lbl" id="ocr_creditor_lbl" for="ocr_creditor">Creditor<span class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);

                            if (isset($row['creditor']))
                                $echoVal = $row['creditor'];

                            if (isset($echoVal)) {
                                $mrcht_rst = getData('name', "id = '$echoVal'", '', MERCHANT, $finance_connect);
                                if (!$mrcht_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $mrcht_row = $mrcht_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="ocr_creditor" id="ocr_creditor" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $mrcht_row['name'] : ''  ?>">
                            <input type="hidden" name="ocr_creditor_hidden" id="ocr_creditor_hidden" value="<?php echo (isset($row['creditor'])) ? $row['creditor'] : ''; ?>">

                            <?php if (isset($debt_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $debt_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="ocr_amt_lbl" for="ocr_amt">Amount<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="ocr_amt" id="ocr_amt" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['amount']) && !isset($ocr_amt)) {
                                                                                                            echo $row['amount'];
                                                                                                        } else if (isset($ocr_amt)) {
                                                                                                            echo $ocr_amt;
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
                        <div id="SDT_CreateMerchant" hidden>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label form_lbl" id="creditor_other_lbl" for="creditor_other">Debtor
                                            Name*</label>
                                        <input class="form-control" type="text" name="creditor_other" id="creditor_other" <?php if ($act == '') echo 'disabled' ?>>
                                        <?php if (isset($creditor_other_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1"><?php echo $creditor_other_err; ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="ocr_desc_lbl" for="ocr_desc">Description<span class="requireRed">*</span></label>
                    <textarea class="form-control" name="ocr_desc" id="ocr_desc" rows="3" <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['description'])) echo $row['description'] ?></textarea>
                    <?php if (isset($ocr_desc_err)) { ?>
                        <div id="err_msg">
                            <span class="mt-n1"><?php echo $ocr_desc_err; ?></span>
                        </div>
                    <?php } ?>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="ocr_remark_lbl" for="ocr_remark">Remark</label>
                    <textarea class="form-control" name="ocr_remark" id="ocr_remark" rows="3" <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="ocr_attach_lbl" for="ocr_attach">Attachment</label>
                            <input class="form-control" type="file" name="ocr_attach" id="ocr_attach" value="" <?php if ($act == '') echo 'disabled' ?>>
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
                                <img id="ocr_attach_preview" name="ocr_attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="ocr_attachmentValue" value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
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
        <?php include "../js/othr_cred_trans.js" ?>

        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
    </script>

</body>

</html>