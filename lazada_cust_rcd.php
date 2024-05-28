<?php
$pageTitle = "Lazada Customer Record (Deals)";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';

$tblName = LAZADA_CUST_RCD;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


$redirect_page = $SITEURL . '/lazada_cust_rcd_table.php';
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

$series_list_result = getData('*', '', '', BRD_SERIES, $connect);

if (post('actionBtn')) {
    $action = post('actionBtn');

    $lcr_id = postSpaceFilter('lcr_id');
    $lcr_name = postSpaceFilter('lcr_name');
    $lcr_email = postSpaceFilter('lcr_email');
    $lcr_phone = postSpaceFilter('lcr_phone');
    $lcr_pic = postSpaceFilter('lcr_pic_hidden');
    $lcr_country = postSpaceFilter('lcr_country_hidden');
    $lcr_brand = postSpaceFilter('lcr_brand_hidden');
    $lcr_series = postSpaceFilter('lcr_series');
    $lcr_rec_name = postSpaceFilter('lcr_rec_name');
    $lcr_rec_ctc = postSpaceFilter('lcr_rec_ctc');
    $lcr_rec_add = postSpaceFilter('lcr_rec_add');
    $lcr_remark = postSpaceFilter('lcr_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':
        case 'updRecord':

        

            if (!$lcr_id) {
                $lcr_id_err = "Customer ID cannot be empty.";
                break;
            } else if (!$lcr_name) {
                $name_err = "Name cannot be empty.";
                break;
            } else if (!$lcr_email) {
                $email_err = "Email cannot be empty.";
                break;
            } else if (!$lcr_phone) {
                $phone_err = "Phone cannot be empty.";
                break;
            } else if (!$lcr_pic && $lcr_pic < 1) {
                $pic_err = "Sales Person-In-Charge cannot be empty.";
                break;
            } else if (!$lcr_country && $lcr_country < 1) {
                $country_err = "Country cannot be empty.";
                break;
            } else if (!$lcr_brand && $lcr_brand < 1) {
                $brand_err = "Brand cannot be empty.";
                break;
            } else if (!$lcr_series && $lcr_series < 1) {
                $series_err = "Series cannot be empty.";
                break;
            } else if (!$lcr_rec_name) {
                $rec_name_err = "Receiver Name cannot be empty.";
                break;
            } else if (!$lcr_rec_ctc) {
                $rec_ctc_err = "Receiver Contact cannot be empty.";
                break;
            } else if (!$lcr_rec_add) {
                $rec_add_err = "Receiver Address cannot be empty.";
                break;
            } else if ($action == 'addRecord') {
                try {
                    //check values
                    if ($lcr_id) {
                        array_push($newvalarr, $lcr_name);
                        array_push($datafield, 'lcr_id');
                    }

                    if ($lcr_name) {
                        array_push($newvalarr, $lcr_name);
                        array_push($datafield, 'name');
                    }

                    if ($lcr_email) {
                        array_push($newvalarr, $lcr_email);
                        array_push($datafield, 'email');
                    }

                    if ($lcr_phone) {
                        array_push($newvalarr, $lcr_phone);
                        array_push($datafield, 'phone');
                    }

                    if ($lcr_pic) {
                        array_push($newvalarr, $lcr_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($lcr_country) {
                        array_push($newvalarr, $lcr_country);
                        array_push($datafield, 'country');
                    }

                    if ($lcr_brand) {
                        array_push($newvalarr, $lcr_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($lcr_series) {
                        array_push($newvalarr, $lcr_series);
                        array_push($datafield, 'series');
                    }

                    if ($lcr_rec_name) {
                        array_push($newvalarr, $lcr_rec_name);
                        array_push($datafield, 'receiver name');
                    }

                    if ($lcr_rec_ctc) {
                        array_push($newvalarr, $lcr_rec_ctc);
                        array_push($datafield, 'receiver contact');
                    }

                    if ($lcr_rec_add) {
                        array_push($newvalarr, $lcr_rec_add);
                        array_push($datafield, 'receiver address');
                    }

                    if ($lcr_remark) {
                        array_push($newvalarr, $lcr_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(lcr_id,name,email,phone,sales_pic,country,brand,series,ship_rec_name,ship_rec_add,ship_rec_contact,remark,create_by,create_date,create_time) VALUES ('$lcr_id','$lcr_name','$lcr_email','$lcr_phone','$lcr_pic','$lcr_country','$lcr_brand','$lcr_series','$lcr_rec_name','$lcr_rec_add','$lcr_rec_ctc','$lcr_remark','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($connect, $query);
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
                    if ($row['lcr_id'] != $lcr_id) {
                        array_push($oldvalarr, $row['lcr_id']);
                        array_push($chgvalarr, $lcr_id);
                        array_push($datafield, 'lcr_id');
                    }

                    if ($row['name'] != $lcr_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $lcr_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['email'] != $lcr_email) {
                        array_push($oldvalarr, $row['email']);
                        array_push($chgvalarr, $lcr_email);
                        array_push($datafield, 'email');
                    }

                    if ($row['phone'] != $lcr_phone) {
                        array_push($oldvalarr, $row['phone']);
                        array_push($chgvalarr, $lcr_phone);
                        array_push($datafield, 'phone');
                    }

                    if ($row['sales_pic'] != $lcr_pic) {
                        array_push($oldvalarr, $row['sales_pic']);
                        array_push($chgvalarr, $lcr_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['country'] != $lcr_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $lcr_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['brand'] != $lcr_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $lcr_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['series'] != $lcr_series) {
                        array_push($oldvalarr, $row['series']);
                        array_push($chgvalarr, $lcr_series);
                        array_push($datafield, 'series');
                    }

                    if ($row['ship_rec_name'] != $lcr_rec_name) {
                        array_push($oldvalarr, $row['ship_rec_name']);
                        array_push($chgvalarr, $lcr_rec_name);
                        array_push($datafield, 'shipping receiver name');
                    }

                    if ($row['ship_rec_contact'] != $lcr_rec_ctc) {
                        array_push($oldvalarr, $row['ship_rec_contact']);
                        array_push($chgvalarr, $lcr_rec_ctc);
                        array_push($datafield, 'shipping receiver contact');
                    }

                    if ($row['ship_rec_add'] != $lcr_rec_add) {
                        array_push($oldvalarr, $row['ship_rec_add']);
                        array_push($chgvalarr, $lcr_rec_add);
                        array_push($datafield, 'shipping receiver address');
                    }

                    if ($row['remark'] != $lcr_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $lcr_remark == '' ? 'Empty Value' : $lcr_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET lcr_id = '$lcr_id', name = '$lcr_name', email = '$lcr_email', phone = '$lcr_phone', sales_pic = '$lcr_pic', country = '$lcr_country', brand = '$lcr_brand', series = '$lcr_series', ship_rec_name = '$lcr_rec_name', ship_rec_add = '$lcr_rec_add', ship_rec_contact = '$lcr_rec_ctc', remark ='$lcr_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            deleteRecord($tblName, '', $dataID, $fcb_name, $connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['name'] . "</b> from <b><i>$tblName Table</i></b>.";
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

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="FORForm" method="post" action="" enctype="multipart/form-data">
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
        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lcr_id_lbl" for="lcr_id">Customer ID<span class="requireRed">*</span></label>
            <?php 
            unset($echoVal);
            if (isset($row['lcr_id']))
            $echoVal = $row['lcr_id'];
            ?>
            <input class="form-control" type="text" name="lcr_id" id="lcr_id" value="<?php echo !empty($echoVal) ? $row['lcr_id'] : '' ?>" <?php if ($act == '')echo 'disabled' ?>>       
            <?php if (isset($lcr_id_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $lcr_id_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lcr_name_lbl" for="lcr_name">Name<span class="requireRed">*</span></label>
            <?php 
            unset($echoVal);
            if (isset($row['lcr_name']))
            $echoVal = $row['lcr_name'];
            ?>
            <input class="form-control" type="text" name="lcr_name" id="lcr_name" value="<?php echo !empty($echoVal) ? $row['lcr_name'] : '' ?>" <?php if ($act == '')echo 'disabled' ?>>       
            <?php if (isset($name_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $name_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lcr_email_lbl" for="lcr_email">Customer Email<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="lcr_email" id="lcr_email" value="<?php
                if (isset($dataExisted) && isset($row['email']) && !isset($email)) {
                    echo $row['email'];
                } else if (isset($dataExisted) && isset($row['email']) && isset($lcr_email)) {
                    echo $lcr_email;
                } else {
                    echo '';
                }
                ?>" <?php if ($act == '') echo 'readonly' ?>>
            <?php if (isset($email_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $email_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label form_lbl" id="lcr_phone_lbl" for="lcr_phone">Customer Phone<span class="requireRed">*</span></label>
            <?php 
            unset($echoVal);
            if (isset($row['phone']))
            $echoVal = $row['phone'];
            ?>
            <input class="form-control" type="text" name="lcr_phone" id="lcr_phone" value="<?php echo !empty($echoVal) ? $row['phone'] : '' ?>" <?php if ($act == '')echo 'disabled' ?>>      
            <?php if (isset($phone_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $phone_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
        
<div class="form-group">
    <div class="row">
    <div class="col-md-3 mb-3 autocomplete">
    <label class="form-label form_lbl" id="lcr_pic_lbl" for="lcr_pic">Sales Person In Charge<span class="requireRed">*</span></label>
    <?php
    $loggedInUserId = USER_ID; // Assuming USER_ID contains the ID of the logged-in user
    $defaultUser = '';

    // Retrieve details of the logged-in user
    $user_rst = getData('name', "id = '$loggedInUserId'", '', USR_USER, $connect);
    if ($user_rst && $user_rst->num_rows > 0) {
        $user_row = $user_rst->fetch_assoc();
        $defaultUser = $user_row['name'];
    }
    ?>
    <input class="form-control" type="text" name="lcr_pic" id="lcr_pic" <?php if ($act == '') echo 'disabled' ?> value="<?php echo $defaultUser ?>">
    <input type="hidden" name="lcr_pic_hidden" id="lcr_pic_hidden" value="<?php echo $loggedInUserId ?>">
    <?php if (isset($pic_err)) { ?>
        <div id="err_msg">
            <span class="mt-n1">
                <?php echo $pic_err; ?>
            </span>
        </div>
    <?php } ?>
</div>

        <div class="col-md-3 mb-3 autocomplete country-autocomplete">
            <label class="form-label form_lbl" id="lcr_country_lbl" for="lcr_country">Country<span class="requireRed">*</span></label>
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
            <input class="form-control" type="text" name="lcr_country" id="lcr_country" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $country_row['nicename'] : '' ?>">
            <input type="hidden" name="lcr_country_hidden" id="lcr_country_hidden" value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">
            <?php if (isset($country_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $country_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="col-md-3 mb-3 autocomplete">
            <label class="form-label form_lbl" id="lcr_brand_lbl" for="lcr_brand">Brand<span class="requireRed">*</span></label>
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
            <input class="form-control" type="text" name="lcr_brand" id="lcr_brand" <?php if ($act == '') echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
            <input type="hidden" name="lcr_brand_hidden" id="lcr_brand_hidden" value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">
            <?php if (isset($brand_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1">
                        <?php echo $brand_err; ?>
                    </span>
                </div>
            <?php } ?>
        </div>
        <div class="col-md-3 mb-3 autocomplete">
        <label class="form-label form_lbl" id="lcr_series_lbl" for="lcr_series">Series<span class="requireRed">*</span></label>
            <select class="form-select" id="lcr_series" name="lcr_series" <?php if ($act == '') echo 'disabled' ?>>
                <option value="0" disabled selected>Select Series</option>
                <?php
                if ($series_list_result->num_rows >= 1) {
                    $series_list_result->data_seek(0);
                    while ($series = $series_list_result->fetch_assoc()) {
                        $selected = "";
                        if (isset($dataExisted, $row['series']) && (!isset($lcr_series))) {
                            $selected = $row['series'] == $series['id'] ? "selected" : "";
                        } else if (isset($lcr_series)) {
                            list($lcr_series_id, $lcr_series) = explode(':', $lcr_series);
                            $selected = $lcr_series == $series['id'] ? "selected" : "";
                        }
                        echo "<option value=\"" . $series['id'] . "\" $selected>" . $series['name'] . "</option>";
                    }
                } else {
                    echo "<option value=\"0\">None</option>";
                }
                ?>
            </select>

            <?php if (isset($lcr_series_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $lcr_series_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

                        <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Shipping Receiver Details</legend>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form_lbl" id="lcr_rec_name_lbl"
                                            for="lcr_rec_name">Receiver
                                            Name<span class="requireRed">*</span></label>
                                            <?php 
                                            unset($echoVal);
                                            if (isset($row['ship_rec_name']))
                                            $echoVal = $row['ship_rec_name'];
                                            ?>
                                            <input class="form-control" type="text" name="lcr_rec_name" id="lcr_rec_name" value="<?php echo !empty($echoVal) ? $row['ship_rec_name'] : '' ?>" <?php if ($act == '')echo 'disabled' ?>>       
                                        <?php if (isset($rec_name_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1">
                                                    <?php echo $rec_name_err; ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form_lbl" id="lcr_rec_ctc_lbl"
                                            for="lcr_rec_ctc">Receiver
                                            Contact<span class="requireRed">*</span></label>
                                            <?php 
                                            unset($echoVal);
                                            if (isset($row['ship_rec_contact']))
                                            $echoVal = $row['ship_rec_contact'];
                                            ?>
                                            <input class="form-control" type="text" name="lcr_rec_ctc" id="lcr_rec_ctc" value="<?php echo !empty($echoVal) ? $row['ship_rec_contact'] : '' ?>" <?php if ($act == '')echo 'disabled' ?>>       
                                        <?php if (isset($rec_ctc_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1">
                                                    <?php echo $rec_ctc_err; ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label form_lbl" id="lcr_rec_add_lbl"
                                            for="lcr_rec_add">Receiver
                                            Address<span class="requireRed">*</span></label>
                                            <?php 
                                            unset($echoVal);
                                            if (isset($row['ship_rec_add']))
                                            $echoVal = $row['ship_rec_add'];
                                            ?>
                                            <input class="form-control" type="text" name="lcr_rec_add" id="lcr_rec_add" value="<?php echo !empty($echoVal) ? $row['ship_rec_add'] : '' ?>" <?php if ($act == '')echo 'disabled' ?>>       
                                        <?php if (isset($rec_add_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1">
                                                    <?php echo $rec_add_err; ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>

                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="lcr_remark_lbl" for="lcr_remark">Remark</label>
                            <textarea class="form-control" name="lcr_remark" id="lcr_remark" rows="3" <?php if ($act == '')
                                echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark']))
                                echo $row['remark'] ?></textarea>
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
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        setButtonColor();
        preloader(300, action);

        <?php
        include "./js/lazada_cust_rcd.js"
        ?>
    </script>

</body>

</html>