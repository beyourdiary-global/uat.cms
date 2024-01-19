<?php
$pageTitle = "Meta Ads Account";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = META_ADS_ACC;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/finance/meta_ads_acc_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

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

if (post('actionBtn')) {
    $action = post('actionBtn');

    $maa_id = postSpaceFilter("maa_id");
    $maa_name = postSpaceFilter("maa_name");

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addAccount':
        case 'updAccount':

            if (!$maa_id) {
                $id_err = "Please specify the account ID.";
                break;
            } else if ($maa_id && isDuplicateRecord("accID", $maa_id, $tblName,  $finance_connect, $dataID)) {
                $id_err = "Duplicate record found for " . $pageTitle . " ID.";
                break;
            } else if (!$maa_name) {
                $name_err = "Please specify the account name.";
                break;
            } else if ($action == 'addAccount') {
                try {

                    // check value

                    if ($maa_id) {
                        array_push($newvalarr, $maa_id);
                        array_push($datafield, 'id');
                    }

                    if ($maa_name) {
                        array_push($newvalarr, $maa_name);
                        array_push($datafield, 'name');
                    }

                    $query = "INSERT INTO " . $tblName  . "(accID,accName,create_by,create_date,create_time) VALUES ('$maa_id','$maa_name','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    generateDBData(META_ADS_ACC, $finance_connect);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['accID'] != $maa_id) {
                        array_push($oldvalarr, $row['accID']);
                        array_push($chgvalarr, $maa_id);
                        array_push($datafield, 'ID');
                    }

                    if ($row['accName'] != $maa_name) {
                        array_push($oldvalarr, $row['accName']);
                        array_push($chgvalarr, $maa_name);
                        array_push($datafield, 'name');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET accID = '$maa_id',accName = '$maa_name', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);
                        generateDBData(META_ADS_ACC, $finance_connect);
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            //SET the record status to 'D'
            deleteRecord($tblName , $dataID, $maa_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            generateDBData(META_ADS_ACC, $finance_connect);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $acc_id = isset($dataExisted) ? $row['accID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $acc_id . "</b> from <b><i>$tblName Table</i></b>.";
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
    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                                                                                                                    echo displayPageAction($act, 'Account');
                                                                                                                    ?></p>

    </div>

    <div id="CBAFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="CBAForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, 'Account');
                        ?>
                    </h2>
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label form_lbl" id="maa_id_lbl" for="maa_id">Account
                                ID</label>
                            <input class="form-control" type="text" name="maa_id" id="maa_id" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['accID']) && !isset($maa_id)) {
                                                                                                            echo $row['accID'];
                                                                                                        } else if (isset($dataExisted) && isset($row['accID']) && isset($maa_id)) {
                                                                                                            echo $maa_id;
                                                                                                        } else {
                                                                                                            echo '';
                                                                                                        } ?>" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($id_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $id_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label form_lbl" id="maa_name_lbl" for="maa_name">Account
                                Name</label>
                            <input class="form-control" type="text" name="maa_name" id="maa_name" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['accName']) && !isset($maa_name)) {
                                                                                                                echo $row['accName'];
                                                                                                            } else if (isset($dataExisted) && isset($row['accName']) && isset($maa_name)) {
                                                                                                                echo $maa_name;
                                                                                                            } else {
                                                                                                                echo '';
                                                                                                            } ?>" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($name_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $name_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addAccount">Add Account</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updAccount">Edit Account</button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    /*
        oufei 20231014
        common.fun.js
        function(title, subtitle, page name, ajax url path, redirect path, action)
        to show action dialog after finish certain action (eg. edit)
    */
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
        <?php include "../js/meta_ads_acc.js" ?>

        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
    </script>

</body>

</html>