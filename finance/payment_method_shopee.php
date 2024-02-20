<?php
$pageTitle = "Payment Method (Shopee)";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = PAY_MTHD_SHOPEE;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addAccount' : 'updAccount';

$redirect_page = $SITEURL . '/finance/payment_method_shopee_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);


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

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName,'', $dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addAccount':
        case 'updAccount':

    $pms_name = postSpaceFilter("pms_name");
    $pms_fees = postSpaceFilter("pms_fees");
    $pms_remark = postSpaceFilter("pms_remark");

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    if (isDuplicateRecord("name", $pms_name, $tblName,  $finance_connect, $dataID)) {
        $name_err = "Duplicate record found for " . $pageTitle . " name.";
        break;
    }

            if (!$pms_name) {
                $name_err = "Please specify the payment method name.";
                break;
           
            } else if ($action == 'addAccount') {
                try {

                    // check value

                    if ($pms_name) {
                        array_push($newvalarr, $pms_name);
                        array_push($datafield, 'name');
                    }

                    if ($pms_fees) {
                        array_push($newvalarr, $pms_fees);
                        array_push($datafield, 'fees');
                    }

                    if ($pms_remark) {
                        array_push($newvalarr, $pms_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(name,fees,remark,create_by,create_date,create_time) VALUES ('$pms_name','$pms_fees','$pms_remark','" . USER_ID . "',curdate(),curtime())";

                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $finance_connect->insert_id;
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

                    if ($row['name'] != $pms_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $pms_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['fees'] != $pms_fees) {
                        array_push($oldvalarr, $row['fees']);
                        array_push($chgvalarr, $pms_fees);
                        array_push($datafield, 'fees');
                    }

                    if ($row['remark'] != $pms_remark) {
                        array_push($oldvalarr, $row['remark']);
                        array_push($chgvalarr, $pms_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                      
                        $query = "UPDATE " . $tblName  . " SET name = '$pms_name', fees = '$pms_fees', remark ='$pms_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);

                     
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
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];

        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $acc_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $acc_name . "</b> from <b><i>$tblName Table</i></b>.";
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
<div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                echo displayPageAction($act, $pageTitle);
                 ?>
            </p>

        </div>

    <div id="FBformContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="FBForm" method="post" action="" enctype="multipart/form-data">
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
        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="pms_name_lbl" for="pms_name">Name<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="pms_name" id="pms_name" value="<?php 
                if (isset($dataExisted) && isset($row['name']) && !isset($pms_name)) {
                    echo $row['name'];
                } else if (isset($dataExisted) && isset($row['name']) && isset($pms_name)) {
                    echo $pms_name;
                } else {
                    echo '';
                } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($name_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $name_err; ?></span>
                </div>
            <?php } ?>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="pms_fees_lbl" for="pms_fees">Transaction fees (%)<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="pms_fees" id="pms_fees" value="<?php 
                if (isset($dataExisted) && isset($row['fees']) && !isset($pms_fees)) {
                    echo $row['fees'];
                } else if (isset($dataExisted) && isset($row['fees']) && isset($pms_fees)) {
                    echo $pms_fees;
                } else {
                    echo '';
                } ?>" <?php if ($act == '') echo 'disabled' ?>>
            <?php if (isset($fees_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $fees_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

                <div class="form-group mb-3">
                        <label class="form-label form_lbl" for="pms_remark">Remark</label>
                        <textarea class="form-control" name="pms_remark" id="pms_remark" rows="3" 
                        <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>
                        
                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addAccount">Add Payment Method</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updAccount">Edit Payment Method</button>';
                            break;
                    }
                    ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn"
                            id="actionBtn" value="back">Back</button>
                    </div>
            </form>
        </div>
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
        <?php include "../js/payment_method_shopee.js" ?>

        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setAutofocus(action);
        setButtonColor();
        preloader(300, action);
    </script>
</body>
</html>