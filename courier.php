<?php
$pageTitle = "Courier";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';

$tblName = COURIER;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/courier_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $connect);

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

    $courier_id = postSpaceFilter("courier_id");
    $courier_name = postSpaceFilter("courier_name");
    $courier_country = postSpaceFilter("courier_country_hidden");
    $courier_tax = postSpaceFilter("courier_tax");
    $courier_tracking_link = postSpaceFilter("courier_tracking_link");

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addCourier':
        case 'updCourier':

            if (!$courier_id) {
                $id_err = "Please specify the Courier ID.";
                break;
            } else if (!$courier_name) {
                $name_err = "Please specify the Courier Name.";
                break;
            } else if (!$courier_country) {
                $country_err = "Please specify the Courier Country.";
                break;
            } else if (!$courier_tax && $courier_tax < 1) {
                $tax_err = "Please specify the Courier tax.";
                break;
            }else if (!$courier_tracking_link) {
                    $tracking_link_err = "Please specify the Courier Tracking Link.";
                    break;
            }
            else if ($courier_id && $courier_name && $courier_country && isDuplicateRecord("id", $courier_id, $tblName,  $connect, $dataID) && isDuplicateRecord("name", $courier_name, $tblName,  $connect, $dataID) && isDuplicateRecord("country", $courier_country, $tblName,  $connect, $dataID)) {
                $id_err = "Duplicate record found for " . $pageTitle . " ID, Name and Country.";
                break;
            } else if ($action == 'addCourier') {
                try {

                    // check value

                    if ($courier_id) {
                        array_push($newvalarr, $courier_id);
                        array_push($datafield, 'id');
                    }

                    if ($courier_name) {
                        array_push($newvalarr, $courier_name);
                        array_push($datafield, 'name');
                    }
                    if ($courier_country) {
                        array_push($newvalarr, $courier_country);
                        array_push($datafield, 'country');
                    }
                    if ($courier_tax) {
                        array_push($newvalarr, $courier_tax);
                        array_push($datafield, 'taxable');
                    }

                    if ($courier_tracking_link) {
                        array_push($newvalarr, $courier_tracking_link);
                        array_push($datafield, 'tracking link');
                    }

                    $query = "INSERT INTO " . $tblName  . "(id,name,country,taxable,create_by,create_date,create_time,tracking_link) VALUES ('$courier_id','$courier_name','$courier_country','$courier_tax','" . USER_ID . "',curdate(),curtime(),'$courier_tracking_link')";
                    // Execute the query
                   
                    var_dump($courier_country);
                    var_dump($courier_tax);
                   
                    $returnData = mysqli_query($connect, $query);
                    var_dump($returnData);
                    
                    generateDBData(COURIER, $connect);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['id'] != $courier_id) {
                        array_push($oldvalarr, $row['id']);
                        array_push($chgvalarr, $courier_id);
                        array_push($datafield, 'ID');
                    }

                    if ($row['name'] != $courier_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $courier_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['tracking_link'] != $courier_tracking_link) {
                        array_push($oldvalarr, $row['tracking_link']);
                        array_push($chgvalarr, $courier_tracking_link);
                        array_push($datafield, 'tracking link');
                    }

                    if ($row['country'] != $courier_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $courier_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['taxable'] != $courier_tax) {
                        array_push($oldvalarr, $row['taxable']);
                        array_push($chgvalarr, $courier_tax);
                        array_push($datafield, 'taxable');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET id = '$courier_id',name = '$courier_name',country = '$courier_country',taxable = '$courier_tax', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($connect, $query);
                        generateDBData(COURIER, $connect);
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];

            //SET the record status to 'D'
            deleteRecord($tblName, 'id', $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
            generateDBData(COURIER, $connect);
            $_SESSION['delChk'] = 1;
            
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $acc_id = isset($dataExisted) ? $row['id'] : '';
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
                                                                                                                    echo displayPageAction($act, 'Courier');
                                                                                                                    ?>
        </p>

    </div>

    <div id="CBAFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="CBAForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, 'Courier');
                        ?>
                    </h2>
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label form_lbl" id="courier_id_lbl" for="courier_id">Courier
                                ID<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="courier_id" id="courier_id" value="<?php
                                                                                                        if (isset($dataExisted) && isset($dataID) && !isset($courier_id)) {
                                                                                                            echo $dataID;
                                                                                                        } else if (isset($dataExisted) && isset($dataID) && isset($courier_id)) {
                                                                                                            echo $courier_id;
                                                                                                        } else {
                                                                                                            echo '';
                                                                                                        } ?>"
                                <?php if ($act == '') echo 'disabled' ?>>
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
                            <label class="form-label form_lbl" id="courier_name_lbl" for="courier_name">Courier
                                Name<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="courier_name" id="courier_name" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['name']) && !isset($courier_name)) {
                                                                                                                echo $row['name'];
                                                                                                            } else if (isset($dataExisted) && isset($row['name']) && isset($courier_name)) {
                                                                                                                echo $courier_name;
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
                            <label class="form-label form_lbl" id="courier_tracking_link_lbl" for="courier_tracking_link">Courier
                                Tracking Link<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="courier_tracking_link" id="courier_tracking_link" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['tracking_link']) && !isset($courier_tracking_link)) {
                                                                                                                echo $row['name'];
                                                                                                            } else if (isset($dataExisted) && isset($row['tracking_link']) && isset($courier_tracking_link)) {
                                                                                                                echo $courier_tracking_link;
                                                                                                            } else {
                                                                                                                echo '';
                                                                                                            } ?>"
                                <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($tracking_link_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $tracking_link_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12 autocomplete">
                            <label class="form-label form_lbl" id="courier_country_lbl" for="courier_country">Courier Country<span class="requireRed">*</span></label>
                            <?php
                                unset($echoVal);

                                if (isset($row['country']))
                                    $echoVal = $row['country'];

                                if (isset($echoVal)) {
                                    $country_rst = getData('*', "id = '$echoVal'", '', COUNTRIES, $connect);
                                    if (!$country_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $countries = $country_rst->fetch_assoc();
                                }
                                ?>
                            <input class="form-control" type="text" name="courier_country" id="courier_country"
                                <?php if ($act == '') echo 'disabled' ?>
                                value="<?php echo !empty($echoVal) ? $countries['nicename'] : ''  ?>">
                            <input type="hidden" name="courier_country_hidden" id="courier_country_hidden"
                                value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">


                            <?php if (isset($country_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $country_err; ?></span>
                            </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label form_lbl" id="courier_tax_label" for="courier_tax">Taxable
                                <span class="requireRed">*</span></label>
                            <select class="form-select" name="courier_tax" id="courier_tax" required
                                <?php if ($act == '') echo 'disabled' ?>>
                                <option disabled selected>Select option</option>
                                <option value="Y" <?php
                                                    if (isset($dataExisted, $row['taxable'])  && $row['taxable'] == 'Y'  && (!isset($courier_tax) ||  $courier_tax == 'Y')) {
                                                        echo "selected";
                                                    } else {
                                                        echo "";
                                                    }

                                                    ?>>
                                    Yes</option>
                                <option value="N" <?php
                                                        if (isset($dataExisted, $row['taxable']) && $row['taxable'] == 'N' && (!isset($courier_tax) || $courier_tax == 'N')) {
                                                            echo "selected";
                                                        } else {
                                                            echo "";
                                                        }

                                                        ?>>
                                    No</option>
                            </select>
                            <?php if (isset($tax_err)) { ?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $tax_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addCourier">Add Courier</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updCourier">Edit Courier</button>';
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
    <?php include "js/courier.js" ?>

    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ''; ?>";

    checkCurrentPage(page, action);
    </script>

</body>

</html>