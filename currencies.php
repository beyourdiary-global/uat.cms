<?php
$pageTitle = "Currencies";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

echo '<script>var page = "' . $pageTitle . '"; checkCurrentPage(page);</script>';

$tblName = CURRENCIES;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/currencies_table.php';
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

//Get Currencies Unit Data
$cur_list_result = getData('*', '', '', CUR_UNIT, $connect);

// currency unit
$cur_unit_arr = array();
if ($cur_list_result != false) {
    while ($row2 = $cur_list_result->fetch_assoc()) {
        $x = $row2['id'];
        $y = $row2['unit'];
        $cur_unit_arr[$x] = $y;
    }
}

//Get Specific Currencies Unit Data
if ($pageAction != 'Add') {
    $resultDeUnit = getData('unit', "id='" . $row['default_currency_unit'] . "'", CUR_UNIT, $connect);
    $resultExUnit = getData('unit', "id='" . $row['exchange_currency_unit'] . "'", CUR_UNIT, $connect);

    if (!$resultDeUnit || !$resultExUnit) {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
    }

    $rowDeUnit = $resultDeUnit->fetch_assoc();
    $rowExUnit = $resultExUnit->fetch_assoc();
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $rowDeUnit['unit'] . "->" . $rowExUnit['unit'], $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data ";
    } else {
        $viewActMsg = USER_NAME . " viewed the data <b>" .  $rowDeUnit['unit'] . "->" . $rowExUnit['unit'] . "</b> from <b><i>$tblName Table</i></b>.";
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

            $dflt_cur_unit = post('dflt_cur_unit');
            $exchg_cur_rate = post('exchg_cur_rate');
            $exchg_cur_unit = post('exchg_cur_unit');
            $dflt_cur_unit = explode(":", $dflt_cur_unit);
            $exchg_cur_unit = explode(":", $exchg_cur_unit);
            $dataRemark = postSpaceFilter('currencies_remark');


            $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("default_currency_unit", $dflt_cur_unit[0], $tblName, $connect, $dataID) && isDuplicateRecord("exchange_currency_rate", $exchg_cur_rate, $tblName, $connect, $dataID) && isDuplicateRecord("exchange_currency_unit", $exchg_cur_unit[0], $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($dflt_cur_unit)
                        array_push($newvalarr, $dflt_cur_unit[0]);

                    if ($exchg_cur_rate)
                        array_push($newvalarr, $exchg_cur_rate);

                    if ($exchg_cur_unit)
                        array_push($newvalarr, $exchg_cur_unit[0]);

                    if ($dataRemark)
                        array_push($newvalarr, $dataRemark);

                    $query = "INSERT INTO " . $tblName . "(default_currency_unit,exchange_currency_rate,exchange_currency_unit,remark,create_by,create_date,create_time) VALUES ('$dflt_cur_unit[0]','$exchg_cur_rate','$exchg_cur_unit[0]','$dataRemark','" . USER_ID . "',curdate(),curtime())";

                    $returnData = mysqli_query($connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            } else {
                try {
                    if ($row['default_currency_unit'] != $dflt_cur_unit[0]) {
                        array_push($oldvalarr, $row['default_currency_unit']);
                        array_push($chgvalarr, $dflt_cur_unit[0]);
                    }

                    if ($row['exchange_currency_rate'] != $exchg_cur_rate) {
                        array_push($oldvalarr, $row['exchange_currency_rate']);
                        array_push($chgvalarr, $exchg_cur_rate);
                    }

                    if ($row['exchange_currency_unit'] != $exchg_cur_unit[0]) {
                        array_push($oldvalarr, $row['exchange_currency_unit']);
                        array_push($chgvalarr, $exchg_cur_unit[0]);
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET default_currency_unit ='$dflt_cur_unit[0]', exchange_currency_rate ='$exchg_cur_rate', exchange_currency_unit ='$exchg_cur_unit[0]', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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

            $resultDeUnit = getData('unit', "id='" . $dflt_cur_unit[0] . "'", CUR_UNIT, $connect);
            $resultExUnit = getData('unit', "id='" . $exchg_cur_unit[0] . "'", CUR_UNIT, $connect);

            if (!$resultDeUnit || !$resultExUnit) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }

            $rowDeUnit = $resultDeUnit->fetch_assoc();
            $rowExUnit = $resultExUnit->fetch_assoc();

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
                        $log['act_msg'] = USER_NAME . " added <b> " . $rowDeUnit['unit'] . "  ->  " . $rowExUnit['unit'] . "  </b> into <b><i>$tblName Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>" . $rowDeUnit['unit'] . "  ->  " . $rowExUnit['unit'] . " </b> into <b><i>$tblName Table</i></b> ( $errorMsg )";
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

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="dflt_cur_unit_lbl" for="dflt_cur_unit">Default Currency Unit</label>
                    <select class="form-select" id="dflt_cur_unit" name="dflt_cur_unit" <?php if ($act == '') echo 'disabled' ?>>
                        <?php
                        if ($cur_list_result->num_rows >= 1) {
                            $cur_list_result->data_seek(0);
                            while ($row2 = $cur_list_result->fetch_assoc()) {
                                $selected = "";
                                if (isset($row['default_currency_unit']))
                                    $selected = $row['default_currency_unit'] == $row2['id'] ? " selected" : "";

                                echo "<option value=\"" . $row2['id'] . ":" . $row2['unit'] . "\"$selected>" . $row2['unit'] . "</option>";
                            }
                        } else {
                            echo "<option value=\"0\">None</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="exchg_cur_rate_lbl" for="exchg_cur_rate">Exchange Currency Rate</label>
                    <input class="form-control" type="number" step="any" name="exchg_cur_rate" id="exchg_cur_rate" value="<?php if (isset($row['exchange_currency_unit'])) echo $row['exchange_currency_rate'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                    <div id="err_msg">
                        <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="dflt_cur_unit_lbl" for="exchg_cur_unit">Exchange Currency Unit</label>
                    <select class="form-select" id="exchg_cur_unit" name="exchg_cur_unit" <?php if ($act == '') echo 'disabled' ?>>
                        <?php
                        if ($cur_list_result->num_rows >= 1) {
                            $cur_list_result->data_seek(0);
                            while ($row2 = $cur_list_result->fetch_assoc()) {
                                $selected = "";
                                if (isset($row['exchange_currency_unit']))
                                    $selected = $row['exchange_currency_unit'] == $row2['id'] ? " selected" : "";

                                echo "<option value=\"" . $row2['id'] . ":" . $row2['unit'] . "\"$selected>" . $row2['unit'] . "</option>";
                            }
                        } else {
                            echo "<option value=\"0\">None</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="currencies_remark_lbl" for="currencies_remark">Currency Unit Remark</label>
                    <textarea class="form-control" name="currencies_remark" id="currencies_remark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
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