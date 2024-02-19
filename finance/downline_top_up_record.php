<?php
$pageTitle = "Downline Top Up Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = DW_TOP_UP_RECORD;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/downline_top_up_record_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/downline_top_up_record/';
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

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, '',$dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//Edit And Add Data
if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $dtur_agent = postSpaceFilter('dtur_agent_hidden');
            $dtur_brand = postSpaceFilter('dtur_brand_hidden');
            $dtur_curr = postSpaceFilter('dtur_currency');
            $dtur_amount = postSpaceFilter('dtur_amount');

            $dtur_attach = null;
            if (isset($_FILES["dtur_attach"]) && $_FILES["dtur_attach"]["size"] != 0) {
            
                $dtur_attach = $_FILES["dtur_attach"]["name"];
            } else if (isset($_POST['existing_attachment'])) {
                $dtur_attach = $_POST['existing_attachment'];
            }

            $dtur_remark = postSpaceFilter('dtur_remark');
            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if ($_FILES["dtur_attach"]["size"] != 0) {
                // move file
                $dtur_file_name = $_FILES["dtur_attach"]["name"];
                $dtur_file_tmp_name = $_FILES["dtur_attach"]["tmp_name"];
                $img_ext = pathinfo($dtur_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $dataID . '_*.' . $img_ext);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (preg_match('/' . preg_quote($dataID, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $dataID . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($dtur_file_tmp_name, $img_path . $new_file_name)) {
                        $dtur_attach = $new_file_name; 
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

           if (!$dtur_amount) {
                $amount_err = "Please specify the amount.";
                break;

            } else if ($action == 'addData') {
                try {

                    //check values
                    if ($dtur_agent) {
                        array_push($newvalarr, $dtur_agent);
                        array_push($datafield, 'agent');
                    }

                    if ($dtur_brand) {
                        array_push($newvalarr, $dtur_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($dtur_curr) {
                        array_push($newvalarr, $dtur_curr);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($dtur_amount) {
                        array_push($newvalarr, $dtur_amount);
                        array_push($datafield, 'amount');
                    }

                    if ($dtur_attach) {
                        array_push($newvalarr, $dtur_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($dtur_remark) {
                        array_push($newvalarr, $dtur_remark);
                        array_push($datafield, 'remark');
                    }
                    $query = "INSERT INTO " . $tblName  . "(agent,brand,currency_unit,amount,attachment,remark,create_by,create_date,create_time) VALUES ('$dtur_agent','$dtur_brand','$dtur_curr','$dtur_amount','$dtur_attach','$dtur_remark','" . USER_ID . "',curdate(),curtime())";
                    
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
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);
                    $row = $rst->fetch_assoc();

                   // check value
                    if ($row['agent'] != $dtur_agent) {
                        array_push($oldvalarr, $row['agent']);
                        array_push($chgvalarr, $dtur_agent);
                        array_push($datafield, 'agent');
                    }

                    if ($row['brand'] != $dtur_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $dtur_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['currency_unit'] != $dtur_curr) {
                        array_push($oldvalarr, $row['currency_unit']);
                        array_push($chgvalarr, $dtur_curr);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($row['amount'] != $dtur_amount) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $dtur_amount);
                        array_push($datafield, 'amount');
                    }

                    $dtur_attach = isset($dtur_attach) ? $dtur_attach : '';
                    if (($row['attachment'] != $dtur_attach) && ($dtur_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $dtur_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $dtur_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dtur_remark == '' ? 'Empty Value' : $dtur_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET agent ='$dtur_agent',brand='$dtur_brand',currency='$dtur_curr',amount='$dtur_amount',attachment='$dtur_attach',remark ='$dtur_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
    $dtur_agent = isset($dataExisted) ? $row['id'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <i>$tblName Table</i></b>.";
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
<div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                                                                                                                    echo displayPageAction($act, $pageTitle);
                                                                                                                    ?>
            </p>

        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="Form" method="post" action="" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                            <?php
                        echo displayPageAction($act, $pageTitle);
                        ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                    </div>

                    <div class="form-group">
                        <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="dtur_agent_lbl" for="dtur_agent">Agent<span class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);

                            if (isset($row['agent']))
                                $echoVal = $row['agent'];

                            if (isset($echoVal)) {
                                $agent_rst = getData('name', "id = '$echoVal'", '', AGENT, $finance_connect);
                                if (!$agent_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $agent_row = $agent_rst->fetch_assoc();
        
                            }
                            ?>
                            <input class="form-control" type="text" name="dtur_agent" id="dtur_agent" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $agent_row['name'] : ''  ?>">
                            <input type="hidden" name="dtur_agent_hidden" id="dtur_agent_hidden" value="<?php echo (isset($row['agent'])) ? $row['agent'] : ''; ?>">

                            <?php if (isset($agent_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $agent_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="col-md mb-3 autocomplete">
                                <label class="form-label form_lbl" id="dtur_brand_lbl" for="dtur_brand">Brand<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['brand']))
                                    $echoVal = $row['brand'];

                                if (isset($echoVal)) {
                                    $brand_rst = getData('name', "id = '$echoVal'", '', BRAND, $connect);
                                    if (!$brand_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $brand_row = $brand_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="dtur_brand" id="dtur_brand" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
                                <input type="hidden" name="dtur_brand_hidden" id="dtur_brand_hidden"
                                    value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">

                                <?php if (isset($brand_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $brand_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>

                        </div>
                    </div>

<div class="form-group mb-3">
    <div class="row">
        <div class="col-md-6 mb-2">
        <label class="form-label form_lbl" id="dtur_currency_lbl"
                                    for="dtur_currency">Currency Unit<span class="requireRed">*</span></label>
                                <select class="form-select" id="dtur_currency" name="dtur_currency"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                    <option value="0" disabled selected>Select Currency Unit</option>
                                    <?php
                                if ($cur_list_result->num_rows >= 1) {
                                    $cur_list_result->data_seek(0);
                                    while ($row2 = $cur_list_result->fetch_assoc()) {
                                        $selected = "";
                                        if (isset($dataExisted, $row['currency_unit']) && (!isset($dtur_curr))) {
                                            $selected = $row['currency_unit'] == $row2['id'] ? "selected" : "";
                                        } else if (isset($dtur_curr)) {
                                            $selected = $dtur_curr == $row2['id'] ? "selected" : "";
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
        
        <div class="col-md-6 mb-2">
        <label class="form-label form_lbl" id="dtur_amount_lbl" for="dtur_amount">Amount<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="dtur_amount" id="dtur_amount" value="<?php
                                    if (isset($dataExisted) && isset($row['amount']) && !isset($dtur_amount)) {
                                        echo $row['amount'];
                                            } else if (isset($dtur_amount)) {
                                          echo $dtur_amount;
                                         }
                                     ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($amount_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $amount_err; ?></span>
                                </div>
                                <?php } ?>
                            </div>

</div>

                    <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label form_lbl" id="dtur_attach_lbl" for="dtur_attach">Attachment</label>
                            <input class="form-control" type="file" name="dtur_attach" id="dtur_attach"
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
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <?php
                                $attachmentSrc = '';

                                if (isset($dataExisted) && isset($row['attachment']) && !isset($dtur_attach)) {
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                } else if (isset($dtur_attach)) {
                                    $attachmentSrc = $img_path . $dtur_attach;
                                }
                                ?>
                                <img id="dtur_attach_preview" name="dtur_attach_preview"
                                    src="<?php echo $attachmentSrc; ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="dtur_attachmentValue" id="dtur_attachmentValue"
                                    value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" for="dtur_remark">Remark</label>
                    <textarea class="form-control" name="dtur_remark" id="dtur_remark" rows="3" 
                        <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                            <?php
                        switch ($act) {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addData">Add Data</button>';
                                break;
                            case 'E':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updData">Edit Data</button>';
                                break;
                        }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn"
                            id="actionBtn" value="back">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
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

       centerAlignment("formContainer");
       setButtonColor();
       preloader(300, action);
       checkCurrentPage(page, action);
       setAutofocus(action);
       <?php include "../js/dw_top_up_record.js" ?>
   </script>

</body>

</html>
