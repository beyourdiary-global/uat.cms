<?php
$pageTitle = "Shopee Withdrawal Transactions";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = SHOPEE_WDL_TRANS;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/shopee_withdrawal_transactions_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Attachment
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

$img_path = '../' . img_server . 'finance/shopee_withdrawal_transactions/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}
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

    $swt_date = postSpaceFilter("swt_date");
    $swt_id = postSpaceFilter("swt_id");
    $swt_amt = postSpaceFilter('swt_amt');
    $swt_pic = postSpaceFilter("swt_pic_hidden");
    $swt_attach = null;
    if (isset($_FILES["swt_attach"]) && $_FILES["swt_attach"]["size"] != 0) {
        $swt_attach = $_FILES["swt_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $swt_attach = $_POST['existing_attachment'];
    }
    $swt_remark = postSpaceFilter('swt_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["swt_attach"]["size"] != 0) {
                // move file
                $swt_file_name = $_FILES["swt_attach"]["name"];
                $swt_file_tmp_name = $_FILES["swt_attach"]["tmp_name"];
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
                    if (move_uploaded_file($swt_file_tmp_name, $img_path . $new_file_name)) {
                        $swt_attach = $new_file_name; // Update $swt_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            if (!$swt_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$swt_id) {
                $id_err = "Please specify the id.";
                break;
            } else if (!$swt_amt) {
                $amt_err = "Please specify the amount.";
                break;
            } else if (!$swt_pic && $swt_pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //check values
                    
                    if ($swt_date) {
                        array_push($newvalarr, $swt_date);
                        array_push($datafield, 'date');
                    }

                    if ($swt_id) {
                        array_push($newvalarr, $swt_id);
                        array_push($datafield, 'id');
                    }

                    if ($swt_amt) {
                        array_push($newvalarr, $swt_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($swt_pic) {
                        array_push($newvalarr, $swt_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($swt_attach) {
                        array_push($newvalarr, $swt_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($swt_remark) {
                        array_push($newvalarr, $swt_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(date,id,amount,pic,attachment,remark,create_by,create_date,create_time) VALUES ('$swt_date','$swt_id','$swt_amt','$swt_pic','$swt_attach','$swt_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // check value
                    if ($row['date'] != $swt_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $swt_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['id'] != $swt_id) {
                        array_push($oldvalarr, $row['id']);
                        array_push($chgvalarr, $swt_id);
                        array_push($datafield, 'id');
                    }

                    if ($row['amount'] != $swt_amt) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $swt_amt);
                        array_push($datafield, 'amount');
                    }

                    if ($row['pic'] != $swt_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $swt_pic);
                        array_push($datafield, 'pic');
                    }

                    $swt_attach = isset($swt_attach) ? $swt_attach : '';
                    if (($row['attachment'] != $swt_attach) && ($swt_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $swt_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $swt_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $swt_remark == '' ? 'Empty Value' : $swt_remark);
                        array_push($datafield, 'remark');
                    }

                     // convert into string
                     $oldval = implode(",", $oldvalarr);
                     $chgval = implode(",", $chgvalarr);
                     $_SESSION['tempValConfirmBox'] = true;
 
                     if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                        
                         $query = "UPDATE " . $tblName  . " SET date = '$swt_date', id = '$swt_id', amt = '$swt_amt', pic = '$swt_pic', attachment ='$swt_attach', remark ='$swt_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            deleteRecord($tblName ,'', $dataID, '',$finance_connect, $connect, $cdate, $ctime, $pageTitle);
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

    <div id="SWTFormContainer" class="container d-flex justify-content-center">
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

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" id="swt_date" for="swt_date">Withdrawal Date<span class="requireRed">*</span></label>
                        <input class="form-control" type="date" name="swt_date" id="swt_date" value="<?php echo (isset($row['swt_date'])) ? $row['swt_date'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" id="swt_id" for="swt_id">Withdrawal ID<span class="requireRed">*</span></label>
                        <input class="form-control" type="text" name="swt_id" id="swt_id" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $swt_id_row['ID'] : ''  ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label" for="swt_amount">Withdrawal Amount (SGD)<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="swt_amount" id="swt_amount" value="<?php if (isset($row['swt_amount'])) echo $row['swt_amount'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off" oninput="validateNumericInput(this, 'swt_amountErrorMsg')">

            <div id="swt_amountErrorMsg" class="error-message">
                <span class="mt-n1"></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_" id="swt_pic" for="swt_pic">Person-In-Charge<span class="requireRed">*</span></label>
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
            <input class="form-control" type="text" name="swt_pic" id="swt_pic" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : ''  ?>">
            <input type="hidden" name="swt_pic_hidden" id="swt_pic_hidden" value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">

            <?php if (isset($pic_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $pic_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

            <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label form" id="attachment" for="attachment">Attachment</label>
                                <input class="form-control" type="file" name="attachment" id="attachment" value="" <?php if ($act == '') echo 'disabled' ?>>
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
                                    <img id="attach_preview" name="attach_preview" src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                    <input type="hidden" name="attachmentValue" value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label form" for="currentDataRemark">Remark</label>
                        <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                        <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script>
    <?php
   
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

        <?php include "../js/shopee_withdrawal_transactions.js" ?>
    </script>

</body>

</html>
