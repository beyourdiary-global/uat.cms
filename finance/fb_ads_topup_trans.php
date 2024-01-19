<?php
$pageTitle = "Facebook Ads Top Up Transaction";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = FB_ADS_TOPUP;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/fb_ads_topup_trans_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/fb_ads_topup/';
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

    $fat_acc = postSpaceFilter("fat_meta_acc_hidden");
    $fat_trans_id = postSpaceFilter("fat_trans_id");
    $fat_date = postSpaceFilter("fat_date");
    $fat_pic = postSpaceFilter("fat_pic_hidden");
    $fat_bank = postSpaceFilter("fat_bank");
    $fat_amt = postSpaceFilter('fat_amt');
    $fat_remark = postSpaceFilter('fat_remark');
    
    $fat_attach = null;
    if (isset($_FILES["fat_attach"]) && $_FILES["fat_attach"]["size"] != 0) {
        $fat_attach = $_FILES["fat_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $fat_attach = $_POST['existing_attachment'];
    }

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            if ($_FILES["fat_attach"]["size"] != 0) {
                // move file
                $fat_file_name = $_FILES["fat_attach"]["name"];
                $fat_file_tmp_name = $_FILES["fat_attach"]["tmp_name"];
                $img_ext = pathinfo($fat_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $fat_trans_id . '_*.' . $img_ext);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (preg_match('/' . preg_quote($fat_trans_id, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $fat_trans_id . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($fat_file_tmp_name, $img_path . $new_file_name)) {
                        $fat_attach = $new_file_name; // Update $fat_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
            }

            if (!$fat_acc && $fat_acc < 1) {
                $acc_err = "Please specify the account.";
                break;
            } else if (!$fat_trans_id) {
                $id_err = "Please specify the transaction ID.";
                break;
            } else if (!$fat_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$fat_pic && $fat_pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if (!$fat_amt) {
                $amt_err = "Please specify the top-up amount.";
                break;
            } else if (!$fat_attach) {
                $desc_err = "Please attach the proof of payment.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    //check values
                    if ($fat_acc) {
                        array_push($newvalarr, $fat_acc);
                        array_push($datafield, 'account');
                    }
                    if ($fat_trans_id) {
                        array_push($newvalarr, $fat_trans_id);
                        array_push($datafield, 'transaction ID');
                    }

                    if ($fat_date) {
                        array_push($newvalarr, $fat_date);
                        array_push($datafield, 'payment date');
                    }

                    if ($fat_pic) {
                        array_push($newvalarr, $fat_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($fat_amt) {
                        array_push($newvalarr, $fat_amt);
                        array_push($datafield, 'top-up amount');
                    }

                    if ($fat_attach) {
                        array_push($newvalarr, $fat_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($fat_remark) {
                        array_push($newvalarr, $fat_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(meta_acc,transactionID,payment_date,pic,topup_amt,attachment,remark,create_by,create_date,create_time) VALUES ('$fat_acc','$fat_trans_id','$fat_date','$fat_pic','$fat_amt','$fat_attach','$fat_remark','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['meta_acc'] != $fat_acc) {
                        array_push($oldvalarr, $row['meta_acc']);
                        array_push($chgvalarr, $fat_acc);
                        array_push($datafield, 'meta account');
                    }

                    if ($row['transactionID'] != $fat_trans_id) {
                        array_push($oldvalarr, $row['transactionID']);
                        array_push($chgvalarr, $fat_trans_id);
                        array_push($datafield, 'transaction ID');
                    }

                    if ($row['payment_date'] != $fat_date) {
                        array_push($oldvalarr, $row['payment_date']);
                        array_push($chgvalarr, $fat_date);
                        array_push($datafield, 'payment date');
                    }

                    if ($row['pic'] != $fat_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $fat_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['topup_amt'] != $fat_amt) {
                        array_push($oldvalarr, $row['topup_amt']);
                        array_push($chgvalarr, $fat_amt);
                        array_push($datafield, 'topup_amt');
                    }

                    $fat_attach = isset($fat_attach) ? $fat_attach : '';
                    if (($row['attachment'] != $fat_attach) && ($fat_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $fat_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $fat_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $fat_remark == '' ? 'Empty Value' : $fat_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                        
                        $query = "UPDATE " . $tblName  . " SET meta_acc = '$fat_acc', transactionID = '$fat_trans_id', payment_date = '$fat_date', pic = '$fat_pic', topup_amt = '$fat_amt', remark ='$fat_remark', attachment ='$fat_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            $fat_trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord($tblName , $dataID, $fat_trans_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['transactionID'] . "</b> from <b><i>$tblName Table</i></b>.";
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

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-4 autocomplete">
                            <label class="form-label form_lbl" id="fat_meta_acc_lbl" for="fat_meta_acc">Meta
                                Account<span class="requireRed">*</span></label>
                            <?php
                                unset($echoVal);

                                if (isset($row['meta_acc']))
                                    $echoVal = $row['meta_acc'];

                                if (isset($echoVal)) {
                                    $meta_rst = getData('*', "id = '$echoVal'", '', META_ADS_ACC, $finance_connect);
                                    if (!$meta_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $meta_row = $meta_rst->fetch_assoc();
                                }
                                ?>
                            <input class="form-control" type="text" name="fat_meta_acc" id="fat_meta_acc"
                                <?php if ($act == '') echo 'disabled' ?>
                                value="<?php echo !empty($echoVal) ? $meta_row['accName'] : ''  ?>">
                            <input type="hidden" name="fat_meta_acc_hidden" id="fat_meta_acc_hidden"
                                value="<?php echo (isset($row['meta_acc'])) ? $row['meta_acc'] : ''; ?>">


                            <?php if (isset($acc_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $acc_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="fat_trans_lbl" for="fat_trans_id">Transaction ID<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="fat_trans_id" id="fat_trans_id" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['transactionID']) && !isset($fat_trans_id)) {
                                                                                                            echo $row['transactionID'];
                                                                                                        } else if (isset($fat_trans_id)) {
                                                                                                            echo $fat_trans_id;
                                                                                                        }
                                                                                                        ?>"
                                <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($amt_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $id_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label form_lbl" id="fat_date_label" for="fat_date">Invoice/Payment
                                Date<span class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="fat_date" id="fat_date" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['payment_date']) && !isset($fat_date)) {
                                                                                                                echo $row['payment_date'];
                                                                                                            } else if (isset($fat_date)) {
                                                                                                                echo $fat_date;
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
                        <div class="col-md-6 autocomplete">
                            <label class="form-label form_lbl" id="fat_pic_lbl" for="fat_pic">Person-In-Charge<span
                                    class="requireRed">*</span></label>
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
                            <input class="form-control" type="text" name="fat_pic" id="fat_pic"
                                <?php if ($act == '') echo 'disabled' ?>
                                value="<?php echo !empty($echoVal) ? $user_row['name'] : ''  ?>">
                            <input type="hidden" name="fat_pic_hidden" id="fat_pic_hidden"
                                value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">


                            <?php if (isset($pic_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $pic_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="fat_amt_lbl" for="fat_amt">Amount<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="fat_amt" id="fat_amt" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['topup_amt']) && !isset($fat_amt)) {
                                                                                                            echo $row['topup_amt'];
                                                                                                        } else if (isset($fat_amt)) {
                                                                                                            echo $fat_amt;
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
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="fat_attach_lbl" for="fat_attach">Attachment*</label>
                            <input class="form-control" type="file" name="fat_attach" id="fat_attach"
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

                                if (isset($dataExisted) && isset($row['attachment']) && !isset($fat_attach)) {
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                } else if (isset($fat_attach)) {
                                    $attachmentSrc = $img_path . $fat_attach;
                                }
                                ?>
                                <img id="fat_attach_preview" name="fat_attach_preview"
                                    src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="fat_attachmentValue" id="fat_attachmentValue"
                                    value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="fat_remark_lbl" for="fat_remark">Remark</label>
                    <textarea class="form-control" name="fat_remark" id="fat_remark" rows="3"
                        <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
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
    <?php include "../js/fb_ads_topup_trans.js" ?>
    </script>

</body>

</html>