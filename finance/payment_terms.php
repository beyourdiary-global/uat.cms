<?php
$pageTitle = "Payment Terms";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = FIN_PAY_TERMS;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/finance/payment_terms_table.php';
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

    $pay_terms_name = postSpaceFilter("pay_terms_name");
    $pay_terms_desc = postSpaceFilter("pay_terms_desc");
    $pay_terms_remark = postSpaceFilter("pay_terms_remark");

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addPaymentTerms':
        case 'updPaymentTerms':

            if (!$pay_terms_name) {
                $name_err = "Please specify the payment terms name.";
                break;
            } else if ($pay_terms_name && isDuplicateRecord("name", $pay_terms_name, $tblName,  $finance_connect, $dataID)) {
                $name_err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            } else if ($action == 'addPaymentTerms') {
                try {
                    // check value
                    if ($pay_terms_name) {
                        array_push($newvalarr, $pay_terms_name);
                        array_push($datafield, 'name');
                    }
                    if ($pay_terms_desc) {
                        array_push($newvalarr, $pay_terms_desc);
                        array_push($datafield, 'description');
                    }
                    if ($pay_terms_remark) {
                        array_push($newvalarr, $pay_terms_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(name,description,remark,create_by,create_date,create_time) VALUES ('$pay_terms_name','$pay_terms_desc','$pay_terms_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    // generateDBData(FIN_PAY_TERMS, $finance_connect);
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
                    if ($row['name'] != $pay_terms_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $pay_terms_name);
                        array_push($datafield, 'name');
                    }
                    if ($row['description'] != $pay_terms_desc) {
                        array_push($oldvalarr, $row['description']);
                        array_push($chgvalarr, $pay_terms_desc);
                        array_push($datafield, 'description');
                    }

                    if ($row['remark'] != $pay_terms_remark) {
                        array_push($oldvalarr, $row['remark']);
                        array_push($chgvalarr, $pay_terms_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET name = '$pay_terms_name', description = '$pay_terms_desc', remark = '$pay_terms_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);
                        generateDBData(FIN_PAY_TERMS, $finance_connect);
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
            deleteRecord($tblName , '',$dataID, $pay_terms_name, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            generateDBData(FIN_PAY_TERMS, $finance_connect);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $pay_terms_name . "</b> from <b><i>$tblName Table</i></b>.";
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
                                                                                                                    echo displayPageAction($act, $pageTitle);
                                                                                                                    ?>
        </p>

    </div>

    <div id="CBAFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="CBAForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, $pageTitle);
                        ?>
                    </h2>
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label form_lbl" id="pay_terms_name_lbl" for="pay_terms_name">Name</label>
                            <input class="form-control" type="text" name="pay_terms_name" id="pay_terms_name" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['name']) && !isset($pay_terms_name)) {
                                                                                                            echo $row['name'];
                                                                                                        } else if (isset($dataExisted) && isset($row['name']) && isset($pay_terms_name)) {
                                                                                                            echo $pay_terms_name;
                                                                                                        } else {
                                                                                                            echo '';
                                                                                                        } ?>"
                                <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($name_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $name_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label form_lbl" id="pay_terms_desc_lbl" for="pay_terms_desc">Description</label>
                            <input class="form-control" type="text" name="pay_terms_desc" id="pay_terms_desc" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['description']) && !isset($pay_terms_desc)) {
                                                                                                            echo $row['description'];
                                                                                                        } else if (isset($dataExisted) && isset($row['description']) && isset($pay_terms_desc)) {
                                                                                                            echo $pay_terms_desc;
                                                                                                        } ?>"
                                <?php if ($act == '') echo 'disabled' ?>>
                            
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="pay_terms_remark_lbl" for="pay_terms_remark">Remark</label>
                    <textarea class="form-control" name="pay_terms_remark" id="pay_terms_remark" rows="3"
                        <?php if ($act == '') echo 'disabled' ?>><?php
                                                                if (isset($dataExisted) && isset($row['remark']) && !isset($pay_terms_remark)) {
                                                                    echo $row['remark'];
                                                                } else if (isset($dataExisted) && isset($row['remark']) && isset($pay_terms_remark)) {
                                                                    echo $pay_terms_remark;
                                                                } ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addPaymentTerms">Add Payment Terms</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updPaymentTerms">Edit Payment Terms</button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn"
                        value="back">Back</button>
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
    <?php include "../js/fin_payment_terms.js" ?>

    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ''; ?>";

    checkCurrentPage(page, action);
    </script>

</body>

</html>