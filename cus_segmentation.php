<?php
$pageTitle = "Customer Segmentation";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = CUR_SEGMENTATION;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/cus_segmentation_table.php';
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
    deleteRecord($tblName, '', $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
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
            $colorSegmentation =  postSpaceFilter('segmentationColor');
            $currentDataboxFrom = postSpaceFilter('boxFrom');
            $currentDataboxUntil = postSpaceFilter('boxUntil');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                $errorCount  = 1;
            }

            if (isDuplicateRecord("colorCode", $colorSegmentation, $tblName, $connect, $dataID)) {
                $err2 = "Duplicate record found for " . $pageTitle . " color code.";
                $errorCount  = 1;
            }

            if (isset($errorCount)) {
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName) {
                        array_push($newvalarr, $currentDataName);
                        array_push($datafield, 'name');
                    }

                    if ($colorSegmentation)
                        array_push($newvalarr, $colorSegmentation);

                    if ($currentDataboxFrom)
                        array_push($newvalarr, $currentDataboxFrom);

                    if ($currentDataboxUntil)
                        array_push($newvalarr, $currentDataboxUntil);

                    if ($dataRemark)
                        array_push($newvalarr, $dataRemark);

                    $query = "INSERT INTO " . $tblName . "(name,colorCode,remark,boxFrom,boxUntil,create_by,create_date,create_time) VALUES ('$currentDataName','$colorSegmentation','$dataRemark','$currentDataboxFrom','$currentDataboxUntil','" . USER_ID . "',curdate(),curtime())";

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

                    if ($row['colorCode'] != $colorSegmentation) {
                        array_push($oldvalarr, $row['colorCode']);
                        array_push($chgvalarr, $colorSegmentation);
                        array_push($datafield, 'colorCode');
                    }

                    if ($row['box_from'] != $currentDataboxFrom) {
                        array_push($oldvalarr, $row['boxFrom']);
                        array_push($chgvalarr, $currentDataboxFrom);
                    }

                    if ($row['box_until'] != $currentDataboxUntil) {
                        array_push($oldvalarr, $row['boxUntil']);
                        array_push($chgvalarr, $currentDataboxUntil);
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName', colorCode = '$colorSegmentation' , boxFrom='$currentDataboxFrom', boxUntil='$currentDataboxUntil', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm">
                                <label class="form-label" for="currentDataName"><?php echo $pageTitle ?> Name*</label>
                                <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                                <div id="err_msg">
                                    <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm">
                                <label class=" form-label" for="segmentationColor"><?php echo $pageTitle ?> Color</label><br>
                                <div class="col d-flex justify-content-start align-items-center">
                                    <input type="color" name="segmentationColor" id="segmentationColor" <?php if ($act == '') echo 'disabled ' ?> value="<?php if (isset($row['colorCode'])) echo $row['colorCode'] ?>" class="form-control" style="height: 40px;">
                                    <span id="color-display"><?php if (isset($dataExisted) && isset($row['colorCode'])) echo $row['colorCode']; ?></span>
                                </div>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm">
                                <label class="form-label" for="boxFrom">Box From*</label>
                                <input class="form-control" type="text" name="boxFrom" id="boxFrom" value="<?php if (isset($row['boxFrom'])) echo $row['boxFrom'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off" oninput="validateNumericInput(this, 'boxFromErrorMsg', 'boxUntilErrorMsg')">
                                <div id="boxFromErrorMsg" class="error-message">
                                    <span class="mt-n1"></span>
                                </div>
                            </div>
                            <div class="col-sm">
                                <label class="form-label" for="boxUntil">Box Until*</label>
                                <input class="form-control" type="text" name="boxUntil" id="boxUntil" value="<?php if (isset($row['boxUntil'])) echo $row['boxUntil'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off" oninput="validateNumericInput(this, 'boxUntilErrorMsg', 'boxFromErrorMsg')">
                                <div id="boxUntilErrorMsg" class="error-message">
                                    <span class="mt-n1"></span>
                                </div>
                            </div>
                            <div class="col-sm autocomplete">
                                <label class="form-label" for="brandSeries">Brand Series</label>
                                <input class="form-control" type="text" name="brandSeries" id="brandSeries" value="<?php if (isset($row['brandSeries'])) echo $row['brandSeries'] ?>" <?php if ($act == '') echo 'readonly' ?> autocomplete="off">
                                <div id="brandSeriesErrorMsg" class="error-message">
                                    <span class="mt-n1"></span>
                                </div>
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
    </div>
    <script>
        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);
        
        <?php include "js/cus_segmentation.js" ?>
    </script>

</body>

</html>