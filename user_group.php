<?php
$pageTitle = "User Group";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

echo '<script>var page = "' . $pageTitle . '"; checkCurrentPage(page);</script>';

$tblName = USR_GRP;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/user_group_table.php';
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

//Get Pin and Pin Group Data
$pinResult = getData('*', '', '', PIN, $connect);
$pinGrpResult = getData('*', '', '', PIN_GRP, $connect);

if (!$pinResult || !$pinGrpResult) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo $redirectLink;
}

$pin_arr = array();

if ($dataID) {
    $userGroupResult = getData('*', "id = '$dataID'", '', USR_GRP, $connect);

    if ($userGroupResult) {
        $row = $userGroupResult->fetch_assoc();
        $permission_grp = array();

        // get pin group and pin
        $pins = explode("+", $row['pins']);
        for ($i = 0; $i < count($pins); $i++) {
            $pins[$i] = str_replace("[", "", $pins[$i]);
            $pins[$i] = str_replace("]", "", $pins[$i]);
        }

        foreach ($pins as $x) {
            $colonpos = stripos($x, ":");
            $tmp_pingrp = substr($x, 0, $colonpos);
            $tmp_pin = substr($x, $colonpos);
            $tmp_pin = str_replace(":", "", $tmp_pin);
            $tmp_pin = explode(",", $tmp_pin);
            $permission_grp[$tmp_pingrp] = $tmp_pin;
        }
        $permission_grp_keys = array_keys($permission_grp);
        $permission_grp_count = count($permission_grp);
    } else {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
    }
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
            $dataRemark = postSpaceFilter('currentDataRemark');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            }

            $arr = post('user_grp_chkbox_val');
            $storevalue = array();

            // convert all array into string
            if ($arr) {
                // get pin group
                $keys = implode(",", array_keys($arr));
                $keys_arr = explode(",", $keys);

                foreach ($keys_arr as $x) {
                    $value = implode(",", $arr[$x]);
                    $temp = "[" . $x . ":" . $value . "]";  // ex. [<pingrp>:<permission>]
                    array_push($storevalue, $temp);
                }

                $permission_grp = implode("+", $storevalue);
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName) {
                        array_push($newvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($permission_grp) {
                        array_push($newvalarr, $permission_grp);
                        array_push($datafield, 'pins');
                    }

                    if ($dataRemark) {
                        array_push($newvalarr, $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(name,pins,remark,create_by,create_date,create_time) VALUES ('$currentDataName','$permission_grp','$dataRemark','" . USER_ID . "',curdate(),curtime())";
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

                    if ($row['pins'] != $permission_grp) {
                        array_push($oldvalarr, $row['pins']);
                        array_push($chgvalarr, $permission_grp);
                        array_push($datafield, 'pins');
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName',pins = '$permission_grp', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                    <label class="form-label" for="currentDataName"><?php echo $pageTitle ?> Name</label>
                    <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                    <div id="err_msg">
                        <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                    </div>
                </div>

                <div class="row d-flex justify-content-center">
                    <div class="form-group mb-3">
                        <label class="form-label" id="permission_table_lbl" for="permission_table">Permissions</label>
                        <div class="table-responsive">
                            <table class="table table-striped" id="permission_table">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col"></th>
                                        <?php
                                        if (mysqli_num_rows($pinResult) != 0) {
                                            while ($pin_row = $pinResult->fetch_assoc()) {
                                        ?>
                                                <th class="text-center" scope="col">
                                                    <?php
                                                    echo $pin_row['name'];
                                                    array_push($pin_arr, $pin_row['id']);
                                                    ?>
                                                </th>
                                        <?php
                                            }
                                            $pin_arr_num = count($pin_arr);
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($pinGrpResult) != 0) {
                                        while ($pin_grp_row = $pinGrpResult->fetch_assoc()) {
                                            // get pin
                                            $pin_grp_pins = explode(",", $pin_grp_row['pins']);
                                            $pin_grp_pins_count = count($pin_grp_pins);
                                    ?>
                                            <tr id="<?php echo $pin_grp_row['name'] . '_row[' . $pin_grp_row['id'] . ']' ?>" name="<?php echo $pin_grp_row['name'] . '_row' ?>">
                                                <th scope="row"><?php echo $pin_grp_row['name'] ?></th>
                                                <?php
                                                for ($i = 0; $i < $pin_arr_num; $i++) {
                                                    $found = 0;
                                                    $checked = '';

                                                    for ($j = 0; $j < $pin_grp_pins_count; $j++) {
                                                        // check if pin exist in pin group
                                                        if ($pin_arr[$i] == $pin_grp_pins[$j]) {
                                                            // check if pin checked (act: edit/view)
                                                            if ((isset($act)) && ($act != 'I')) {
                                                                for ($k = 0; $k < $permission_grp_count; $k++) {
                                                                    if ($permission_grp_keys[$k] == $pin_grp_row['id']) {
                                                                        if (is_array($permission_grp[$permission_grp_keys[$k]]) || is_object($permission_grp[$permission_grp_keys[$k]])) {
                                                                            foreach ($permission_grp[$permission_grp_keys[$k]] as $val) {
                                                                                if ($val == $pin_grp_pins[$j])
                                                                                    $checked = " checked";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($act == '')
                                                                $readonly = ' disabled';
                                                            else $readonly = '';

                                                            echo '<td class="text-center" scope="row"><input class="form-check-input" type="checkbox" name="user_grp_chkbox_val[' . $pin_grp_row['id'] . '][]" value="' . $pin_arr[$i] . '"' . $checked . $readonly . '></td>';
                                                            $found = 1;
                                                        }
                                                    }
                                                    if ($found != 1)
                                                        echo '<td scope="row"></td>';
                                                }
                                                ?>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
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
        setButtonColor();
        setAutofocus(action);
    </script>

</body>

</html>