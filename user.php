<?php
$pageTitle = "User";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

echo '<script>var page = "' . $pageTitle . '"; checkCurrentPage(page);</script>';

$tblName = USR_USER;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/user_table.php';
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
            $dataUsername = postSpaceFilter('dataUsername');
            $userGroup = postSpaceFilter('userGroup');
            $userEmail = postSpaceFilter('currentUserEmail');
            $userPassword = md5($dataUsername);

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for username.";
                $errCount = 1;
            }

            if (isDuplicateRecord("username", $dataUsername, $tblName, $connect, $dataID)) {
                $err2 = "Duplicate record found for user name.";
                $errCount = 1;
            }

            if (isDuplicateRecord("email", $userEmail, $tblName, $connect, $dataID)) {
                $err3 = "Duplicate record found for user email.";
                $errCount = 1;
            }

            if (isset($errCount)) {
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName) {
                        array_push($newvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($dataUsername) {
                        array_push($newvalarr, $dataUsername);
                        array_push($datafield, 'username');
                    }

                    if ($userEmail) {
                        array_push($newvalarr, $userEmail);
                        array_push($datafield, 'email');
                    }

                    if ($userGroup) {
                        array_push($newvalarr, $userGroup);
                        array_push($datafield, 'access_id');
                    }

                    $query = "INSERT INTO " . $tblName . "(name,username,password_alt,email,access_id,create_by,create_date,create_time) VALUES ('$currentDataName','$dataUsername','$userPassword','$userEmail','$userGroup','" . USER_ID . "',curdate(),curtime())";
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

                    if ($row['username'] != $dataUsername) {
                        array_push($oldvalarr, $row['username']);
                        array_push($chgvalarr, $dataUsername);
                        array_push($datafield, 'username');
                    }

                    if ($row['email'] != $userEmail) {
                        array_push($oldvalarr, $row['email']);
                        array_push($chgvalarr, $userEmail);
                        array_push($datafield, 'email');
                    }

                    if ($row['access_id'] != $userGroup) {
                        array_push($oldvalarr, $row['access_id']);
                        array_push($chgvalarr, $userGroup);
                        array_push($datafield, 'access_id');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName', username ='$dataUsername',email ='$userEmail', access_id ='$userGroup', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="currentDataName">Name</label>
                            <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                            <div id="err_msg">
                                <span class="mt-n1" id="errorSpan"><?php if (isset($err1)) echo $err1; ?></span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="dataUsername">Username</label>
                            <input class="form-control" type="text" name="dataUsername" id="dataUsername" value="<?php if (isset($row['username'])) echo $row['username'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                            <div id="err_msg">
                                <span class="mt-n1" id="errorSpan"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="currentUserEmail">Email</label>
                            <input class="form-control" type="text" name="currentUserEmail" id="currentUserEmail" value="<?php if (isset($row['email'])) echo $row['email'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                            <div id="err_msg">
                                <span class="mt-n1" id="errorSpan"><?php if (isset($err3)) echo $err3; ?></span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="currentUsername">User Group</label>
                            <select class="form-select" id="userGroup" name="userGroup" <?php if ($act == '') echo "disabled" ?> required>
                                <option value="" disabled selected style="display:none;">Select User Group</option>
                                <?php
                                $user_grp_list = getData('id,name', '', '', USR_GRP, $connect);
                                if ($user_grp_list) {
                                    while ($row2 = $user_grp_list->fetch_assoc()) {
                                        $selected = '';
                                        $id = $row2['id'];
                                        $grpname = $row2['name'];

                                        if (isset($userGroup)) {
                                            if ($userGroup == $id)
                                                $selected = ' selected';
                                        } else if (isset($dataExisted)) {
                                            if ($row['access_id'] == $id)
                                                $selected = ' selected';
                                        }

                                        echo "<option value=\"$id\" $selected>$grpname</option>";
                                    }
                                } else {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                ?>
                            </select>
                            <div id="err_msg">
                                <span class="mt-n1" id="errorSpan"><?php if (isset($err4)) echo $err4; ?></span>
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

    <script>
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        centerAlignment("formContainer");
        setButtonColor();
        setAutofocus(action);
    </script>

</body>

</html>