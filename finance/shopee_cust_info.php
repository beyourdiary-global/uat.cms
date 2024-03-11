<?php
$pageTitle = "Shopee Customer Record";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = SHOPEE_CUST_INFO;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addRecord' : 'updRecord';

$redirect_page = $SITEURL . '/finance/shopee_cust_info_table.php';
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
    deleteRecord($tblName, '', $dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addRecord':
        case 'updRecord':

            $scr_username = postSpaceFilter("scr_username");
            $scr_pic = postSpaceFilter("scr_pic_hidden");
            $scr_country = postSpaceFilter("scr_country_hidden");
            $scr_brand = postSpaceFilter("scr_brand_hidden");
            $scr_series = postSpaceFilter("scr_series_hidden");
            $scr_remark = postSpaceFilter("scr_remark");

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (!$scr_username) {
                $name_err = "Shopee Buyer Username cannot be empty";
                break;
            } else if (!$scr_pic) {
                $pic_err = "Sales Person In Charge cannot be empty";
                break;
            } else if (!$scr_country) {
                $country_err = "Country cannot be empty";
                break;
            } else if (!$scr_brand) {
                $brand_err = "Brand cannot be empty";
                break;
            } else if (!$scr_series) {
                $series_err = "Series cannot be empty";
                break;
            } else if ($action == 'addRecord') {
                try {

                    // check value

                    if ($scr_username) {
                        array_push($newvalarr, $scr_username);
                        array_push($datafield, 'name');
                    }

                    if ($scr_pic) {
                        array_push($newvalarr, $scr_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($scr_country) {
                        array_push($newvalarr, $scr_country);
                        array_push($datafield, 'country');
                    }

                    if ($scr_brand) {
                        array_push($newvalarr, $scr_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($scr_series) {
                        array_push($newvalarr, $scr_series);
                        array_push($datafield, 'series');
                    }

                    if ($scr_remark) {
                        array_push($newvalarr, $scr_remark);
                        array_push($datafield, 'remark');
                    }


                    $query = "INSERT INTO " . $tblName . "(buyer_username,pic,country,brand,series,remark,create_by,create_date,create_time) VALUES ('$scr_username','$scr_pic','$scr_country','$scr_brand','$scr_series','$scr_remark','" . USER_ID . "',curdate(),curtime())";

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

                    if ($row['buyer_username'] != $scr_username) {
                        array_push($oldvalarr, $row['buyer_username']);
                        array_push($chgvalarr, $scr_username);
                        array_push($datafield, 'buyer_username');
                    }

                    if ($row['pic'] != $scr_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $scr_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['country'] != $scr_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $scr_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['brand'] != $scr_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $scr_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['series'] != $scr_series) {
                        array_push($oldvalarr, $row['series']);
                        array_push($chgvalarr, $scr_series);
                        array_push($datafield, 'series');
                    }

                    if ($row['remark'] != $scr_remark) {
                        array_push($oldvalarr, $row['remark']);
                        array_push($chgvalarr, $scr_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET buyer_username = '$scr_username', pic = '$scr_pic', country = '$scr_country', brand = '$scr_brand', series = '$scr_series', remark = '$scr_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                }
                audit_log($log);
            }

            break;
        case 'back':
            if ($action == 'addRecord' || $action == 'updRecord') {
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
                echo displayPageAction($act, $pageTitle);
                ?>
            </p>

        </div>

        <div id="SCRformContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="SCRForm" method="post" action="" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                            <?php
                            echo displayPageAction($act, $pageTitle);
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
                            <div class="col-md-12 mb-3">
                                <label class="form-label form_lbl" id="scr_username_lbl" for="scr_username">Shopee Buyer
                                    Username<span class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="scr_username" id="scr_username" value="<?php
                                if (isset($dataExisted) && isset($row['buyer_username']) && !isset($scr_username)) {
                                    echo $row['buyer_username'];
                                } else if (isset($dataExisted) && isset($row['buyer_username']) && isset($scr_username)) {
                                    echo $scr_username;
                                } else {
                                    echo '';
                                } ?>" <?php if ($act == '')
                                     echo 'disabled' ?>>

                                <?php if (isset($username_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $username_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="form-group autocomplete col-md-6 mb-3">
                                <label class="form-label form_lbl" id="scr_pic_lbl" for="scr_pic">Sales Person In
                                    Charge<span class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['pic']))
                                    $echoVal = $row['pic'];

                                if (isset($echoVal)) {
                                    $pic_rst = getData('name', "id = '$echoVal'", '', USR_USER, $connect);
                                    if (!$pic_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $pic_row = $pic_rst->fetch_assoc();
                                }
                                ?>

                                <input class="form-control" type="text" name="scr_pic" id="scr_pic" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $pic_row['name'] : '' ?>">

                                <input type="hidden" name="scr_pic_hidden" id="scr_pic_hidden"
                                    value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">

                                <?php if (isset($pic_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $pic_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group autocomplete col-md-6 mb-3">
                                <label class="form-label form_lbl" id="scr_country_lbl" for="scr_country">Country<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['country']))
                                    $echoVal = $row['country'];

                                if (isset($echoVal)) {
                                    $country_rst = getData('nicename', "id = '$echoVal'", '', COUNTRIES, $connect);
                                    if (!$country_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $country_row = $country_rst->fetch_assoc();
                                }
                                ?>

                                <input class="form-control" type="text" name="scr_country" id="scr_country" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $country_row['nicename'] : '' ?>">

                                <input type="hidden" name="scr_country_hidden" id="scr_country_hidden"
                                    value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">

                                <?php if (isset($country_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $country_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                        <div class="row">
                            <div class="form-group autocomplete col-md-6 mb-3">
                                <label class="form-label form_lbl" id="scr_brand_lbl" for="scr_brand">Brand<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['brand']))
                                    $echoVal = $row['brand'];

                                if (isset($echoVal)) {
                                    $brand_rst = getData('name', "id = '$echoVal'", '', BRAND, $connect);
                                    if (!$brand_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $brand_row = $brand_rst->fetch_assoc();
                                }
                                ?>

                                <input class="form-control" type="text" name="scr_brand" id="scr_brand" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">

                                <input type="hidden" name="scr_brand_hidden" id="scr_brand_hidden"
                                    value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">

                                <?php if (isset($brand_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $brand_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group autocomplete col-md-6 mb-3">
                                <label class="form-label form_lbl" id="scr_series_lbl" for="scr_series">Series<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['series']))
                                    $echoVal = $row['series'];

                                if (isset($echoVal)) {
                                    $series_rst = getData('name', "id = '$echoVal'", '', BRD_SERIES, $connect);
                                    if (!$series_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $series_row = $series_rst->fetch_assoc();
                                }
                                ?>

                                <input class="form-control" type="text" name="scr_series" id="scr_series" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $series_row['name'] : '' ?>">

                                <input type="hidden" name="scr_series_hidden" id="scr_series_hidden"
                                    value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">

                                <?php if (isset($series_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $series_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" id="scr_remark_lbl" for="scr_remark">Remark</label>
                                <textarea class="form-control" name="scr_remark" id="scr_remark" rows="3" <?php if ($act == '')
                                    echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark']))
                                    echo $row['remark'] ?></textarea>
                                </div>

                            </div>
                        </div>


                        <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                            <?php
                                switch ($act) {
                                    case 'I':
                                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addRecord">Add Record</button>';
                                        break;
                                    case 'E':
                                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updRecord">Edit Record</button>';
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
        <?php include "../js/shopee_cust_info.js" ?>

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