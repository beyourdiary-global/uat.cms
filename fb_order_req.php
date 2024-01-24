<?php
$pageTitle = "Facebook Order Request";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';

$tblName = FB_ORDER_REQ;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/fb_order_req_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'fb_order_req/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $connect);

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

    $for_acc = postSpaceFilter("for_meta_acc_hidden");
    $for_trans_id = postSpaceFilter("for_trans_id");
    $for_date = postSpaceFilter("for_date");
    $for_pic = postSpaceFilter("for_pic_hidden");
    $for_bank = postSpaceFilter("for_bank");
    $for_amt = postSpaceFilter('for_amt');
    $for_remark = postSpaceFilter('for_remark');
    
    $for_attach = null;
    if (isset($_FILES["for_attach"]) && $_FILES["for_attach"]["size"] != 0) {
        $for_attach = $_FILES["for_attach"]["name"];
    } elseif (isset($_POST['existing_attachment'])) {
        $for_attach = $_POST['existing_attachment'];
    }

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':
        case 'updRecord':
            if ($_FILES["for_attach"]["size"] != 0) {
                // move file
                $for_file_name = $_FILES["for_attach"]["name"];
                $for_file_tmp_name = $_FILES["for_attach"]["tmp_name"];
                $img_ext = pathinfo($for_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $for_trans_id . '_*.' . $img_ext);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (preg_match('/' . preg_quote($for_trans_id, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int)$matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $for_trans_id . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($for_file_tmp_name, $img_path . $new_file_name)) {
                        $for_attach = $new_file_name; // Update $for_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
            }

            if (!$for_acc && $for_acc < 1) {
                $acc_err = "Please specify the account.";
                break;
            } else if (!$for_trans_id) {
                $id_err = "Please specify the transaction ID.";
                break;
            } else if (!$for_date) {
                $date_err = "Please specify the date.";
                break;
            } else if (!$for_pic && $for_pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if (!$for_amt) {
                $amt_err = "Please specify the top-up amount.";
                break;
            } else if (!$for_attach) {
                $desc_err = "Please attach the proof of payment.";
                break;
            } else if ($action == 'addRecord') {
                try {
                    //check values
                    if ($for_acc) {
                        array_push($newvalarr, $for_acc);
                        array_push($datafield, 'account');
                    }
                    if ($for_trans_id) {
                        array_push($newvalarr, $for_trans_id);
                        array_push($datafield, 'transaction ID');
                    }

                    if ($for_date) {
                        array_push($newvalarr, $for_date);
                        array_push($datafield, 'payment date');
                    }

                    if ($for_pic) {
                        array_push($newvalarr, $for_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($for_amt) {
                        array_push($newvalarr, $for_amt);
                        array_push($datafield, 'top-up amount');
                    }

                    if ($for_attach) {
                        array_push($newvalarr, $for_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($for_remark) {
                        array_push($newvalarr, $for_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(meta_acc,transactionID,payment_date,pic,topup_amt,attachment,remark,create_by,create_date,create_time) VALUES ('$for_acc','$for_trans_id','$for_date','$for_pic','$for_amt','$for_attach','$for_remark','" . USER_ID . "',curdate(),curtime())";
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
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $connect);
                    $row = $rst->fetch_assoc();

                    // check value
                    if ($row['meta_acc'] != $for_acc) {
                        array_push($oldvalarr, $row['meta_acc']);
                        array_push($chgvalarr, $for_acc);
                        array_push($datafield, 'meta account');
                    }

                    if ($row['transactionID'] != $for_trans_id) {
                        array_push($oldvalarr, $row['transactionID']);
                        array_push($chgvalarr, $for_trans_id);
                        array_push($datafield, 'transaction ID');
                    }

                    if ($row['payment_date'] != $for_date) {
                        array_push($oldvalarr, $row['payment_date']);
                        array_push($chgvalarr, $for_date);
                        array_push($datafield, 'payment date');
                    }

                    if ($row['pic'] != $for_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $for_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['topup_amt'] != $for_amt) {
                        array_push($oldvalarr, $row['topup_amt']);
                        array_push($chgvalarr, $for_amt);
                        array_push($datafield, 'topup_amt');
                    }

                    $for_attach = isset($for_attach) ? $for_attach : '';
                    if (($row['attachment'] != $for_attach) && ($for_attach != '')) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $for_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $for_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $for_remark == '' ? 'Empty Value' : $for_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                        
                        $query = "UPDATE " . $tblName  . " SET meta_acc = '$for_acc', transactionID = '$for_trans_id', payment_date = '$for_date', pic = '$for_pic', topup_amt = '$for_amt', remark ='$for_remark', attachment ='$for_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName , $connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            $for_trans_id = $row['transactionID'];

            //SET the record status to 'D'
            deleteRecord($tblName , $dataID, $for_trans_id, $connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['transactionID'] . "</b> from <b><i>$tblName Table</i></b>.";
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
                <form id="FORForm" method="post" action="" enctype="multipart/form-data">
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

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="for_name_lbl" for="for_name">Name<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="for_name" id="for_name" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['name']) && !isset($for_name)) {
                                                                                                            echo $row['name'];
                                                                                                        } else if (isset($for_name)) {
                                                                                                            echo $for_name;
                                                                                                        }
                                                                                                        ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($name_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $name_err; ?></span>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="for_link_lbl" for="for_link">Facebook Link<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="for_link" id="for_link" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['fb_link']) && !isset($for_link)) {
                                                                                                            echo $row['fb_link'];
                                                                                                        } else if (isset($for_link)) {
                                                                                                            echo $for_link;
                                                                                                        }
                                                                                                        ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($link_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $link_err; ?></span>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="for_contact_lbl" for="for_contact">Contact<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="for_contact" id="for_contact" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['contact']) && !isset($for_contact)) {
                                                                                                            echo $row['contact'];
                                                                                                        } else if (isset($for_contact)) {
                                                                                                            echo $for_contact;
                                                                                                        }
                                                                                                        ?>"
                                    <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($contact_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $contact_err; ?></span>
                                </div>
                                <?php } ?>
                            </div>


                        </div>

                    </div>
                    <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
                        <legend class="float-none w-auto p-2">Order Request Details</legend>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_pic_lbl" for="for_pic">Sales Person In
                                        Charge<span class="requireRed">*</span></label>
                                    <?php
                                unset($echoVal);

                                if (isset($row['sales_pic']))
                                    $echoVal = $row['sales_pic'];

                                if (isset($echoVal)) {
                                    $user_rst = getData('name', "id = '$echoVal'", '', USR_USER, $connect);
                                    if (!$user_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $user_row = $user_rst->fetch_assoc();
                                }
                                ?>
                                    <input class="form-control" type="text" name="for_pic" id="for_pic"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $user_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_pic_hidden" id="for_pic_hidden"
                                        value="<?php echo (isset($row['sales_pic'])) ? $row['sales_pic'] : ''; ?>">


                                    <?php if (isset($pic_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $pic_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-6 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_country_lbl"
                                        for="for_country">Country<span class="requireRed">*</span></label>
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
                                    <input class="form-control" type="text" name="for_country" id="for_country"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $country_row['nicename'] : ''  ?>">
                                    <input type="hidden" name="for_country_hidden" id="for_country_hidden"
                                        value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">


                                    <?php if (isset($country_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $country_err; ?></span>
                                    </div>
                                    <?php } ?>

                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_brand_lbl" for="for_brand">Brand<span
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
                                    <input class="form-control" type="text" name="for_brand" id="for_brand"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_brand_hidden" id="for_brand_hidden"
                                        value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">


                                    <?php if (isset($brand_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $brand_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-4 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_series_lbl" for="for_series">Series<span
                                            class="requireRed">*</span></label>
                                    <?php
                                unset($echoVal);

                                if (isset($row['series']))
                                    $echoVal = $row['series'];

                                if (isset($echoVal)) {
                                    $series_rst = getData('name', "id = '$echoVal'", '', BRD_SERIES, $connect);
                                    if (!$brand_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $series_row = $series_rst->fetch_assoc();
                                }
                                ?>
                                    <input class="form-control" type="text" name="for_series" id="for_series"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $series_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_series_hidden" id="for_series_hidden"
                                        value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">


                                    <?php if (isset($series_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $series_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-4 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_pkg_lbl" for="for_pkg">Package<span
                                            class="requireRed">*</span></label>
                                    <?php
                                unset($echoVal);

                                if (isset($row['package']))
                                    $echoVal = $row['package'];

                                if (isset($echoVal)) {
                                    $pkg_rst = getData('name', "id = '$echoVal'", '', PKG, $connect);
                                    if (!$pkg_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $pkg_row = $pkg_rst->fetch_assoc();
                                }
                                ?>
                                    <input class="form-control" type="text" name="for_pkg" id="for_pkg"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $pkg_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_pkg_hidden" id="for_pkg_hidden"
                                        value="<?php echo (isset($row['package'])) ? $row['package'] : ''; ?>">


                                    <?php if (isset($pkg_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $pkg_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_fb_page_lbl" for="for_fbpage">Facebook
                                        Page<span class="requireRed">*</span></label>
                                    <?php
                                unset($echoVal);

                                if (isset($row['fb_page']))
                                    $echoVal = $row['fb_page'];

                                if (isset($echoVal)) {
                                    $brand_rst = getData('name', "id = '$echoVal'", '', BRAND, $connect);
                                    if (!$brand_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $brand_row = $brand_rst->fetch_assoc();
                                }
                                ?>
                                    <input class="form-control" type="text" name="for_fbpage" id="for_fbpage"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_fbpage_hidden" id="for_fbpage_hidden"
                                        value="<?php echo (isset($row['fb_page'])) ? $row['fb_page'] : ''; ?>">


                                    <?php if (isset($fbpage_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $fbpage_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-4 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_series_lbl" for="for_series">Channel<span
                                            class="requireRed">*</span></label>
                                    <?php
                                unset($echoVal);

                                if (isset($row['series']))
                                    $echoVal = $row['series'];

                                if (isset($echoVal)) {
                                    $series_rst = getData('name', "id = '$echoVal'", '', BRD_SERIES, $connect);
                                    if (!$brand_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $series_row = $series_rst->fetch_assoc();
                                }
                                ?>
                                    <input class="form-control" type="text" name="for_series" id="for_series"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $series_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_series_hidden" id="for_series_hidden"
                                        value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">


                                    <?php if (isset($series_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $series_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-4 mb-3 autocomplete">
                                    <label class="form-label form_lbl" id="for_pay_meth_lbl" for="for_pay_meth">Payment
                                        Method<span class="requireRed">*</span></label>
                                    <?php
                                unset($echoVal);

                                if (isset($row['pay_method']))
                                    $echoVal = $row['pay_method'];

                                if (isset($echoVal)) {
                                    $pay_rst = getData('name', "id = '$echoVal'", '', FIN_PAY_METH, $connect);
                                    if (!$pay_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $pay_row = $pay_rst->fetch_assoc();
                                }
                                ?>
                                    <input class="form-control" type="text" name="for_pay_meth" id="for_pay_meth"
                                        <?php if ($act == '') echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $pay_row['name'] : ''  ?>">
                                    <input type="hidden" name="for_pay_meth_hidden" id="for_pay_meth_hidden"
                                        value="<?php echo (isset($row['pay_method'])) ? $row['pay_method'] : ''; ?>">


                                    <?php if (isset($pay_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $pay_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
                        <legend class="float-none w-auto p-2">Shipping Receiver Details</legend>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label form_lbl" id="for_rec_name_lbl" for="for_rec_name">Receiver
                                        Name<span class="requireRed">*</span></label>
                                    <input class="form-control" type="text" name="for_rec_name" id="for_rec_name" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['ship_rec_name']) && !isset($for_rec_name)) {
                                                                                                            echo $row['ship_rec_name'];
                                                                                                        } else if (isset($for_rec_name)) {
                                                                                                            echo $for_rec_name;
                                                                                                        }
                                                                                                        ?>"
                                        <?php if ($act == '') echo 'disabled' ?>>
                                    <?php if (isset($rec_name_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $rec_name_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label form_lbl" id="for_rec_ctc_lbl" for="for_rec_ctc">Receiver
                                        Contact<span class="requireRed">*</span></label>
                                    <input class="form-control" type="number" name="for_rec_ctc" id="for_rec_ctc" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['ship_rec_contact']) && !isset($for_rec_ctc)) {
                                                                                                            echo $row['ship_rec_contact'];
                                                                                                        } else if (isset($for_rec_ctc)) {
                                                                                                            echo $for_rec_ctc;
                                                                                                        }
                                                                                                        ?>"
                                        <?php if ($act == '') echo 'disabled' ?>>
                                    <?php if (isset($rec_ctc_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $rec_ctc_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label form_lbl" id="for_rec_add_lbl" for="for_rec_add">Receiver
                                        Address<span class="requireRed">*</span></label>
                                    <input class="form-control" type="text" name="for_rec_add" id="for_rec_add" value="<?php
                                                                                                        if (isset($dataExisted) && isset($row['ship_rec_add']) && !isset($for_rec_add)) {
                                                                                                            echo $row['ship_rec_add'];
                                                                                                        } else if (isset($for_rec_add)) {
                                                                                                            echo $for_rec_add;
                                                                                                        }
                                                                                                        ?>"
                                        <?php if ($act == '') echo 'disabled' ?>>
                                    <?php if (isset($rec_add_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $rec_add_err; ?></span>
                                    </div>
                                    <?php } ?>
                                </div>

                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" id="for_remark_lbl" for="for_remark">Remark</label>
                        <textarea class="form-control" name="for_remark" id="for_remark" rows="3"
                            <?php if ($act == '') echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="for_attach_lbl"
                                    for="for_attach">Attachment*</label>
                                <input class="form-control" type="file" name="for_attach" id="for_attach"
                                    <?php if ($act == '') echo 'disabled' ?>>

                                <?php if (isset($row['attachment']) && $row['attachment']) { ?>
                                <div id="err_msg">
                                    <span
                                        class="mt-n1"><?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?></span>
                                </div>
                                <input type="hidden" name="existing_attachment"
                                    value="<?php echo htmlspecialchars($row['attachment']); ?>">
                                <?php } ?>

                                <?php if (isset($attach_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $attach_err; ?></span>
                                </div>
                                <?php } ?>

                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-center justify-content-md-end px-4">
                                    <?php
                                $attachmentSrc = '';

                                if (isset($dataExisted) && isset($row['attachment']) && !isset($for_attach)) {
                                    $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                } else if (isset($for_attach)) {
                                    $attachmentSrc = $img_path . $for_attach;
                                }
                                ?>
                                    <img id="for_attach_preview" name="for_attach_preview"
                                        src="<?php echo $attachmentSrc; ?>" class="img-thumbnail"
                                        alt="Attachment Preview">
                                    <input type="hidden" name="for_attachmentValue" id="for_attachmentValue"
                                        value="<?php if (isset($row['attachment'])) echo $row['attachment']; ?>">
                                </div>
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
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";
    var managerAssignJSON = <?php echo isset($managerAssignJSON) ? $managerAssignJSON : '""' ?>;

    checkCurrentPage(page, action);
    centerAlignment("formContainer");
    setButtonColor();
    preloader(300, action);

    // <?php include "./js/fb_order_req.js" ?>
    </script>

</body>

</html>