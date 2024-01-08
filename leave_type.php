<?php
$pageTitle = "Leave Type";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

echo '<script>var page = "' . $pageTitle . '"; checkCurrentPage(page);</script>';

$tblName = L_TYPE;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/leave_type_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['name'] . "</b> from <b><i>$tblName Table</i></b>.";
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

            $currentDataName = postSpaceFilter('currentDataName');
            $autoAssign = postSpaceFilter('currentDataAutoAssign');
            $numOfDays = postSpaceFilter('numOfDays');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle;
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName) {
                        array_push($newvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($numOfDays) {
                        array_push($newvalarr, $numOfDays);
                        array_push($datafield, 'num_of_days');
                    }

                    if ($autoAssign) {
                        array_push($newvalarr, $autoAssign);
                        array_push($datafield, 'auto_assign');
                    }

                    $query = "INSERT INTO " . $tblName . "(name,num_of_days,leave_status,auto_assign,create_by,create_date,create_time) VALUES ('$currentDataName','$numOfDays','Active','$autoAssign','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);
                    $dataID = $connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if ($row['name'] != $currentDataName) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($row['num_of_days'] != $numOfDays) {
                        array_push($oldvalarr, $row['num_of_days']);
                        array_push($chgvalarr, $numOfDays);
                        array_push($datafield, 'num_of_days');
                    }

                    if ($row['auto_assign'] != $autoAssign) {
                        array_push($oldvalarr, $row['auto_assign']);
                        array_push($chgvalarr, $autoAssign);
                        array_push($datafield, 'auto_assign');
                    }


                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName',num_of_days = '$numOfDays', auto_assign = '$autoAssign', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($connect, $query);
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
                <form id="form" method="post" novalidate>

                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>

                    <div class="row d-flex justify-content-center mb-3">
                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                            <label class="form-label form_lbl" for="auto_assign"><?php echo $pageTitle ?> Auto Assign</label>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-check">
                                <label class="form-check-label" for="currentDataAutoAssign">Yes</label>
                                <input class="form-check-input" type="radio" name="currentDataAutoAssign" id="currentDataAutoAssign" value="yes" <?php if ($act == '') echo 'disabled';
                                                                                                                                                    if (isset($row['auto_assign']) && $row['auto_assign'] == "yes") echo ' checked'; ?>>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-check">
                                <label class="form-check-label" for="currentDataAutoAssign">No</label>
                                <input class="form-check-input" type="radio" name="currentDataAutoAssign" id="currentDataAutoAssign" value="no" <?php if ($act == '') echo 'disabled';
                                                                                                                                                if (!isset($row['auto_assign']) || $row['auto_assign'] != "yes") echo ' checked'; ?>>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center" style="margin-top: 10px;">
                        <div class="col-12 col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" for="currentDataName"><?php echo $pageTitle ?></label>
                                <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php echo (isset($row['name'])) ? $row['name'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                                <span class="mt-n1" style="color:red">
                                    <?php if (isset($err)) echo $err; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <div class="col-12 col-md-12">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl for=" numOfDays">Number of Days</label>
                                <input class="form-control" type="number" min="1" step="1" name="numOfDays" id="numOfDays" value="<?php echo (isset($row['num_of_days'])) ? $row['num_of_days'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
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
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);
    </script>

</body>

</html>