<?php
$pageTitle = "Atome Transaction Backup Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = ATOME_TRANS_BACKUP;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

$redirect_page = $SITEURL . '/finance/atome_trans_backup_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/atome_trans_backup/';
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

    $atb_trans_id = postSpaceFilter("atb_trans_id");
    $atb_atome_id = postSpaceFilter("atb_atome_id");
    $atb_date = postSpaceFilter("atb_date");
    $atb_trans_outlet = postSpaceFilter("atb_trans_outlet");
    $atb_platform_id = postSpaceFilter("atb_platform_id");
    $atb_amt_rec = postSpaceFilter("atb_amt_rec");

    $atb_attach = null;
    if (isset($_FILES["atb_attach"]) && $_FILES["atb_attach"]["size"] != 0) {
        $atb_attach = $_FILES["atb_attach"]["name"];
    } elseif (isset($_POST['atb_attachmentValue'])) {
        $atb_attach = $_POST['atb_attachmentValue'];
    }

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["atb_attach"]["size"] != 0) {
                // move file
                $atb_file_name = $_FILES["atb_attach"]["name"];
                $atb_file_tmp_name = $_FILES["atb_attach"]["tmp_name"];
                $img_ext = pathinfo($atb_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $atb_date . '_' . $img_ext);

                    foreach ($files as $file) {
                        $filename = basename($file);

                        // Adjust the regex to match the new file naming convention
                        if (preg_match('/' . preg_quote($atb_date . '_' ) . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $atb_date . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($atb_file_tmp_name, $img_path . $new_file_name)) {
                        $atb_attach = $new_file_name; // Update $btb_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else {
                    $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
                }
            }

            if (!$atb_trans_id) {
                $atb_trans_id_err = "Please specify the Transaction ID.";
                break;
            } else if (!$atb_atome_id) {
                $atb_atome_id_err = "Please specify the Atome Order ID.";
                break;
            } else if (!$atb_date) {
                $atb_date_err = "Please specify the Transaction Date and Time.";
                break;
            } else if (!$atb_trans_outlet) {
                $atb_trans_outlet_err = "Please specify the Transaction Outlet.";
                break;
            } else if (!$atb_platform_id) {
                $atb_platform_id_err = "Please specify the E-commerce Platform Order ID.";
                break;
            } else if (!$atb_amt_rec) {
                $atb_amt_rec_err = "Please specify the Amount Receivable.";
                break;
            } else if (!$atb_attach) {
                $attach_err = "Please attach the file.";
                break;

            } else if ($atb_trans_id && $atb_atome_id && $atb_date && $atb_trans_outlet && $atb_platform_id && isDuplicateRecordWithConditions(['trans_id', 'atome_id', 'date','trans_outlet', 'platform_id'], [$atb_trans_id, $atb_atome_id, $atb_date, $atb_trans_outlet, $atb_platform_id], $tblName, $finance_connect, $dataID)) {
                $atb_trans_id_err = "Duplicate record found for " . $pageTitle . " Transaction ID.";
                $atb_atome_id_err = "Duplicate record found for " . $pageTitle . " Atome Order ID.";
                $atb_date_err = "Duplicate record found for " . $pageTitle . " Transaction Date and Time.";
                $atb_trans_outlet_err = "Duplicate record found for " . $pageTitle . " Transaction Outlet.";
                $atb_platform_id_err = "Duplicate record found for " . $pageTitle . " E-commerce Platform Order ID.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //check values
                    if ($atb_trans_id) {
                        array_push($newvalarr, $atb_trans_id);
                        array_push($datafield, 'trans_id');
                    }
                    if ($atb_atome_id) {
                        array_push($newvalarr, $atb_atome_id);
                        array_push($datafield, 'atome_id');
                    }
                    if ($atb_date) {
                        array_push($newvalarr, $atb_date);
                        array_push($datafield, 'date');
                    }
                    if ($atb_trans_outlet) {
                        array_push($newvalarr, $atb_trans_outlet);
                        array_push($datafield, 'trans_outlet');
                    }
                    if ($atb_platform_id) {
                        array_push($newvalarr, $atb_platform_id);
                        array_push($datafield, 'platform_id');
                    }

                    if ($atb_amt_rec) {
                        array_push($newvalarr, $atb_amt_rec);
                        array_push($datafield, 'amt_rec');
                    }

                    if ($atb_attach) {
                        array_push($newvalarr, $atb_attach);
                        array_push($datafield, 'attachment');
                    }

                    $query = "INSERT INTO " . $tblName  . "(trans_id,atome_id,date,trans_outlet,platform_id,amt_rec,attachment,create_by,create_date,create_time) VALUES ('$atb_trans_id','$atb_atome_id','$atb_date','$atb_trans_outlet','$atb_platform_id','$atb_amt_rec','$atb_attach','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['trans_id'] != $atb_trans_id) {
                        array_push($oldvalarr, $row['trans_id']);
                        array_push($chgvalarr, $atb_trans_id);
                        array_push($datafield, 'trans_id');
                    }

                    if ($row['atome_id'] != $atb_atome_id) {
                        array_push($oldvalarr, $row['atome_id']);
                        array_push($chgvalarr, $atb_atome_id);
                        array_push($datafield, 'atome_id');
                    }

                    if ($row['date'] != $atb_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $atb_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['trans_outlet'] != $atb_trans_outlet) {
                        array_push($oldvalarr, $row['trans_outlet']);
                        array_push($chgvalarr, $atb_trans_outlet);
                        array_push($datafield, 'trans_outlet');
                    }

                    if ($row['platform_id'] != $atb_platform_id) {
                        array_push($oldvalarr, $row['platform_id']);
                        array_push($chgvalarr, $atb_platform_id);
                        array_push($datafield, 'platform_id');
                    }

                    if ($row['amt_rec'] != $atb_amt_rec) {
                        array_push($oldvalarr, $row['amt_rec']);
                        array_push($chgvalarr, $atb_amt_rec);
                        array_push($datafield, 'amt_rec');
                    }

                    $atb_attach = isset($atb_attach) ? $atb_attach : '';
                    if (($row['attachment'] != $atb_attach) && ($atb_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $atb_attach);
                        array_push($datafield, 'attachment');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET trans_id = '$atb_trans_id', atome_id = '$atb_atome_id', date = '$atb_date', trans_outlet = '$atb_trans_outlet', platform_id = '$atb_platform_id', amt_rec = '$atb_amt_rec', attachment ='$atb_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            <form id="ATBForm" method="post" action="" enctype="multipart/form-data">
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
            <label class="form-label form_lbl" id="atb_trans_id_lbl" for="atb_trans_id">Transaction ID<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="atb_trans_id" id="atb_trans_id" value="<?php
                if (isset($dataExisted) && isset($row['trans_id']) && !isset($atb_trans_id)) {
                    echo $row['trans_id'];
                } else if (isset($atb_trans_id)) {
                    echo $atb_trans_id;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($atb_trans_id_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $atb_trans_id_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
            <label class="form-label form_lbl" id="atb_atome_id_lbl" for="atb_atome_id">Atome Order ID<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="atb_atome_id" id="atb_atome_id" value="<?php
                if (isset($dataExisted) && isset($row['atome_id']) && !isset($atb_atome_id)) {
                    echo $row['atome_id'];
                } else if (isset($atb_atome_id)) {
                    echo $atb_atome_id;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($atb_atome_id_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $atb_atome_id_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="atb_date_label" for="atb_date">Transaction Date and Time<span class="requireRed">*</span></label>
            <input class="form-control" type="date" name="atb_date" id="atb_date" value="<?php
                if (isset($dataExisted) && isset($row['date']) && !isset($atb_date)) {
                    echo $row['date'];
                } else if (isset($atb_date)) {
                    echo $atb_date;
                } else {
                    echo date('Y-m-d');
                }
            ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($atb_date)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $atb_date; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="atb_trans_outlet_lbl" for="atb_trans_outlet">Transaction Outlet<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="atb_trans_outlet" id="atb_trans_outlet" value="<?php
                if (isset($dataExisted) && isset($row['trans_outlet']) && !isset($atb_trans_outlet)) {
                    echo $row['trans_outlet'];
                } else if (isset($atb_trans_outlet)) {
                    echo $atb_trans_outlet;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($atb_trans_outlet_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $atb_trans_outlet_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="atb_platform_id_lbl" for="atb_platform_id">E-commerce Platform Order ID<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="atb_platform_id" id="atb_platform_id" value="<?php
                if (isset($dataExisted) && isset($row['platform_id']) && !isset($atb_platform_id)) {
                    echo $row['platform_id'];
                } else if (isset($atb_platform_id)) {
                    echo $atb_platform_id;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($atb_platform_id_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $atb_platform_id_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
        <label class="form-label form_lbl" id="atb_amt_rec_lbl" for="atb_amt_rec">Amount Receivable<span class="requireRed">*</span></label>
            <input class="form-control" type="number" step='0.01' name="atb_amt_rec" id="atb_amt_rec" value="<?php
                if (isset($dataExisted) && isset($row['amt_rec']) && !isset($atb_amt_rec)) {
                    echo $row['amt_rec'];
                } else if (isset($atb_amt_rec)) {
                    echo $atb_amt_rec;
                }
            ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($atb_amt_rec_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $atb_amt_rec_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
          
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="atb_attach_lbl" for="atb_attach">Attachment*</label>
                            <input class="form-control" type="file" name="atb_attach" id="atb_attach" <?php if ($act == '') echo 'disabled' ?>>

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

                                if (isset($dataExisted) && isset($row['attachment']) && !isset($atb_attach)) {
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                } else if (isset($atb_attach)) {
                                    $attachmentSrc = $img_path . $atb_attach;
                                }
                                ?>
                                <img id="atb_attach_preview" name="atb_attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="atb_attachmentValue" id="atb_attachmentValue" value="<?php if (isset($dataExisted) && isset($row['attachment']) && !isset($atb_attach)) {
                                                                                                                    echo $row['attachment'];
                                                                                                                } else if (isset($atb_month)) {
                                                                                                                    echo $atb_attach;
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

        <?php include "../js/atome_trans_backup.js" ?>
    </script>

</body>

</html>