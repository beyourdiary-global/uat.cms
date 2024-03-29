<?php
$pageTitle = "Shopee SG Setting";
$isFinance = 1;
include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = SHOPEE_SG_SETT;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';


//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/shopee_sg_setting_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);

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

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, '',$dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');
   
    switch ($action) {
        case 'addData':
        case 'updData':

    $commission = postSpaceFilter('commission');
    $service = postSpaceFilter('service');
    $transaction = postSpaceFilter('transaction');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    $fields = array('commission', 'service', 'transaction'); // Define fields to check for duplicates
    $values = array($commission,$service, $transaction); // Values of the fields

    if (isDuplicateRecordWithConditions($fields, $values, $tblName, $finance_connect, $dataID)) {
                $commission_err = "Duplicate record found for commission fees rate, servise fee rate and transaction fee.";
                break;}
            

            if (!$commission) {
                $commission_err = "Please specify the commission fees rate.";
                break;
            } else if (!$service) {
                $service_err = "Please specify the service fee rate.";
                break;
            } else if (!$transaction) {
                $transaction_err = "Please specify the transaction fee rate.";
                break;

            } else if ($action == 'addData') {
                try {

                     // check value

                    if ($commission) {
                        array_push($newvalarr, $commission);
                        array_push($datafield, 'commission');
                    }

                    if ($service) {
                        array_push($newvalarr, $service);
                        array_push($datafield, 'service');
                    }

                    if ($transaction) {
                        array_push($newvalarr, $transaction);
                        array_push($datafield, 'transaction');
                    }

                    $query = "INSERT INTO " . $tblName . "(commission,service,transaction,create_by,create_date,create_time) VALUES ('$commission','$service',$transaction,'" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $finance_connect->insert_id;
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);
                    $row = $rst->fetch_assoc();
                    
                    if ($row['commission'] != $commission) {
                        array_push($oldvalarr, $row['commission']);
                        array_push($chgvalarr, $commission);
                        array_push($datafield, 'commission');
                    }

                    if ($row['service'] != $service) {
                        array_push($oldvalarr, $row['service']);
                        array_push($chgvalarr, $service);
                        array_push($datafield, 'service');
                    }

                    if ($row['transaction'] != $transaction) {
                        array_push($oldvalarr, $row['transaction']);
                        array_push($chgvalarr, $transaction);
                        array_push($datafield, 'transaction');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                        
                        $query = "UPDATE " . $tblName  . " SET commission = '$commission', service ='$service', transaction ='$transaction', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                if ($action == 'addData' || $action == 'upData') {
                    echo $clearLocalStorage . ' ' . $redirectLink;
                } else {
                    echo $redirectLink;
                }
                break;
    }
}
    

if (post('act') == 'D') {
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            //SET the record status to 'D'
           
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $commission = isset($dataExisted) ? $row['commission'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $commission . "</b> from <b><i>$tblName Table</i></b>.";
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
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
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

    <div id="TAXformContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="TAXForm" method="post" action="" enctype="multipart/form-data">
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


                <div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label form_lbl" id="commission_lbl" for="commission">Commission Fees Rate (%)<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="commission" id="commission" value="<?php 
            if (isset($dataExisted) && isset($row['commission']) && !isset($commission)) {
                echo $row['commission'];
            } else if (isset($dataExisted) && isset($row['commission']) && isset($commission)) {
                echo $commission;
            } else {
                echo '';
            } ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($commission_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $commission_err; ?></span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label form_lbl" id="service_lbl" for="service">Service Fee Rate (%)<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="service" id="service" value="<?php 
            if (isset($dataExisted) && isset($row['service']) && !isset($service)) {
                echo $row['service'];
            } else if (isset($dataExisted) && isset($row['service']) && isset($service)) {
                echo $service;
            } else {
                echo '';
            } ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($service_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $service_err; ?></span>
            </div>
        <?php } ?>
    </div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
        <label class="form-label form_lbl" id="transaction_lbl" for="transaction">Transaction Fee (%)<span class="requireRed">*</span></label>
        <input class="form-control" type="number" step=".01" name="transaction" id="transaction" value="<?php 
            if (isset($dataExisted) && isset($row['transaction']) && !isset($transaction)) {
                echo $row['transaction'];
            } else if (isset($dataExisted) && isset($row['transaction']) && isset($transaction)) {
                echo $transaction;
            } else {
                echo '';
            } ?>" <?php if ($act == '') echo 'disabled' ?>>
        <?php if (isset($transaction_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $transaction_err; ?></span>
            </div>
        <?php } ?>
    </div>
    </div>


                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addData">Add Setting</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updData">Edit Setting</button>';
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
    
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>

    <script>


        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setAutofocus(action);
        setButtonColor();
        preloader(300, action);
        <?php include "../js/shopee_sg_setting.js" ?>
    </script>

</body>

</html>