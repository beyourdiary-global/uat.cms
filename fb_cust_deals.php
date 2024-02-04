<?php
$pageTitle = "Facebook Customer Record (Deals)";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';

$tblName = FB_CUST_DEALS;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


$redirect_page = $SITEURL . '/fb_cust_deals_table.php';
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

    $fcb_name = postSpaceFilter('fcb_name');
    $fcb_link = postSpaceFilter('fcb_link');
    $fcb_ctc = postSpaceFilter('fcb_contact');
    $fcb_pic = postSpaceFilter('fcb_pic_hidden');
    $fcb_country = postSpaceFilter('fcb_country_hidden');
    $fcb_brand = postSpaceFilter('fcb_brand_hidden');
    $fcb_series = postSpaceFilter('fcb_series_hidden');
    $fcb_fbpage = postSpaceFilter('fcb_fbpage_hidden');
    $fcb_channel = postSpaceFilter('fcb_channel_hidden');
    $fcb_rec_name = postSpaceFilter('fcb_rec_name');
    $fcb_rec_ctc = postSpaceFilter('fcb_rec_ctc');
    $fcb_rec_add = postSpaceFilter('fcb_rec_add');
    $fcb_remark = postSpaceFilter('fcb_remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':
        case 'updRecord':

            if (!$fcb_name) {
                $name_err = "Name cannot be empty.";
                break;
            } else if (!$fcb_link) {
                $link_err = "Facebook Link cannot be empty.";
                break;
            } else if (!$fcb_ctc) {
                $contact_err = "Contact cannot be empty.";
                break;
            } else if (!$fcb_pic && $fcb_pic < 1) {
                $pic_err = "Sales Person-In-Charge cannot be empty.";
                break;
            } else if (!$fcb_country && $fcb_country < 1) {
                $country_err = "Country cannot be empty.";
                break;
            } else if (!$fcb_brand && $fcb_brand < 1) {
                $brand_err = "Brand cannot be empty.";
                break;
            } else if (!$fcb_series && $fcb_series < 1) {
                $series_err = "Series cannot be empty.";
                break;
            } else if (!$fcb_fbpage && $fcb_fbpage < 1) {
                $fbpage_err = "Facebook Page cannot be empty.";
                break;
            } else if (!$fcb_channel && $fcb_channel < 1) {
                $channel_err = "Channel cannot be empty.";
                break;
            } else if (!$fcb_rec_name) {
                $rec_name_err = "Receiver Name cannot be empty.";
                break;
            } else if (!$fcb_rec_ctc) {
                $rec_ctc_err = "Receiver Contact cannot be empty.";
                break;
            } else if (!$fcb_rec_add) {
                $rec_add_err = "Receiver Address cannot be empty.";
                break;
            } else if ($action == 'addRecord') {
                try {
                    //check values
                    if ($fcb_name) {
                        array_push($newvalarr, $fcb_name);
                        array_push($datafield, 'name');
                    }
                    if ($fcb_link) {
                        array_push($newvalarr, $fcb_link);
                        array_push($datafield, 'facebook link');
                    }

                    if ($fcb_ctc) {
                        array_push($newvalarr, $fcb_ctc);
                        array_push($datafield, 'contact');
                    }

                    if ($fcb_pic) {
                        array_push($newvalarr, $fcb_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($fcb_country) {
                        array_push($newvalarr, $fcb_country);
                        array_push($datafield, 'country');
                    }

                    if ($fcb_brand) {
                        array_push($newvalarr, $fcb_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($fcb_series) {
                        array_push($newvalarr, $fcb_series);
                        array_push($datafield, 'series');
                    }

                    if ($fcb_fbpage) {
                        array_push($newvalarr, $fcb_fbpage);
                        array_push($datafield, 'fb page');
                    }

                    if ($fcb_channel) {
                        array_push($newvalarr, $fcb_channel);
                        array_push($datafield, 'channel');
                    }

                    if ($fcb_rec_name) {
                        array_push($newvalarr, $fcb_rec_name);
                        array_push($datafield, 'receiver name');
                    }

                    if ($fcb_rec_ctc) {
                        array_push($newvalarr, $fcb_rec_ctc);
                        array_push($datafield, 'receiver contact');
                    }

                    if ($fcb_rec_add) {
                        array_push($newvalarr, $fcb_rec_add);
                        array_push($datafield, 'receiver address');
                    }

                    if ($fcb_remark) {
                        array_push($newvalarr, $fcb_remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(name,fb_link,contact,sales_pic,country,brand,series,fb_page,channel,ship_rec_name,ship_rec_add,ship_rec_contact,remark,create_by,create_date,create_time) VALUES ('$fcb_name','$fcb_link','$fcb_ctc','$fcb_pic','$fcb_country','$fcb_brand','$fcb_series','$fcb_fbpage','$fcb_channel','$fcb_rec_name','$fcb_rec_add','$fcb_rec_ctc','$fcb_remark','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['name'] != $fcb_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $fcb_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['fb_link'] != $fcb_link) {
                        array_push($oldvalarr, $row['fb_link']);
                        array_push($chgvalarr, $fb_link);
                        array_push($datafield, 'fb link');
                    }

                    if ($row['contact'] != $fcb_ctc) {
                        array_push($oldvalarr, $row['contact']);
                        array_push($chgvalarr, $fcb_ctc);
                        array_push($datafield, 'contact');
                    }

                    if ($row['sales_pic'] != $fcb_pic) {
                        array_push($oldvalarr, $row['sales_pic']);
                        array_push($chgvalarr, $fcb_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['country'] != $fcb_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $fcb_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['brand'] != $fcb_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $fcb_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['series'] != $fcb_series) {
                        array_push($oldvalarr, $row['series']);
                        array_push($chgvalarr, $fcb_series);
                        array_push($datafield, 'series');
                    }

                    if ($row['fb_page'] != $fcb_fbpage) {
                        array_push($oldvalarr, $row['fb_page']);
                        array_push($chgvalarr, $fcb_fbpage);
                        array_push($datafield, 'fb_page');
                    }

                    if ($row['channel'] != $fcb_channel) {
                        array_push($oldvalarr, $row['channel']);
                        array_push($chgvalarr, $fcb_channel);
                        array_push($datafield, 'channel');
                    }

                    if ($row['ship_rec_name'] != $fcb_rec_name) {
                        array_push($oldvalarr, $row['ship_rec_name']);
                        array_push($chgvalarr, $fcb_rec_name);
                        array_push($datafield, 'shipping receiver name');
                    }

                    if ($row['ship_rec_contact'] != $fcb_rec_ctc) {
                        array_push($oldvalarr, $row['ship_rec_contact']);
                        array_push($chgvalarr, $fcb_rec_ctc);
                        array_push($datafield, 'shipping receiver contact');
                    }

                    if ($row['ship_rec_add'] != $fcb_rec_add) {
                        array_push($oldvalarr, $row['ship_rec_add']);
                        array_push($chgvalarr, $fcb_rec_add);
                        array_push($datafield, 'shipping receiver address');
                    }

                    if ($row['remark'] != $fcb_remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $fcb_remark == '' ? 'Empty Value' : $fcb_remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName . " SET name = '$fcb_name', fb_link = '$fcb_link', contact = '$fcb_ctc', sales_pic = '$fcb_pic', country = '$fcb_country', brand = '$fcb_brand', series = '$fcb_series', fb_page = '$fcb_fbpage', channel = '$fcb_channel', ship_rec_name = '$fcb_rec_name', ship_rec_add = '$fcb_rec_add', ship_rec_contact = '$fcb_rec_ctc', remark ='$fcb_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="fcb_name_lbl" for="fcb_name">Name<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="fcb_name" id="fcb_name" value="<?php
                                if (isset($dataExisted) && isset($row['name']) && !isset($fcb_name)) {
                                    echo $row['name'];
                                } else if (isset($fcb_name)) {
                                    echo $fcb_name;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($name_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $name_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="fcb_link_lbl" for="fcb_link">Facebook Link<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="fcb_link" id="fcb_link" value="<?php
                                if (isset($dataExisted) && isset($row['fb_link']) && !isset($fcb_link)) {
                                    echo $row['fb_link'];
                                } else if (isset($fcb_link)) {
                                    echo $fcb_link;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($link_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $link_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="fcb_contact_lbl" for="fcb_contact">Contact<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="fcb_contact" id="fcb_contact" value="<?php
                                if (isset($dataExisted) && isset($row['contact']) && !isset($fcb_contact)) {
                                    echo $row['contact'];
                                } else if (isset($fcb_contact)) {
                                    echo $fcb_contact;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($contact_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $contact_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>


                        </div>

                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="fcb_pic_lbl" for="fcb_pic">Sales Person In
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
                                <input class="form-control" type="text" name="fcb_pic" id="fcb_pic" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : '' ?>">
                                <input type="hidden" name="fcb_pic_hidden" id="fcb_pic_hidden"
                                    value="<?php echo (isset($row['sales_pic'])) ? $row['sales_pic'] : ''; ?>">


                                <?php if (isset($pic_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $pic_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="fcb_country_lbl" for="fcb_country">Country<span
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
                                <input class="form-control" type="text" name="fcb_country" id="fcb_country" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $country_row['nicename'] : '' ?>">
                                <input type="hidden" name="fcb_country_hidden" id="fcb_country_hidden"
                                    value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">


                                <?php if (isset($country_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $country_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="fcb_brand_lbl" for="fcb_brand">Brand<span
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
                                <input class="form-control" type="text" name="fcb_brand" id="fcb_brand" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
                                <input type="hidden" name="fcb_brand_hidden" id="fcb_brand_hidden"
                                    value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">


                                <?php if (isset($brand_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $brand_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="fcb_fb_page_lbl" for="fcb_fbpage">Facebook
                                    Page<span class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['fb_page']))
                                    $echoVal = $row['fb_page'];

                                if (isset($echoVal)) {
                                    $fbpage_rst = getData('name', "id = '$echoVal'", '', FB_PAGE_ACC, $finance_connect);
                                    if (!$fbpage_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $fbpage_row = $fbpage_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="fcb_fbpage" id="fcb_fbpage" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $fbpage_row['name'] : '' ?>">
                                <input type="hidden" name="fcb_fbpage_hidden" id="fcb_fbpage_hidden"
                                    value="<?php echo (isset($row['fb_page'])) ? $row['fb_page'] : ''; ?>">


                                <?php if (isset($fbpage_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $fbpage_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="fcb_channel_lbl" for="fcb_channel">Channel<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['channel']))
                                    $echoVal = $row['channel'];

                                if (isset($echoVal)) {
                                    $channel_rst = getData('*', "id = '$echoVal'", '', CHANNEL, $connect);
                                } else {
                                    $channel_rst = getData('*', "name = 'Facebook'", '', CHANNEL, $connect);
                                }

                                if (!$channel_rst) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $channel_row = $channel_rst->fetch_assoc();
                                ?>
                                <input class="form-control" type="text" name="fcb_channel" id="fcb_channel" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo $channel_row['name'] ?>">
                                <input type="hidden" name="fcb_channel_hidden" id="fcb_channel_hidden"
                                    value="<?php echo (isset($row['channel'])) ? $row['channel'] : $channel_row['id']; ?>">


                                <?php if (isset($channel_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $channel_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="fcb_series_lbl" for="fcb_series">Series<span
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
                                <input class="form-control" type="text" name="fcb_series" id="fcb_series" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $series_row['name'] : '' ?>">
                                <input type="hidden" name="fcb_series_hidden" id="fcb_series_hidden"
                                    value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">


                                <?php if (isset($series_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $series_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                        <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Shipping Receiver Details</legend>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form_lbl" id="fcb_rec_name_lbl"
                                            for="fcb_rec_name">Receiver
                                            Name<span class="requireRed">*</span></label>
                                        <input class="form-control" type="text" name="fcb_rec_name" id="fcb_rec_name"
                                            value="<?php
                                            if (isset($dataExisted) && isset($row['ship_rec_name']) && !isset($fcb_rec_name)) {
                                                echo $row['ship_rec_name'];
                                            } else if (isset($fcb_rec_name)) {
                                                echo $fcb_rec_name;
                                            }
                                            ?>" <?php if ($act == '')
                                                echo 'disabled' ?>>
                                        <?php if (isset($rec_name_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1">
                                                    <?php echo $rec_name_err; ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form_lbl" id="fcb_rec_ctc_lbl"
                                            for="fcb_rec_ctc">Receiver
                                            Contact<span class="requireRed">*</span></label>
                                        <input class="form-control" type="number" name="fcb_rec_ctc" id="fcb_rec_ctc"
                                            value="<?php
                                            if (isset($dataExisted) && isset($row['ship_rec_contact']) && !isset($fcb_rec_ctc)) {
                                                echo $row['ship_rec_contact'];
                                            } else if (isset($fcb_rec_ctc)) {
                                                echo $fcb_rec_ctc;
                                            }
                                            ?>" <?php if ($act == '')
                                                echo 'disabled' ?>>
                                        <?php if (isset($rec_ctc_err)) { ?>
                                            <div id="err_msg">
                                                <span class="mt-n1">
                                                    <?php echo $rec_ctc_err; ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label form_lbl" id="fcb_rec_add_lbl"
                                            for="fcb_rec_add">Receiver
                                            Address<span class="requireRed">*</span></label>
                                        <input class="form-control" type="text" name="fcb_rec_add" id="fcb_rec_add"
                                            value="<?php
                                            if (isset($dataExisted) && isset($row['ship_rec_add']) && !isset($fcb_rec_add)) {
                                                echo $row['ship_rec_add'];
                                            } else if (isset($fcb_rec_add)) {
                                                echo $fcb_rec_add;
                                            }
                                            ?>" <?php if ($act == '')
                                                echo 'disabled' ?>>
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
                            <label class="form-label form_lbl" id="fcb_remark_lbl" for="fcb_remark">Remark</label>
                            <textarea class="form-control" name="fcb_remark" id="fcb_remark" rows="3" <?php if ($act == '')
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
        include "./js/fb_cust_deals.js"
            ?>
    </script>

</body>

</html>