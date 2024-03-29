<?php
$pageTitle = "Internal Consume Item";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = ITL_CSM_ITEM;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


$redirect_page = $SITEURL . '/finance/internal_consume_item_table.php';
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

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, '',$dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

$brand_list_result = getData('*', '', '', BRAND, $connect);

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
            
    $ici_date = postSpaceFilter("ici_date");
    $ici_pic = postSpaceFilter("ici_pic_hidden");
    $ici_brand = postSpaceFilter('ici_brand');
    $ici_package = postSpaceFilter('ici_package_hidden');
    $ici_cost = postSpaceFilter('ici_cost');
    $ici_remark = postSpaceFilter('ici_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    $fields = array('date', 'pic', 'brand', 'package'); // Define fields to check for duplicates
    $values = array($ici_date,$ici_pic, $ici_brand, $ici_package); // Values of the fields
    
    if (isDuplicateRecordWithConditions($fields, $values, $tblName, $finance_connect, $dataID)) {
        $date_err = "Duplicate record found for date, pic, brand, package.";
        break;}


            if (!$ici_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$ici_pic && $ici_pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if (!$ici_brand && $ici_brand < 1) {
                $brand_err = "Please specify the brand.";
                break;
            } else if (!$ici_package && $ici_package < 1) {
                $package_err = "Please specify the package.";
                break;

            } else if ($action == 'addTransaction') {
                try {

                    //check values

                    if ($ici_date) {
                        array_push($newvalarr, $ici_date);
                        array_push($datafield, 'date');
                    }

                    if ($ici_pic) {
                        array_push($newvalarr, $ici_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($ici_brand) {
                        array_push($newvalarr, $ici_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($ici_package) {
                        array_push($newvalarr, $ici_package);
                        array_push($datafield, 'package');
                    }

                    if ($ici_cost) {
                        array_push($newvalarr, $ici_cost);
                        array_push($datafield, 'cost');
                    }

                    if ($ici_remark) {
                        array_push($newvalarr, $ici_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(date,pic,brand,package,cost,remark,create_by,create_date,create_time) VALUES ('$ici_date','$ici_pic','$ici_brand','$ici_package','$ici_cost','$ici_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
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

                    if ($row['date'] != $ici_date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $ici_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['pic'] != $ici_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $ici_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['brand'] != $ici_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $ici_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['package'] != $ici_package) {
                        array_push($oldvalarr, $row['package']);
                        array_push($chgvalarr, $ici_package);
                        array_push($datafield, 'package');
                    }

                    if ($row['cost'] != $ici_cost) {
                        array_push($oldvalarr, $row['cost']);
                        array_push($chgvalarr, $ici_cost);
                        array_push($datafield, 'cost');
                    }

                    if ($row['remark'] != $ici_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $ici_remark == '' ? 'Empty Value' : $ici_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET date = '$ici_date', pic = '$ici_pic', brand = '$ici_brand', package = '$ici_package', cost = '$ici_cost', remark ='$ici_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
    $id = post('id');
    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];

            //SET the record status to 'D'
            deleteRecord($tblName, '', $dataID, '', $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <i>$tblName Table</i></b>.";
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

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="ICIForm" method="post" action="" enctype="multipart/form-data">
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
                            <label class="form-label form_lbl" id="ici_date_label" for="ici_date">Date<span class="requireRed">*</span></label>
                            <input class="form-control" type="date" name="ici_date" id="ici_date" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['date']) && !isset($ici_date)) {
                                                                                                                echo $row['date'];
                                                                                                            } else if (isset($ici_date)) {
                                                                                                                echo $ici_date;
                                                                                                            } else {
                                                                                                                echo date('Y-m-d');
                                                                                                            }
                                                                                                            ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '') echo 'disabled' ?>>
                            <?php if (isset($date_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $date_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="col-md-6 mb-3 autocomplete">
                            <label class="form-label form_lbl" id="ici_pic_lbl" for="ici_pic">Person-In-Charge<span class="requireRed">*</span></label>
                            <?php
                            unset($echoVal);

                            if (isset($row['pic']))
                                $echoVal = $row['pic'];

                            if (isset($echoVal)) {
                                $user_rst = getData('name', "id = '$echoVal'", '', USR_USER, $connect);
                                if (!$user_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $user_row = $user_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="ici_pic" id="ici_pic" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : ''  ?>">
                            <input type="hidden" name="ici_pic_hidden" id="ici_pic_hidden" value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">

                            <?php if (isset($pic_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $pic_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label form_lbl" id="ici_brand_lbl" for="ici_brand">Brand<span class="requireRed">*</span></label>
        <select class="form-select" id="ici_brand" name="ici_brand" <?php if ($act == '') echo 'disabled' ?>>
            <option value="0" disabled selected>Select Brand</option>
            <?php
            if ($brand_list_result->num_rows >= 1) {
                $brand_list_result->data_seek(0);
                while ($row2 = $brand_list_result->fetch_assoc()) {
                    $selected = "";
                    if (isset($dataExisted, $row['brand']) && !isset($ici_brand)) {
                        $selected = $row['brand'] == $row2['id'] ? " selected" : "";
                    } else if (isset($ici_brand)) {
                        $selected = $ici_brand == $row2['id'] ? " selected" : "";
                    }
                    echo "<option value=\"" . $row2['id'] . "\"$selected>" . $row2['name'] . "</option>";
                }
            } else {
                echo "<option value=\"0\">None</option>";
            }
            ?>
        </select>

        <?php if (isset($brand_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1"><?php echo $brand_err; ?></span>
            </div>
        <?php } ?>
    </div>

    <div class="col-md mb-3 autocomplete">
        <label class="form-label form_lbl" id="ici_package_lbl" for="ici_package">Package<span class="requireRed">*</span></label>
        <?php
        unset($echoVal);

        if (isset($row['package']))
            $echoVal = $row['package'];

        if (isset($echoVal)) {
            $package_rst = getData('*', "id = '$echoVal'", '', PKG, $connect);
            if (!$package_rst) {
                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
            }
            $package_row = $package_rst->fetch_assoc();
        }
        ?>
        <input class="form-control" type="text" name="ici_package" id="ici_package" <?php if ($act == '') echo 'disabled' ?>
            value="<?php echo !empty($echoVal) ? $package_row['name'] : '' ?>">
        <input type="hidden" name="ici_package_hidden" id="ici_package_hidden"
            value="<?php echo (isset($row['package'])) ? $row['package'] : ''; ?>">

        <?php if (isset($pkg_err)) { ?>
            <div id="err_msg">
                <span class="mt-n1">
                    <?php echo $pkg_err; ?>
                </span>
            </div>
        <?php } ?>
    </div>
</div>

                            <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="ici_cost_lbl" for="ici_cost">Cost<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="ici_cost" id="ici_cost" value="<?php
                                if (isset($dataExisted) && isset($row['cost']) && !isset($ici_cost)) {
                                    echo $row['cost'];
                                } else if (isset($ici_cost)) {
                                    echo $ici_cost;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($cost_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $cost_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>

<div class="form-group mb-3" style="margin-top: 10px;">
    <label class="form-label form_lbl" id="ici_remark_lbl" for="ici_remark">Remark</label>
    <textarea class="form-control" name="ici_remark" id="ici_remark" rows="3" <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
</div>


                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                        switch ($act) {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addTransaction">Add Item</button>';
                                break;
                            case 'E':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updTransaction">Edit Item</button>';
                                break;
                        }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn" value="back">Back</button>
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
        <?php include "../js/internal_consume_item.js" ?>
    </script>

</body>

</html>