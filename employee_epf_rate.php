<?php
$pageTitle = "Employee EPF Rate";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

echo '<script>var page = "' . $pageTitle . '"; checkCurrentPage(page);</script>';

$tblName = EMPLOYEE_EPF;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/employee_epf_rate_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';
$errorMsgAlert = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", $tblName, $connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $row['name'], $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data ";
    } else {
        $viewActMsg = USER_NAME . " viewed the data <b>" . $row['epf_rate'] . "</b> from <b><i>$tblName Table</i></b>.";
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

            $currentDataEpfRate = postSpaceFilter('currentDataEpfRate');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("epf_rate", $currentDataEpfRate, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " (%)";
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataEpfRate)
                        array_push($newvalarr, $currentDataEpfRate);

                    if ($dataRemark)
                        array_push($newvalarr, $dataRemark);

                    $query = "INSERT INTO " . $tblName . "(epf_rate,remark,create_by,create_date,create_time) VALUES ('$currentDataEpfRate','$dataRemark','" . USER_ID . "',curdate(),curtime())";

                    $returnData = mysqli_query($connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            } else {
                try {
                    if ($row['epf_rate'] != $currentDataEpfRate) {
                        array_push($oldvalarr, $row['epf_rate']);
                        array_push($chgvalarr, $currentDataEpfRate);
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET epf_rate ='$currentDataEpfRate', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($connect, $query);
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
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
                        $log['act_msg'] = USER_NAME . " added <b>$currentDataEpfRate</b> into <b><i>$tblName Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>$currentDataEpfRate</b> into <b><i>$tblName Table</i></b> ( $errorMsg )";
                    }
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($oldvalarr, $chgvalarr, $tblName, (isset($returnData) ? '' : $errorMsg));
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

<body>

    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php echo $pageActionTitle ?>
        </p>
    </div>

    <div id="formContainer" class="container d-flex justify-content-center">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="form" method="post" novalidate>
                <div class="form-group mb-5">
                    <h2>
                        <?php echo $pageActionTitle ?>
                    </h2>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataEpfRate"><?php echo $pageTitle ?> (%)</label>
                    <input class="form-control" type="text" name="currentDataEpfRate" id="currentDataEpfRate" value="<?php if (isset($row['epf_rate'])) echo $row['epf_rate'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                    <div id="err_msg">
                        <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataRemark"><?php echo $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                    <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        centerAlignment("formContainer");
        setButtonColor();
        setAutofocus(action);
    </script>

</body>

</html>