<?php
$pageTitle = "Monthly Bank Transaction Backup Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = BANK_TRANS_BACKUP;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

$redirect_page = $SITEURL . '/finance/bank_trans_backup_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/bank_trans_backup/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// Get the current date
$defaultDate = new DateTime();
// Set the date to the previous month
$defaultDate->modify('-1 month');


// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);

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

    $btb_year = postSpaceFilter("btb_year");

    $month = postSpaceFilter('btb_month');
    $btb_month = monthStringToNumber($month);

    
    $btb_attach = null;
    if (isset($_FILES["btb_attach"]) && $_FILES["btb_attach"]["size"] != 0) {
        $btb_attach = $_FILES["btb_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $btb_attach = $_POST['existing_attachment'];
    }

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["btb_attach"]["size"] != 0) {
                // move file
                $btb_file_name = $_FILES["btb_attach"]["name"];
                $btb_file_tmp_name = $_FILES["btb_attach"]["tmp_name"];
                $img_ext = pathinfo($btb_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $btb_month . '_' . $btb_year . '_*.' . $img_ext);
                    
                    foreach ($files as $file) {
                        $filename = basename($file);
                        
                        // Adjust the regex to match the new file naming convention
                        if (preg_match('/' . preg_quote($btb_year . '_' . $btb_month, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }
                
                    $unique_id = $highestNumber + 1;
                    $new_file_name = $btb_year . '_' . $btb_month . '_' . $unique_id . '.' . $img_ext_lc;
                
                    // Move the uploaded file
                    if (move_uploaded_file($btb_file_tmp_name, $img_path . $new_file_name)) {
                        $btb_attach = $new_file_name; // Update $btb_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else {
                    $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
                }
                
            }

            if (!$btb_year) {
                $year_err = "Please specify the year.";
                break;
            } else if (!$btb_attach) {
                $attach_err = "Please attach the file.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //check values
                    if ($btb_year) {
                        array_push($newvalarr, $btb_year);
                        array_push($datafield, 'year');
                    }
                    if ($btb_month) {
                        array_push($newvalarr, $btb_month);
                        array_push($datafield, 'month');
                    }

                    if ($btb_attach) {
                        array_push($newvalarr, $btb_attach);
                        array_push($datafield, 'attachment');
                    }

                    $query = "INSERT INTO " . $tblName  . "(year,month,attachment,create_by,create_date,create_time) VALUES ('$btb_year','$btb_month','$btb_attach','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['year'] != $btb_year) {
                        array_push($oldvalarr, $row['year']);
                        array_push($chgvalarr, $btb_year);
                        array_push($datafield, 'year');
                    }

                    if ($row['month'] != $btb_month) {
                        array_push($oldvalarr, $row['month']);
                        array_push($chgvalarr, $btb_month);
                        array_push($datafield, 'month');
                    }

                    $btb_attach = isset($btb_attach) ? $btb_attach : '';
                    if (($row['attachment'] != $btb_attach) && ($btb_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $btb_attach);
                        array_push($datafield, 'attachment');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                        
                        $query = "UPDATE " . $tblName  . " SET year = '$btb_year', month = '$btb_month', attachment ='$btb_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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

            //SET the record status to 'D'
            deleteRecord($tblName , $dataID, $dataID, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
                                                                                                                    echo displayPageAction($act, 'Transaction');
                                                                                                                    ?>
        </p>

    </div>

    <div id="formContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="FATTForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, 'Transaction');
                        ?>
                    </h2>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">

                            <label class="form-label form_lbl" id="btb_year_label" for="btb_year">Year<span
                                    class="requireRed">*</span></label>
                            <div class="input-group date">
                                <input class="form-control" type="text" name="btb_year" id="btb_year" value="<?php
                                                                                if (isset($dataExisted) && isset($row['year']) && !isset($btb_year)) {
                                                                                    echo $row['year'];
                                                                                } else if (isset($btb_year)) {
                                                                                    echo $btb_year;
                                                                                } else {
                                                                                    $defaultYear = $defaultDate->format('Y');
                                                                                    echo $defaultYear;
                                                                                }
                                                                                ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fa-regular fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <?php if (isset($year_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $year_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-6">

                            <label class="form-label form_lbl" id="btb_month_label" for="btb_month">Month<span
                                    class="requireRed">*</span></label>
                            <div class="input-group date">
                                <input class="form-control" type="text" name="btb_month" id="btb_month" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['month']) && !isset($btb_month)) {
                                                                                                                echo monthNumberToString($row['month']);
                                                                                                            } else if (isset($btb_month)) {
                                                                                                                echo $btb_month;
                                                                                                            } else {
                                                                                                                // Get the default month in the format "m"
                                                                                                                $defaultMonth = $defaultDate->format('M');
                                                                                                                echo $defaultMonth;
                                                                                                            }
                                                                                                            ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fa-regular fa-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            <?php if (isset($month_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $month_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                    </div>

                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="btb_attach_lbl" for="btb_attach">Attachment*</label>
                            <input class="form-control" type="file" name="btb_attach" id="btb_attach"
                                <?php if ($act == '') echo 'disabled' ?>>

                            <?php if (isset($row['attachment']) && $row['attachment']) { ?>
                            <div id="err_msg">
                                <span
                                    class="mt-n1"><?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?></span>
                            </div>
                            <input type="hidden" name="existing_attachment"
                                value="<?php echo htmlspecialchars($row['attachment']); ?>">
                            <?php } ?>

                            <?php if (isset($attach_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $attach_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <?php
                                $attachmentSrc = '';

                                if (isset($dataExisted) && isset($row['attachment']) && !isset($btb_attach)) {
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                } else if (isset($btb_attach)) {
                                    $attachmentSrc = $img_path . $btb_attach;
                                }
                                ?>
                                <img id="btb_attach_preview" name="btb_attach_preview"
                                    src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="btb_attachmentValue" id="btb_attachmentValue"
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
    <?php include "../js/bank_trans_backup.js" ?>
    </script>

</body>

</html>