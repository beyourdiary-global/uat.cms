<?php
$pageTitle = "Stripe Transaction Backup Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = STRIPE_TRANS_BACKUP;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

$redirect_page = $SITEURL . '/finance/stripe_trans_backup_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/stripe_trans_backup/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

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

    $stb_payout_id = postSpaceFilter("stb_payout_id");
    $stb_date_paid = postSpaceFilter('stb_date_paid');
    $stb_curr_unit = postSpaceFilter('stb_curr_unit_hidden');
    $stb_amount = postSpaceFilter('stb_amount');

    $stb_attach = null;
    if (isset($_FILES["stb_attach"]) && $_FILES["stb_attach"]["size"] != 0) {
        $btb_attach = $_FILES["stb_attach"]["name"];
    } elseif (isset($_POST['stb_attachmentValue'])) {
        $btb_attach = $_POST['stb_attachmentValue'];
    }

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["stb_attach"]["size"] != 0) {
                // move file
                $stb_file_name = $_FILES["stb_attach"]["name"];
                $stb_file_tmp_name = $_FILES["stb_attach"]["tmp_name"];
                $img_ext = pathinfo($stb_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $stb_date_paid . $img_ext);

                    foreach ($files as $file) {
                        $filename = basename($file);

                        // Adjust the regex to match the new file naming convention
                        if (preg_match('/' . preg_quote($stb_date_paid . '_' , '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $stb_date_paid . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($stb_file_tmp_name, $img_path . $new_file_name)) {
                        $stb_attach = $new_file_name; // Update $stb_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else {
                    $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
                }
            }

            if (empty($stb_amount)) {
                $stb_amount_err = "Amount is required!";
            }

            if (!$stb_payout_id) {
                $stb_payout_id_err = "Please specify the Stripe Payout ID.";
                break;
            } else if (!$stb_curr_unit) {
                $stb_curr_unit_err = "Please specify the Curreny Unit.";
                break;
            } else if (!$stb_amount) {
                $stb_amount_err = "Please specify the Amount.";
                break;
            } else if (!$stb_attach) {
                $attach_err = "Please attach the file.";
                break;
            } else if ($stb_payout_id && $stb_date_paid && $stb_curr_unit && $stb_amount && isDuplicateRecordWithConditions(['payout_id', 'date_paid','curr_unit','amount'], [$stb_payout_id,$stb_date_paid,$stb_curr_unit,$stb_amount], $tblName, $finance_connect, $dataID)) {
                $stb_payout_id_err = "Duplicate record found for " . $pageTitle . " Stripe Payout ID.";
                $stb_date_paid_err = "Duplicate record found for " . $pageTitle . " Date Paid.";
                $stb_curr_unit_err = "Duplicate record found for " . $pageTitle . " Currency Unit.";
                $stb_amount_err = "Duplicate record found for " . $pageTitle . " Amount.";

                break;
            } else if ($action == 'addTransaction') {
                try {
                    //check values
                    if ($stb_payout_id) {
                        array_push($newvalarr, $stb_payout_id);
                        array_push($datafield, 'payout_id');
                    }
                    if ($stb_date_paid) {
                        array_push($newvalarr, $stb_date_paid);
                        array_push($datafield, 'date_paid');
                    }

                    if ($stb_curr_unit) {
                        array_push($newvalarr, $stb_curr_unit);
                        array_push($datafield, 'curr_unit');
                    }
                    if ($stb_amount) {
                        array_push($newvalarr, $stb_amount);
                        array_push($datafield, 'amount');
                    }

                    if ($stb_attach) {
                        array_push($newvalarr, $stb_attach);
                        array_push($datafield, 'attachment');
                    }

                    $query = "INSERT INTO " . $tblName  . "(payout_id,date_paid,curr_unit,amount,attachment,create_by,create_date,create_time) VALUES ('$stb_payout_id','$stb_date_paid','$stb_curr_unit','$stb_amount','$stb_attach','" . USER_ID . "',curdate(),curtime())";

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
                    if ($row['payout_id'] != $stb_payout_id) {
                        array_push($oldvalarr, $row['payout_id']);
                        array_push($chgvalarr, $stb_payout_id);
                        array_push($datafield, 'payout_id');
                    }

                    if ($row['date_paid'] != $stb_date_paid) {
                        array_push($oldvalarr, $row['date_paid']);
                        array_push($chgvalarr, $stb_date_paid);
                        array_push($datafield, 'date_paid');
                    }

                    if ($row['curr_unit'] != $stb_curr_unit) {
                        array_push($oldvalarr, $row['curr_unit']);
                        array_push($chgvalarr, $stb_curr_unit);
                        array_push($datafield, 'curr_unit');
                    }

                    if ($row['amount'] != $stb_amount) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $stb_amount);
                        array_push($datafield, 'amount');
                    }

                    $stb_attach = isset($stb_attach) ? $stb_attach : '';
                    if (($row['attachment'] != $stb_attach) && ($stb_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $stb_attach);
                        array_push($datafield, 'attachment');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET payout_id = '$stb_payout_id', date_paid = '$stb_date_paid', curr_unit = '$stb_curr_unit', amount = '$stb_amount', attachment ='$stb_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];

            //SET the record status to 'D'
            deleteRecord($tblName, '', $dataID, $dataID, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
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
                                                                                                                    echo displayPageAction($act, $pageTitle);
                                                                                                                    ?>
        </p>

    </div>

    <div id="formContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="STBForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, $pageTitle);
                        ?>
                    </h2>
                </div>

                        <div class="row">
                <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="stb_payout_id_lbl" for="stb_payout_id">Stripe Payout ID<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="stb_payout_id" id="stb_payout_id" value="<?php
                if (isset($dataExisted) && isset($row['payout_id']) && !isset($stb_payout_id)) {
                    echo $row['payout_id'];
                } else if (isset($stb_payout_id)) {
                    echo $stb_payout_id;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($stb_payout_id_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $stb_payout_id_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="stb_date_paid_label" for="stb_date_paid">Paid Date<span class="requireRed">*</span></label>
            <input class="form-control" type="date" name="stb_date_paid" id="stb_date_paid" value="<?php
                if (isset($dataExisted) && isset($row['paid_date']) && !isset($stb_date_paid)) {
                    echo $row['date'];
                } else if (isset($stb_date_paid)) {
                    echo $stb_date_paid;
                } else {
                    echo date('Y-m-d');
                }
            ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($stb_date_paid_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $stb_date_paid_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6 autocomplete">
        <label class="form-label form_lbl" id="stb_curr_unit_lbl" for="stb_curr_unit">Currency Unit<span class="requireRed">*</span></label>
        <?php
        unset($echoVal);

        if (isset($row['curr_unit']))
            $echoVal = $row['curr_unit'];

        if (isset($echoVal)) {
            $curr_unit_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
            if (!$curr_unit_rst) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }
            $curr_unit_row = $curr_unit_rst->fetch_assoc();
        }
        ?>
        <input class="form-control" type="text" name="stb_curr_unit" id="stb_curr_unit" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $curr_unit_row['name'] : '' ?>">
        <input type="hidden" name="stb_curr_unit_hidden" id="stb_curr_unit_hidden" value="<?php echo (isset($row['curr_unit'])) ? $row['curr_unit'] : ''; ?>">

        <?php if (isset($curr_unit_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $curr_unit_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="stb_amount_lbl" for="stb_amount">Amount<span class="requireRed">*</span></label>
            <input class="form-control" type="number" step="0.01" name="stb_amount" id="stb_amount" value="<?php
                if (isset($dataExisted) && isset($row['amount']) && !isset($stb_amount)) {
                    echo $row['amount'];
                } else if (isset($stb_amount)) {
                    echo $stb_amount;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($stb_amount_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $stb_amount_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>


                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="stb_attach_lbl" for="stb_attach">Attachment*</label>
                            <input class="form-control" type="file" name="stb_attach" id="stb_attach" <?php if ($act == '') echo 'disabled' ?>>

                            <?php if (isset($row['attachment']) && $row['attachment']) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?></span>
                                </div>
                                <input type="hidden" name="existing_attachment" value="<?php echo htmlspecialchars($row['attachment']); ?>">
                            <?php } ?>

                            <?php if (isset($attach_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $attach_err; ?></span>
                                </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <?php
                                $attachmentSrc = '';

                                if (isset($dataExisted) && isset($row['attachment']) && !isset($stb_attach)) {
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                } else if (isset($stb_attach)) {
                                    $attachmentSrc = $img_path . $stb_attach;
                                }
                                ?>
                                <img id="stb_attach_preview" name="stb_attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="stb_attachmentValue" id="stb_attachmentValue" value="<?php if (isset($dataExisted) && isset($row['attachment']) && !isset($stb_attach)) {
                                                                                                                    echo $row['attachment'];
                                                                                                                } else if (isset($stb_attach)) {
                                                                                                                    echo $stb_attach;
                                                                                                                }

                                                                                                                ?>">
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
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        setButtonColor();
        setAutofocus(action);
        preloader(300, action);

        <?php include "../js/stripe_trans_backup.js" ?>
    </script>

</body>

</html>