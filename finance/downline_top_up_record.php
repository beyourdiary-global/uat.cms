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

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/downline_top_up_record_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Attachment
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");

$img_path = '../' . img_server . 'finance/downline_top_up_record/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $finance_connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ]  from <b><i>$tblName Table</i></b>.";
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

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $agent = postSpaceFilter('agent');
            $brand = postSpaceFilter('brand');
            $curr_unit = postSpaceFilter('currency_unit');
            $amount = postSpaceFilter('amount');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $attach = null;

            if (isset($_FILES["attachment"]) && $_FILES["attachment"]["size"] != 0) {
                $attach = $_FILES["attachment"]["name"];
                $attachment_tmp_name = $_FILES["attachment"]["tmp_name"];
                $img_ext = pathinfo($attach, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);
                $imgExist = true;
                move_uploaded_file($attachment_tmp_name, $img_path . $attach);
            } else if (isset($_POST['existing_attachment'])) {
                $attach = $_POST['existing_attachment'];
            }

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($agent) {
                        array_push($newvalarr, $agent);
                        array_push($datafield, 'agent');
                    }

                    if ($brand) {
                        array_push($newvalarr, $brand);
                        array_push($datafield, 'brand');
                    }

                    if ($curr_unit) {
                        array_push($newvalarr, $curr_unit);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($amount) {
                        array_push($newvalarr, $amount);
                        array_push($datafield, 'amount');
                    }

                    if ($dataRemark) {
                        array_push($newvalarr, $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    if ($attach) {
                        array_push($newvalarr, $attach);
                        array_push($datafield, 'attachment');
                    }
                    $query = "INSERT INTO " . $tblName . "(agent,brand,currency_unit,amount,attachment,remark,create_by,create_date,create_time) VALUES ('$agent','$brand','$curr_unit','$amount','$attach','$dataRemark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $finance_connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if ($row['agent'] != $agent) {
                        array_push($oldvalarr, $row['agent']);
                        array_push($chgvalarr, $agent);
                        array_push($datafield, 'agent');
                    }


                    if ($row['brand'] != $brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['currency_unit'] != $curr_unit) {
                        array_push($oldvalarr, $row['currency_unit']);
                        array_push($chgvalarr, $curr_unit);
                        array_push($datafield, 'currency_unit');
                    }

                    if ($row['amount'] != $amount) {
                        array_push($oldvalarr, $row['amount']);
                        array_push($chgvalarr, $amount);
                        array_push($datafield, 'amount');
                    }

                    if ($row['attachment'] != $attach) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET agent ='$agent', date='$date',brand='$brand',currency_unit='$curr_unit',amount='$amount',attachment='$attach',remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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

//Function(title, subtitle, page name, ajax url path, redirect path, action)
//To show action dialog after finish certain action (eg. edit)

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<style>
    .requireRed {
        color: red;
    }
</style>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-8 col-md-6 formWidthAdjust">
                <form id="form" method="post" novalidate enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>

                    <div class="form-group mb-3">
    <div class="row">
        <div class="col-md-6 mb-2 autocomplete">
            <label class="form-label form_lbl" id="agent_lbl" for="agent">Agent<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['agent']))
                $echoVal = $row['agent'];

            if (isset($echoVal)) {
                $user_rst = getData('name', "id = '$echoVal'", '', USR_USER, $connect);
                if (!$user_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $user_row = $user_rst->fetch_assoc();
            }
            ?>
            <input class="form-control" type="text" required name="agent" id="agent" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : ''  ?>">
            <input type="hidden" name="agent_hidden" id="agent_hidden" value="<?php echo (isset($row['agent'])) ? $row['agent'] : ''; ?>">
        </div>

        <div class="col-md-6 mb-2">
            <label class="form-label form_lbl" id="brand_lbl" for="brand">Brand<span class="requireRed">*</span></label>
            <select class="form-select" required id="brand" name="brand" <?php if ($act == '') echo 'disabled' ?>>
                <?php
                $resultBrand = getData('*', '', '', BRAND, $connect);

                echo "<option value disabled selected>Select Brand Type</option>";

                if (!$resultBrand) {
                    echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                }
                if ($resultBrand->num_rows >= 1) {
                    while ($rowBrand = $resultBrand->fetch_assoc()) {
                        $selected = isset($row['brand']) && $rowBrand['id'] == $row['brand'] ? "selected" : "";
                        echo "<option value='{$rowBrand['id']}' $selected>{$rowBrand['name']}</option>";
                    }
                } else {
                    echo "<option value=\"0\">None</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>


<div class="form-group mb-3">
    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="form-label form_lbl" id="currency_unit_lbl" for="currency_unit">Currency Unit<span class="requireRed">*</span></label>
            <select class="form-select"  id="currency_unit" name="currency_unit" <?php if ($act == '') echo 'disabled' ?>>
                <?php
                $resultCurUnit = getData('*', '', '', CUR_UNIT, $connect);

                echo "<option value disabled selected>Select Currency Unit</option>";

                if (!$resultCurUnit) {
                    echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                }
                if ($resultBrand->num_rows >= 1) {
                    while ($rowCurUnit = $resultCurUnit->fetch_assoc()) {
                        $selected = isset($row['currency_unit']) && $rowCurUnit['id'] == $row['currency_unit'] ? "selected" : "";
                        echo "<option value='{$rowCurUnit['id']}' $selected>{$rowCurUnit['unit']}</option>";
                    }
                } else {
                    echo "<option value=\"0\">None</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="col-md-6 mb-2">
            <label class="form-label form_lbl" id="amount_lbl" for="amount">Amount<span class="requireRed">*</span></label>
            <input class="form-control"  type="number" step="any" name="amount" id="amount" value="<?php echo (isset($row['amount']) ? $row['amount'] : '') ?>" <?php if ($act == '') echo 'disabled' ?>>
        </div>
    </div>
</div>

                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" for="currentDataRemark">Remark</label>
                        <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label form_lbl" id="attachment_lbl" for="attachment">Attachment</label>
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

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                        <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    

    <script>
        
        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);

        $('#attachment').on('change', function() {
            previewImage(this, 'attach_preview')
        })

        //autocomplete
        $(document).ready(function() {

            if (!($("#pic").attr('disabled'))) {
                $("#pic").keyup(function() {
                    var param = {
                        search: $(this).val(),
                        searchType: 'name', // column of the table
                        elementID: $(this).attr('id'), // id of the input
                        hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                        dbTable: '<?= USR_USER ?>', // json filename (generated when login)
                    }
                    searchInput(param, '<?= $SITEURL ?>');
                });

            }
        })
    </script>

</body>

</html>