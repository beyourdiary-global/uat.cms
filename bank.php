<?php
$pageTitle = "Bank";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = BANK;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page redirect link 
$redirect_page = $SITEURL . '/bank_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

// Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

// to display data to input
if ($dataID && ($rst = getData('*', "id = '$dataID'", $tblName, $connect)) !== false) {
    $dataExisted = 1;
    $row = $rst->fetch_assoc();

    if (empty($row))
        echo '<script>alert("Sorry, currently network temporary fail, please try again later.");</script>' . $redirectLink;
}

if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $currentDataName = postSpaceFilter('currentDataName');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            }

            if ($action == 'addData') {
                try {
                    //Add New Data
                    $query = "INSERT INTO " . $tblName . "(name,remark,create_by,create_date,create_time) VALUES ('$currentDataName','$dataRemark','" . USER_ID . "',curdate(),curtime())";

                    mysqli_query($connect, $query);

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName)
                        array_push($newvalarr, $currentDataName);

                    if ($dataRemark)
                        array_push($newvalarr, $dataRemark);

                    $log = array(
                        'log_act'      => $pageAction,
                        'cdate'        => $cdate,
                        'ctime'        => $ctime,
                        'uid'          => USER_ID,
                        'cby'          => USER_ID,
                        'act_msg'      => USER_NAME . " added <b>$currentDataName</b> into <b><i>$tblName Table</i></b>.",
                        'query_rec'    => $query,
                        'query_table'  => $tblName,
                        'page'         => $pageTitle,
                        'newval'       => implodeWithComma($newvalarr),
                        'connect'      => $connect
                    );
                    audit_log($log);

                    echo $clearLocalStorage;
                } catch (Exception $e) {
                    echo '<script>console.error("Error Message : ' . $e->getMessage() . '");</script>';
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    break;
                }
            } else {
                try {
                    if ($row['name'] != $currentDataName) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $currentDataName);
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty_Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty_Value' : $dataRemark);
                    }

                    if ($oldvalarr && $chgvalarr) {
                        // edit
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        mysqli_query($connect, $query);

                        $_SESSION['tempValConfirmBox'] = true;

                        // audit log
                        $log = [
                            'log_act'      => $pageAction,
                            'cdate'        => $cdate,
                            'ctime'        => $ctime,
                            'uid'          => USER_ID,
                            'cby'          => USER_ID,
                            'oldval'       => implodeWithComma($oldvalarr),
                            'changes'      => implodeWithComma($chgvalarr),
                            'act_msg'      => actMsgLog($oldvalarr, $chgvalarr,$tblName),
                            'query_rec'    => $query,
                            'query_table'  => $tblName,
                            'page'         => $pageTitle,
                            'connect'      => $connect,
                        ];

                        audit_log($log);

                        echo $clearLocalStorage;
                    } else {
                        $_SESSION['tempValConfirmBox'] = true;
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    echo '<script>console.error("Error Message : ' . $e->getMessage() . '");</script>';
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    break;
                }
            }
            break;

        case 'back':
            echo $clearLocalStorage;
            echo $redirectLink;
            break;
    }
}

if ($act == 'D') {
    try {
        deleteRecord($tblName, $dataID, $row['name'], $connect, $cdate, $ctime, $pageTitle);
        $_SESSION['delChk'] = 1;
    } catch (Exception $e) {
        echo '<script>console.error("Error Message : ' . $e->getMessage() . '");</script>';
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    }
}

if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $currentDataName = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = [
        'log_act' => $pageAction,
        'cdate'   => $cdate,
        'ctime'   => $ctime,
        'uid'     => USER_ID,
        'cby'     => USER_ID,
        'act_msg' => USER_NAME . " viewed the data <b>$currentDataName</b> from <b><i>$tblName Table</i></b>.",
        'page'    => $pageTitle,
        'connect' => $connect,
    ];
    audit_log($log);
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
    <link rel="stylesheet" href="./css/main.css">
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
                    <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($dataExisted, $row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                    <div id="err_msg">
                        <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataRemark"><?php echo $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($dataExisted, $row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php echo '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>'; ?>
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