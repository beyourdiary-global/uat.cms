<?php
$pageTitle = "Initial Capital Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = INITCA_TRANS ;

$row_id = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/initial_capital_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/initial_capital/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($row_id) { //edit/remove/view
    $rst = getData('*', "id = '$row_id'", 'LIMIT 1', $tblName , $finance_connect);

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

    $trans_id = "INITCA{$currentDate}{$nextRowId}";
}

if (!($row_id) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//dropdown list for currency
$cur_list_result = getData('*', '', '', CUR_UNIT, $connect);

if (post('actionBtn')) {
    $action = post('actionBtn');

    $initca_date = postSpaceFilter("initca_date");
    $initca_curr = postSpaceFilter('initca_currency');
    $initca_amt = postSpaceFilter('initca_amt');
    $initca_desc = postSpaceFilter('initca_desc');

    $initca_attach = null;
    if (isset($_FILES["initca_attach"]) && $_FILES["initca_attach"]["size"] != 0) {
        $initca_attach = $_FILES["initca_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $initca_attach = $_POST['existing_attachment'];
    }
    $initca_remark = postSpaceFilter('initca_remark');
    

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["initca_attach"]["size"] != 0) {
                // move file
                $initca_file_name = $_FILES["initca_attach"]["name"];
                $initca_file_tmp_name = $_FILES["initca_attach"]["tmp_name"];
                $img_ext = pathinfo($initca_file_name, PATHINFO_EXTENSION);
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
                    if (move_uploaded_file($initca_file_tmp_name, $img_path . $new_file_name)) {
                        $initca_attach = $new_file_name; // Update $initca_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (!$initca_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$initca_curr && $initca_curr < 1) {
                $curr_err = "Please specify the currency.";
                break;
            } else if (!$initca_amt) {
                $amt_err = "Please specify the amount.";
                break;
            } else if (!$initca_desc) {
                $desc_err = "Please specify the description.";
                break;
            } else if ($action == 'addTransaction') {
                try {

                    if ($initca_date) {
                        array_push($newvalarr, $initca_date);
                        array_push($datafield, 'date');
                    }

                    if ($initca_curr) {
                        array_push($newvalarr, $initca_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($initca_amt) {
                        array_push($newvalarr, $initca_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($initca_desc) {
                        array_push($newvalarr, $initca_desc);
                        array_push($datafield, 'description');
                    }

                    if ($initca_attach) {
                        array_push($newvalarr, $initca_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($initca_remark) {
                        array_push($newvalarr, $initca_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(transactionID,date,currency,amount,description,remark,attachment,create_by,create_date,create_time) VALUES ('$trans_id','$initca_date','$initca_curr','$initca_amt','$initca_desc','$initca_attach','$initca_remark','" . USER_ID . "',curdate(),curtime())";
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
                    // take old value
                    $rst = getData('*', "id = '$row_id'", 'LIMIT 1', $tblName , $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['date'] != $initca_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $initca_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['currency'] != $initca_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $initca_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($row['amount'] != $initca_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $initca_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($row['description'] != $initca_desc) {
                        array_push($oldvalarr, $row['description']);
                        array_push($chgvalarr, $initca_desc);
                        array_push($datafield, 'description');
                    }

                    $initca_attach = isset($initca_attach) ? $initca_attach : '';
                    if (($row['attachment'] != $initca_attach) && ($initca_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $initca_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $initca_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $initca_remark == '' ? 'Empty Value' : $initca_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {

                        $query = "UPDATE " . $tblName  . " SET date = '$initca_date', currency = '$initca_curr', amount = '$initca_amt', description = '$initca_desc', attachment ='$initca_attach', remark ='$initca_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$row_id'";
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName , $finance_connect);
            $row = $rst->fetch_assoc();

            $row_id = $row['id'];
            $trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord($tblName , $row_id, $trans_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
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
    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                                                                                                                    echo displayPageAction($act, 'Transaction');
                                                                                                                    ?>
        </p>

    </div>

    <div id="INITCAFormContainer" class="container d-flex justify-content-center">
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
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="initca_trans_id_lbl"
                                for="initca_trans_id">Transaction
                                ID</label>
                            <p>
                                <input class="form-control" type="text" name="initca_trans_id" id="initca_trans_id"
                                    disabled value="<?php echo $trans_id ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="initca_date_label" for="initca_date">Date<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="initca_date" id="initca_date" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['date']) && !isset($initca_date)) {
                                                                                                                echo $row['date'];
                                                                                                            } else if (isset($initca_date)) {
                                                                                                                echo $initca_date;
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

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="initca_currency_lbl"
                                for="initca_currency">Currency<span class="requireRed">*</span></label>
                            <select class="form-select" id="initca_currency" name="initca_currency"
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option value="0" disabled selected>Select Currency</option>
                                <?php
                                if ($cur_list_result->num_rows >= 1) {
                                    $cur_list_result->data_seek(0);
                                    while ($row2 = $cur_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted, $row['currency']) && (!isset($initca_curr))) {
                                            $selected = $row['currency'] == $row2['id'] ? "selected" : "";
                                        } else if (isset($initca_curr)) {
                                            list($initca_curr_id, $initca_curr_unit) = explode(':', $initca_curr);
                                            $selected = $initca_curr == $row2['id'] ? "selected" : "";
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
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="initca_amt_lbl" for="initca_amt">Amount<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="initca_amt" id="initca_amt" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['amount']) && !isset($initca_amt)) {
                                                                                                            echo $row['amount'];
                                                                                                        } else if (isset($initca_amt)) {
                                                                                                            echo $initca_amt;
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
                    <label class="form-label form_lbl" id="initca_desc_lbl" for="initca_desc">Description*</label>
                    <input class="form-control" type="text" name="initca_desc" id="initca_desc" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['description']) && !isset($initca_desc)) {
                                                                                                            echo $row['description'];
                                                                                                        } else if (isset($initca_desc)) {
                                                                                                            echo $initca_desc;
                                                                                                        }
                                                                                                        ?>"
                        <?php if ($act == '') echo 'disabled' ?>>
                    <?php if (isset($desc_err)) { ?>
                    <div id="err_msg">
                        <span class="mt-n1"><?php echo $desc_err; ?></span>
                    </div>
                    <?php } ?>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="initca_remark_lbl" for="initca_remark">Remark</label>
                    <textarea class="form-control" name="initca_remark" id="initca_remark" rows="3"
                        <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="initca_attach_lbl"
                                for="initca_attach">Attachment</label>
                            <input class="form-control" type="file" name="initca_attach" id="initca_attach" value=""
                                <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($err2)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $err2; ?></span>
                            </div>
                            <?php } ?>
                            <?php if (isset($row['attachment']) && $row['attachment']) { ?>
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
                                <img id="initca_attach_preview" name="initca_attach_preview"
                                    src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="initca_attachmentValue"
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
    <?php include "../js/init_cap_trans.js" ?>
    </script>

</body>

</html>