<?php
session_start();

$pageTitle = "Pin";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = PIN;

$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

$redirect_page = $SITEURL . '/pin_table.php';
$clearLocalStorage = '<script>localStorage.clear();</script>';

$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo "<script>location.href = '$redirect_page';</script>";

$rst = getData('*', "id = '$dataID'", '', $tblName, $connect);

if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $act = "F";
}

if ($act == 'D') {
    deleteRecord($tblName, '', $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
    echo "<script>location.href = '$redirect_page';</script>";
    exit;
}

if ($dataID && !$act && USER_ID && !$_SESSION['viewChk']) {
    $_SESSION['viewChk'] = 1;
    $viewActMsg = isset($errorExist) ?
        USER_NAME . " fail to viewed the data [<b> ID = $dataID</b> ] from <b><i>$tblName Table</i></b>." :
        USER_NAME . " viewed the data [<b> ID = $dataID</b> ] <b>" . $row['name'] . "</b> from <b><i>$tblName Table</i></b>.";

    audit_log([
        'log_act' => $pageAction,
        'cdate' => $cdate,
        'ctime' => $ctime,
        'uid' => USER_ID,
        'cby' => USER_ID,
        'act_msg' => $viewActMsg,
        'page' => $pageTitle,
        'connect' => $connect,
    ]);
}

$successAction = '';

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $currentDataName = postSpaceFilter('currentDataName');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for $pageTitle name.";
                break;
            }

            if ($action == 'addData') {
                try {
                    if ($currentDataName) {
                        $newvalarr[] = $currentDataName;
                        $datafield[] = 'name';
                    }
                    if ($dataRemark) {
                        $newvalarr[] = $dataRemark;
                        $datafield[] = 'remark';
                    }

                    $query = "INSERT INTO $tblName (name,remark,create_by,create_date,create_time)
                              VALUES ('$currentDataName','$dataRemark','" . USER_ID . "',curdate(),curtime())";
                    mysqli_query($connect, $query);
                    $dataID = $connect->insert_id;
                    $successAction = 'Inserted';
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if ($row['name'] != $currentDataName) {
                        $oldvalarr[] = $row['name'];
                        $chgvalarr[] = $currentDataName;
                        $datafield[] = 'name';
                    }

                    if ($row['remark'] != $dataRemark) {
                        $oldvalarr[] = $row['remark'] == '' ? 'Empty Value' : $row['remark'];
                        $chgvalarr[] = $dataRemark == '' ? 'Empty Value' : $dataRemark;
                        $datafield[] = 'remark';
                    }

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE $tblName SET name='$currentDataName', remark='$dataRemark',
                                  update_date=curdate(), update_time=curtime(), update_by='" . USER_ID . "' 
                                  WHERE id = '$dataID'";
                        mysqli_query($connect, $query);
                        $successAction = 'Updated';
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            }

            if (isset($query)) {
                $log = [
                    'log_act' => $pageAction,
                    'cdate' => $cdate,
                    'ctime' => $ctime,
                    'uid' => USER_ID,
                    'cby' => USER_ID,
                    'query_rec' => $query,
                    'query_table' => $tblName,
                    'page' => $pageTitle,
                    'connect' => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, '');
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, '');
                }
                audit_log($log);
            }
            break;

        case 'back':
            echo $clearLocalStorage . "<script>location.href = '$redirect_page';</script>";
            exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>
<body>
<div class="pre-load-center"><div class="preloader"></div></div>

<div class="page-load-cover">
    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a>
            <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?= $pageActionTitle ?>
        </p>
    </div>

    <div id="formContainer" class="container d-flex justify-content-center">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="form" method="post" novalidate>
                <div class="form-group mb-5">
                    <h2><?= $pageActionTitle ?></h2>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataName"><?= $pageTitle ?> Name</label>
                    <input class="form-control" type="text" name="currentDataName" id="currentDataName"
                           value="<?= isset($row['name']) ? $row['name'] : '' ?>"
                           <?= ($act == '') ? 'readonly' : '' ?> required autocomplete="off">
                    <div id="err_msg">
                        <span class="mt-n1" id="errorSpan"><?= isset($err) ? $err : '' ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataRemark"><?= $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3"
                              <?= ($act == '') ? 'readonly' : '' ?>><?= isset($row['remark']) ? $row['remark'] : '' ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?= ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : '' ?>
                    <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var page = "<?= $pageTitle ?>";
    var action = "<?= $act ?>";
    checkCurrentPage(page, action);
    centerAlignment("formContainer");
    setButtonColor();
    preloader(300, action);

    <?php if (!empty($successAction)): ?>
    alert("Data successfully <?= $successAction ?>.");
    window.location.href = "<?= $redirect_page ?>";
    <?php endif; ?>
</script>
</body>
</html>
