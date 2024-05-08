<?php
$pageTitle = "Official Process Order";
include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = OFFICIAL_PROCESS_ORDER;

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering
$channel = input('channel');
$redirect_page = $SITEURL . '/finance/order_process_list.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");


$dataID = input('id');
$orderID = input('orderid');
$act = input('act');
$pageAction = getPageAction($act);
$clearLocalStorage = '<script>localStorage.clear();</script>';

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

if (post('actionBtn')) {
    $action = post('actionBtn');

    $dfc_courier = postSpaceFilter("dfc_courier_hidden");
    $for_channel = postSpaceFilter('for_channel');
    $usi_tracking = postSpaceFilter('usi_tracking');
    $usi_order_id = postSpaceFilter('usi_order_id');
    $usi_officialoid = postSpaceFilter('usi_officialoid');
    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':

            if (!$dfc_courier) {
                $courier_err = "Courier cannot be empty.";
                break;
            }
            if (!$usi_order_id) {
                $order_err = "Order ID cannot be empty.";
                break;
            }
            if (!$usi_tracking) {
                $tracking_err = "Tracking ID cannot be empty.";
                break;
            }
            if (!$for_channel) {
                $channel_err= "Channel cannot be empty.";
                break;
            }
            if (!$usi_officialoid) {
                $official_err = "Official Order ID cannot be empty.";
                break;
            }
    
            else {
                try {
                    if ($dfc_courier) {
                        array_push($newvalarr, $dfc_courier);
                        array_push($datafield, 'courier_id');
                    }
                    if ($for_channel) {
                        array_push($newvalarr, $for_channel);
                        array_push($datafield, 'channel');
                    }
                    if ($usi_tracking) {
                        array_push($newvalarr, $usi_tracking);
                        array_push($datafield, 'tracking_id');
                    }
                    if ($usi_officialoid) {
                        array_push($newvalarr, $usi_officialoid);
                        array_push($datafield, 'official_order_id');
                    }
                    if ($usi_order_id) {
                        array_push($newvalarr, $usi_order_id);
                        array_push($datafield, 'order_id');
                    }
                    $channel_rst2 = getData('*', "id = '$channel'", '', CHANEL_SC_MD, $finance_connect);
                    $channel_row = $channel_rst2->fetch_assoc();
                    $channelname = $channel_row['name'];
                    if($channelname =='Shopee'){
                        $tableName = SHOPEE_SG_ORDER_REQ;
                    }else if($channelname =='Facebook'){
                        $tableName = FB_ORDER_REQ;
                    }else if($channelname =='Web'){
                        $tableName = WEB_ORDER_REQ;
                    }
                    else if($channelname =='Lazada'){
                        $tableName = LAZADA_ORDER_REQ;
                    }
                    
                    $query = "INSERT INTO " . $tblName . " (official_order_id,courier_id,tracking_id,order_id,channel)VALUES('$usi_officialoid','$dfc_courier','$usi_tracking','$usi_order_id','$for_channel')";
                    $returnData = mysqli_query($connect, $query);
                    $query2 = "UPDATE $tableName SET order_status = 'SP' WHERE id = '$dataID'";
                    $returnData2 = mysqli_query($finance_connect, $query2);
                    $_SESSION['tempValConfirmBox'] = true;
                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        $act = "F";
                    }
            }

            // audit log
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
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                }
                audit_log($log);
            }

            break;

        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
    }
}


//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    }

    $log = [
        'log_act' => $pageAction,
        'cdate' => $cdate,
        'ctime' => $ctime,
        'uid' => USER_ID,
        'cby' => USER_ID,
        'act_msg' => $viewActMsg,
        'page' => $pageTitle,
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
            <p><a href="<?= $redirect_page ?>">
                    <?= $pageTitle ?>
                </a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php
                echo $pageTitle;
                ?>
            </p>

        </div>

        <div id="DFCFormContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="DFCForm" method="post" action="" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                        <?php
                            echo $pageTitle;
                            ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size: 21px;">
                            <?php if (isset($err1))
                                echo $err1; ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="dfc_courier_lbl" for="dfc_courier">Courier<span
                                        class="requireRed">*</span></label>
                                        <?php
                                unset($echoVal);

                                if (isset($row['courier_id']))
                                    $echoVal = $row['courier_id'];
                                    if (isset($echoVal)) {
                                        $courier_rst = getData('name', "id = '$echoVal'", '', COURIER, $connect);
                                        if (!$courier_rst) {
                                            echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                            echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                        }
                                        $courier_row = $courier_rst->fetch_assoc();
                                 
                                    }
                                    ?>
                                    <input class="form-control" type="text" name="dfc_courier" id="dfc_courier" <?php if ($act == '')
                                        echo 'disabled' ?>
                                            value="<?php echo !empty($echoVal) ? $courier_row['name'] : '' ?>">
                                    <input type="hidden" name="dfc_courier_hidden" id="dfc_courier_hidden"
                                        value="<?php echo (isset($row['courier_id'])) ? $row['courier_id'] : ''; ?>">

                               
                            </div>
                            <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="usi_officialoid_lbl" for="usi_officialoid">Official Order ID:<span
                                        class="requireRed"></span></label>
                                <input class="form-control" type="text" name="usi_officialoid" id="usi_officialoid" value=""
                                    <?php if ($act == '') echo 'disabled' ?>>
                                    <?php if (isset($official_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $official_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                          
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="usi_order_id_lbl" for="usi_order_id">Order ID<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="usi_order_id" id="usi_order_id" <?php if ($act == '')

                                    echo 'disabled' ?> value="<?php echo isset($orderID) ? $orderID : ''; ?>">
                                <input type="hidden" name="usi_order_id_hidden" id="usi_order_id_hidden"
                                value="<?php echo isset($orderID) ? $orderID : ''; ?>">

                                <?php if (isset($order_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $order_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="usi_tracking_lbl" for="usi_tracking">Tracking ID:<span
                                        class="requireRed"></span></label>
                                <input class="form-control" type="text" name="usi_tracking" id="usi_tracking" value=""
                                    <?php if ($act == '') echo 'disabled' ?>>
                                    <?php if (isset($tracking_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $tracking_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                           
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                        <div class="col-md-6 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="for_channel_lbl" for="for_channel">Channel<span
                                        class="requireRed">*</span></label>
                                        <?php
                            
                                    if (isset($channel)) {
                                        $channel_rst = getData('*', "id = '$channel'", '', CHANEL_SC_MD, $finance_connect);
                                        if (!$channel_rst) {
                                            echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                            echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                        }
                                        $channel_row = $channel_rst->fetch_assoc();
                                    }

                                    ?>
                                <input class="form-control" type="text" name="for_channel" id="for_channel" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo (isset($row['channel'])) ? $row['channel'] : (isset($channel_row) ? $channel_row['name'] : ''); ?>">
                                <input type="hidden" name="for_channel_hidden" id="for_channel_hidden"
                                value="<?php echo (isset($row['channel'])) ? $row['channel'] : (isset($channel_row) ? $channel_row['id'] : ''); ?>">



                                <?php if (isset($channel_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $channel_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                            <?php
                        switch ($act) {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addTransaction">Update Shipment</button>';
                                break;
                        }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel " name="actionBtn"
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


        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        setButtonColor();
        setAutofocus(action);
        preloader(300, action);

        <?php include "js/update_shipment.js" ?>
    </script>

</body>

</html>