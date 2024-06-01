<?php
$pageTitle = "Facebook Order Request";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = FB_ORDER_REQ;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/finance/fb_order_req_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = '../' . img_server . 'finance/fb_order_req/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

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

    $for_name = postSpaceFilter('for_name');
    $for_link = postSpaceFilter('for_link');
    $for_ctc = postSpaceFilter('for_contact');
    $for_pic = postSpaceFilter('for_pic_hidden');
    $for_country = postSpaceFilter('for_country_hidden');
    $for_brand = postSpaceFilter('for_brand_hidden');
    $for_series = postSpaceFilter('for_series_hidden');
    $for_pkg = postSpaceFilter('for_pkg_hidden');
    $for_fbpage = postSpaceFilter('for_fbpage_hidden');
    $for_channel = postSpaceFilter('for_channel_hidden');
    $for_price = postSpaceFilter('for_price');
    $for_pay = postSpaceFilter('for_pay_meth_hidden');
    $for_rec_name = postSpaceFilter('for_rec_name');
    $for_rec_ctc = postSpaceFilter('for_rec_ctc');
    $for_rec_add = postSpaceFilter('for_rec_add');
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
                    $files = glob($img_path . $for_link . '_*.' . $img_ext);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (preg_match('/' . preg_quote($for_link, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int) $matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $for_link . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($for_file_tmp_name, $img_path . $new_file_name)) {
                        $for_attach = $new_file_name; // Update $for_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else
                    $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
            }

            if (!$for_name) {
                $name_err = "Name cannot be empty.";
                break;
            } else if (!$for_link) {
                $link_err = "Facebook Link cannot be empty.";
                break;
            } else if (!$for_ctc) {
                $contact_err = "Contact cannot be empty.";
                break;
            } else if (!$for_pic && $for_pic < 1) {
                $pic_err = "Sales Person-In-Charge cannot be empty.";
                break;
            } else if (!$for_country && $for_country < 1) {
                $country_err = "Country cannot be empty.";
                break;
            } else if (!$for_brand && $for_brand < 1) {
                $brand_err = "Brand cannot be empty.";
                break;
            } else if (!$for_series && $for_series < 1) {
                $series_err = "Series cannot be empty.";
                break;
            } else if (!$for_pkg && $for_pkg < 1) {
                $pkg_err = "Package cannot be empty.";
                break;
            } else if (!$for_fbpage && $for_fbpage < 1) {
                $fbpage_err = "Facebook Page cannot be empty.";
                break;
            } else if (!$for_channel && $for_channel < 1) {
                $channel_err = "Channel cannot be empty.";
                break;
            } else if (!$for_price) {
                $price_err = "Price cannot be empty.";
                break;
            } else if (!$for_pay && $for_pay < 1) {
                $pay_err = "Payment Method cannot be empty.";
                break;
            } else if (!$for_rec_name) {
                $rec_name_err = "Receiver Name cannot be empty.";
                break;
            } else if (!$for_rec_ctc) {
                $rec_ctc_err = "Receiver Contact cannot be empty.";
                break;
            } else if (!$for_rec_add) {
                $rec_add_err = "Receiver Address cannot be empty.";
                break;
            } else if (!$for_attach) {
                $desc_err = "Attachment cannot be empty.";
                break;
            } else if ($action == 'addRecord') {
                try {
                    //check values
                    if ($for_name) {
                        array_push($newvalarr, $for_name);
                        array_push($datafield, 'name');
                    }
                    if ($for_link) {
                        array_push($newvalarr, $for_link);
                        array_push($datafield, 'facebook link');
                    }

                    if ($for_ctc) {
                        array_push($newvalarr, $for_ctc);
                        array_push($datafield, 'contact');
                    }

                    if ($for_pic) {
                        array_push($newvalarr, $for_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($for_country) {
                        array_push($newvalarr, $for_country);
                        array_push($datafield, 'country');
                    }

                    if ($for_brand) {
                        array_push($newvalarr, $for_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($for_series) {
                        array_push($newvalarr, $for_series);
                        array_push($datafield, 'series');
                    }

                    if ($for_pkg) {
                        array_push($newvalarr, $for_pkg);
                        array_push($datafield, 'package');
                    }

                    if ($for_fbpage) {
                        array_push($newvalarr, $for_fbpage);
                        array_push($datafield, 'fb page');
                    }

                    if ($for_channel) {
                        array_push($newvalarr, $for_channel);
                        array_push($datafield, 'channel');
                    }

                    if ($for_price) {
                        array_push($newvalarr, $for_price);
                        array_push($datafield, 'price');
                    }

                    if ($for_pay) {
                        array_push($newvalarr, $for_pay);
                        array_push($datafield, 'payment method');
                    }

                    if ($for_rec_name) {
                        array_push($newvalarr, $for_rec_name);
                        array_push($datafield, 'receiver name');
                    }

                    if ($for_rec_ctc) {
                        array_push($newvalarr, $for_rec_ctc);
                        array_push($datafield, 'receiver contact');
                    }

                    if ($for_rec_add) {
                        array_push($newvalarr, $for_rec_add);
                        array_push($datafield, 'receiver address');
                    }

                    if ($for_attach) {
                        array_push($newvalarr, $for_attach);
                        array_push($datafield, 'attachment');
                    }

                    if ($for_remark) {
                        array_push($newvalarr, $for_remark);
                        array_push($datafield, 'remark');
                    }

                    $tblName2 = FB_CUST_DEALS;
                    $query = "INSERT INTO " . $tblName . " (name,fb_link,contact,sales_pic,country,brand,series,package,fb_page,channel,price,pay_method,ship_rec_name,ship_rec_add,ship_rec_contact,remark,attachment,create_by,create_date,create_time) VALUES ('$for_name','$for_link','$for_ctc','$for_pic','$for_country','$for_brand','$for_series','$for_pkg','$for_fbpage','$for_channel','$for_price','$for_pay','$for_rec_name','$for_rec_add','$for_rec_ctc','$for_remark','$for_attach','" . USER_ID . "',curdate(),curtime())";
                   
                    $result2 = getData('*', "name = '$for_name' AND fb_link = '$for_link'", '', $tblName2, $connect);
                    
                    if($result2->num_rows == 0){
                        $query2 = "INSERT INTO " . $tblName2 . " (name, fb_link, contact, sales_pic, country, brand, fb_page, channel, series,ship_rec_name, ship_rec_add, ship_rec_contact, remark, create_by, create_date,create_time)  VALUES ('$for_name','$for_link','$for_ctc','$for_pic','$for_country','$for_brand','$for_fbpage','$for_channel','$for_series','$for_rec_name','$for_rec_add','$for_rec_ctc','$for_remark','" . USER_ID . "',curdate(),curtime())";
                        $returnData2 = mysqli_query($connect, $query2);
                    }
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
                    if ($row['name'] != $for_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $for_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['fb_link'] != $for_link) {
                        array_push($oldvalarr, $row['fb_link']);
                        array_push($chgvalarr, $for_link);
                        array_push($datafield, 'fb link');
                    }

                    if ($row['contact'] != $for_ctc) {
                        array_push($oldvalarr, $row['contact']);
                        array_push($chgvalarr, $for_ctc);
                        array_push($datafield, 'contact');
                    }

                    if ($row['sales_pic'] != $for_pic) {
                        array_push($oldvalarr, $row['sales_pic']);
                        array_push($chgvalarr, $for_pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['country'] != $for_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $for_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['brand'] != $for_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $for_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['series'] != $for_series) {
                        array_push($oldvalarr, $row['series']);
                        array_push($chgvalarr, $for_series);
                        array_push($datafield, 'series');
                    }

                    if ($row['package'] != $for_pkg) {
                        array_push($oldvalarr, $row['package']);
                        array_push($chgvalarr, $for_pkg);
                        array_push($datafield, 'package');
                    }

                    if ($row['fb_page'] != $for_fbpage) {
                        array_push($oldvalarr, $row['fb_page']);
                        array_push($chgvalarr, $for_fbpage);
                        array_push($datafield, 'fb_page');
                    }

                    if ($row['channel'] != $for_channel) {
                        array_push($oldvalarr, $row['channel']);
                        array_push($chgvalarr, $for_channel);
                        array_push($datafield, 'channel');
                    }

                    if ($row['price'] != $for_price) {
                        array_push($oldvalarr, $row['price']);
                        array_push($chgvalarr, $for_price);
                        array_push($datafield, 'price');
                    }

                    if ($row['pay_method'] != $for_pay) {
                        array_push($oldvalarr, $row['pay_method']);
                        array_push($chgvalarr, $for_pay);
                        array_push($datafield, 'payment method');
                    }

                    if ($row['ship_rec_name'] != $for_rec_name) {
                        array_push($oldvalarr, $row['ship_rec_name']);
                        array_push($chgvalarr, $for_rec_name);
                        array_push($datafield, 'shipping receiver name');
                    }

                    if ($row['ship_rec_contact'] != $for_rec_ctc) {
                        array_push($oldvalarr, $row['ship_rec_contact']);
                        array_push($chgvalarr, $for_rec_ctc);
                        array_push($datafield, 'shipping receiver contact');
                    }

                    if ($row['ship_rec_add'] != $for_rec_add) {
                        array_push($oldvalarr, $row['ship_rec_add']);
                        array_push($chgvalarr, $for_rec_add);
                        array_push($datafield, 'shipping receiver address');
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
                        $query = "UPDATE " . $tblName . " SET name = '$for_name', fb_link = '$for_link', contact = '$for_ctc', sales_pic = '$for_pic', country = '$for_country', brand = '$for_brand', series = '$for_series', package = '$for_pkg', fb_page = '$for_fbpage', channel = '$for_channel', price = '$for_price', pay_method = '$for_pay', ship_rec_name = '$for_rec_name', ship_rec_add = '$for_rec_add', ship_rec_contact = '$for_rec_ctc', remark ='$for_remark', attachment ='$for_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            deleteRecord($tblName, '', $dataID, $for_name, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
    <!-- <div class="pre-load-center">
        <div class="preloader"></div>
    </div> -->
    <!-- <div class="page-load-cover"> -->
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
                            <label class="form-label form_lbl" id="for_name_lbl" for="for_name">Name<span
                                    class="requireRed">*</span></label>
                            <?php 
                             unset($echoVal);

                             if (isset($row['name']))
                                 $echoVal = $row['name'];
                            ?>
                            <input class="form-control" type="text" name="for_name" id="for_name" value="<?php echo !empty($echoVal) ? $row['name'] : '' ?>" <?php if ($act == '')
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
                            <label class="form-label form_lbl" id="for_link_lbl" for="for_link">Facebook Link<span
                                    class="requireRed">*</span></label>
                                    <?php 
                             unset($echoVal);

                             if (isset($row['fb_link']))
                                 $echoVal = $row['fb_link'];
                            ?>
                            <input class="form-control" type="text" name="for_link" id="for_link" value="<?php echo !empty($echoVal) ? $row['fb_link'] : '' ?>" <?php if ($act == '')
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
                            <label class="form-label form_lbl" id="for_contact_lbl" for="for_contact">Contact<span
                                    class="requireRed">*</span></label>
                            <input class="form-control" type="number" step="0.01" name="for_contact" id="for_contact" value="<?php
                            if (isset($dataExisted) && isset($row['contact']) && !isset($for_contact)) {
                                echo $row['contact'];
                            } else if (isset($for_contact)) {
                                echo $for_contact;
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
                <fieldset class="border p-2 mb-3" style="border-radius: 3px;">
                    <legend class="float-none w-auto p-2">Order Request Details</legend>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="for_pic_lbl" for="for_pic">Sales Person In
                                    Charge<span class="requireRed">*</span></label>
                                <?php
                                   if(($act == 'E' || $act == '')){
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
                                <input class="form-control" type="text" name="for_pic" id="for_pic" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $user_row['name'] : '' ?>">
                                <input type="hidden" name="for_pic_hidden" id="for_pic_hidden"
                                    value="<?php echo (isset($row['sales_pic'])) ? $row['sales_pic'] : ''; ?>">
                                <?php } ?>
                                <?php
                                if(($act == 'I')){
                           
                                    $loggedInUserId = USER_ID; // Assuming USER_ID contains the ID of the logged-in user
                                    $defaultUser = '';
                                
                                    // Retrieve details of the logged-in user
                                    $user_rst = getData('name', "id = '$loggedInUserId'", '', USR_USER, $connect);
                                    if ($user_rst && $user_rst->num_rows > 0) {
                                        $user_row = $user_rst->fetch_assoc();
                                        $defaultUser = $user_row['name'];
                                    }
                                    
                                ?>
                                <input class="form-control" type="text" name="for_pic" id="for_pic" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo $defaultUser ?>">
                                <input type="hidden" name="for_pic_hidden" id="for_pic_hidden"
                                    value="<?php echo $loggedInUserId ?>">
                                <?php }?>
                                <?php if (isset($pic_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $pic_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="for_country_lbl" for="for_country">Country<span
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
                                <input class="form-control" type="text" name="for_country" id="for_country" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $country_row['nicename'] : '' ?>">
                                <input type="hidden" name="for_country_hidden" id="for_country_hidden"
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
                                <input class="form-control" type="text" name="for_brand" id="for_brand" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
                                <input type="hidden" name="for_brand_hidden" id="for_brand_hidden"
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
                                <input class="form-control" type="text" name="for_series" id="for_series" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $series_row['name'] : '' ?>">
                                <input type="hidden" name="for_series_hidden" id="for_series_hidden"
                                    value="<?php echo (isset($row['series'])) ? $row['series'] : ''; ?>">


                                <?php if (isset($series_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $series_err; ?>
                                        </span>
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
                                <input class="form-control" type="text" name="for_pkg" id="for_pkg" <?php if ($act == '')
                                    echo 'disabled' ?> value="<?php echo !empty($echoVal) ? $pkg_row['name'] : '' ?>">
                                <input type="hidden" name="for_pkg_hidden" id="for_pkg_hidden"
                                    value="<?php echo (isset($row['package'])) ? $row['package'] : ''; ?>">


                                <?php if (isset($pkg_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $pkg_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="for_fb_page_lbl" for="for_fbpage">Facebook
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
                                <input class="form-control" type="text" name="for_fbpage" id="for_fbpage" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $brand_row['name'] : '' ?>">
                                <input type="hidden" name="for_fbpage_hidden" id="for_fbpage_hidden"
                                    value="<?php echo (isset($row['fb_page'])) ? $row['fb_page'] : ''; ?>">


                                <?php if (isset($fbpage_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $fbpage_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4 mb-3 autocomplete">
                                <label class="form-label form_lbl" id="for_channel_lbl" for="for_channel">Channel<span
                                        class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);

                                if (isset($row['channel']))
                                    $echoVal = $row['channel'];

                                if (isset($echoVal)) {
                                    $channel_rst = getData('*', "id = '$echoVal'", '', CHANEL_SC_MD, $finance_connect);
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="for_price_lbl" for="for_price">Price<span class="requireRed">*</span></label>
                                <?php 
                                unset($echoVal);

                                if (isset($row['price']))
                                    $echoVal = $row['price'];
                                ?>
                                <input class="form-control" type="text" name="for_price" id="for_price" value="<?php echo !empty($echoVal) ? $row['price'] : '' ?>" <?php if ($act == '') echo 'disabled' ?>>
                                <?php if (isset($price_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php echo $price_err; ?></span>
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
                                    $pay_rst = getData('name', "id = '$echoVal'", '', FIN_PAY_METH, $finance_connect);
                                    if (!$pay_rst) {
                                        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                        echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                    }
                                    $pay_row = $pay_rst->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="for_pay_meth" id="for_pay_meth" <?php if ($act == '')
                                    echo 'disabled' ?>
                                        value="<?php echo !empty($echoVal) ? $pay_row['name'] : '' ?>">
                                <input type="hidden" name="for_pay_meth_hidden" id="for_pay_meth_hidden"
                                    value="<?php echo (isset($row['pay_method'])) ? $row['pay_method'] : ''; ?>">


                                <?php if (isset($pay_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $pay_err; ?>
                                        </span>
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
                                    <?php 
                                unset($echoVal);

                                if (isset($row['ship_rec_name']))
                                    $echoVal = $row['ship_rec_name'];
                                ?>
                                <input class="form-control" type="text" name="for_rec_name" id="for_rec_name" value="<?php echo !empty($echoVal) ? $row['ship_rec_name'] : '' ?>" <?php if ($act == '')
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
                                <label class="form-label form_lbl" id="for_rec_ctc_lbl" for="for_rec_ctc">Receiver
                                    Contact<span class="requireRed">*</span></label>
                                    <?php 
                                unset($echoVal);

                                if (isset($row['ship_rec_contact']))
                                    $echoVal = $row['ship_rec_contact'];
                                ?>
                                <input class="form-control" type="number" name="for_rec_ctc" id="for_rec_ctc" value="<?php echo !empty($echoVal) ? $row['ship_rec_contact'] : '' ?>" <?php if ($act == '')
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
                                <label class="form-label form_lbl" id="for_rec_add_lbl" for="for_rec_add">Receiver
                                    Address<span class="requireRed">*</span></label>
                                    <?php 
                                unset($echoVal);

                                if (isset($row['ship_rec_add']))
                                    $echoVal = $row['ship_rec_add'];
                                ?>
                                <input class="form-control" type="text" name="for_rec_add" id="for_rec_add" value="<?php echo !empty($echoVal) ? $row['ship_rec_add'] : '' ?>" <?php if ($act == '')
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
                    <label class="form-label form_lbl" id="for_remark_lbl" for="for_remark">Remark</label>
                    <textarea class="form-control" name="for_remark" id="for_remark" rows="3" <?php if ($act == '')
                        echo 'disabled' ?>><?php if (isset($dataExisted) && isset($row['remark']))
                        echo $row['remark'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="for_attach_lbl" for="for_attach">Attachment*</label>
                                <input class="form-control" type="file" name="for_attach" id="for_attach" <?php if ($act == '')
                        echo 'disabled' ?>>

                            <?php if (isset($row['attachment']) && $row['attachment']) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?>
                                    </span>
                                </div>
                                <input type="hidden" name="existing_attachment"
                                    value="<?php echo htmlspecialchars($row['attachment']); ?>">
                            <?php } ?>

                            <?php if (isset($attach_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $attach_err; ?>
                                    </span>
                                </div>
                            <?php } ?>

                        </div>
                        <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-center justify-content-md-end px-4">
                                <?php
                                    
                                unset($echoVal);
                                $attachmentSrc = '';
                                if (isset($row['attachment']))
                                    $echoVal = $row['attachment'];
                                    if(isset($echoVal)){
                                        
                                    if (isset($for_attach)) {
                                        $attachmentSrc = $img_path . $for_attach;
                                    }else{
                                        $attachmentSrc = $img_path . $echoVal;
                                    }
                                    }else{
                                        $attachmentSrc = '';
                                    }
                               
                                ?>
                                <img id="for_attach_preview" name="for_attach_preview"
                                    src="<?php echo !empty($echoVal) ? $attachmentSrc : '' ?>" class="img-thumbnail" alt="Attachment Preview">
                                <input type="hidden" name="for_attachmentValue" id="for_attachmentValue" value="<?php if (isset($row['attachment']))
                                    echo $row['attachment']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if(isset($row['order_status'])){
                if($row['order_status'] == 'SP'){
                ?>
                <div class="form-group mb-4">
                    <h3>
                        Tracking Details
                    </h3>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_courier_lbl" for="sor_courier">Courier</label>
                            <?php
                           
                            if (isset($row['id']))
                            $echoVal = $row['id'];
                            $courier_rst2 = getData('courier_id', "order_id = '$echoVal'", '', OFFICIAL_PROCESS_ORDER, $connect);

                            if (!$courier_rst2) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $courier_row2 = $courier_rst2->fetch_assoc();
                            if ($courier_row2['courier_id'])
                            $echoVal2 = $courier_row2['courier_id'];
                       
                            $courier_rst = getData('name', "id = '$echoVal2'", '', COURIER, $connect);
                            $courier_row = $courier_rst->fetch_assoc();
                      
                            if (isset($courier_row['name'])) {
                                $courier_name = $courier_row['name'];
                            } else {
                                $courier_name = '';
                            }
                            ?>
                            <input class="form-control" type="text" name="sor_courier" id="sor_courier" value="<?php echo !empty($echoVal2) ? $courier_name : ''; ?>" disabled ?>

                            <?php if (isset($courier_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $courier_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label form_lbl" id="sor_track_lbl" for="sor_track">Tracking Number</label>
                            
                            <?php
                             $tracking_rst = getData('tracking_id', "order_id = '$echoVal'", '', OFFICIAL_PROCESS_ORDER, $connect);
                             if (!$tracking_rst) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $tracking_row = $tracking_rst->fetch_assoc();
                            if (isset($tracking_row['tracking_id'])) {
                                $tracking_id = $tracking_row['tracking_id'];
                            } else {
                                $tracking_id = '';
                            }
                             ?>
                             <input class="form-control" type="text"  name="sor_track" id="sor_track" value="<?php echo !empty($echoVal) ? $tracking_id : ''; ?>" disabled ?>
                            <?php if (isset($tracking_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1">
                                        <?php echo $tracking_err; ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-4 d-flex align-items-end">
                            <label>&nbsp;</label><br>
                            <?php
                   
                            $tracking_rst2 = getData('tracking_link', "id = '$echoVal2'", '', COURIER, $connect);
                            if (!$tracking_rst2) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                            }
                            $track_row = $tracking_rst2->fetch_assoc();
                      
                            if (isset($track_row['tracking_link'])) {
                                $tracking_link = $track_row['tracking_link'];
                                
                            } else {
                                $tracking_link = '';
                            }
                            ?>
                            
                            <a href="<?php echo $tracking_link; ?>" id="trackOrderBtn" class="track-order-btn" data-tracking-id="<?php echo $tracking_id; ?>" target="_blank">Track Order</a>
                            
                    </div>
                </div>
                <?php } }?>
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
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn"
                        value="back">Back</button>
                </div>
            </form>
        </div>
    </div>
    <!-- </div> -->

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
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);

        <?php
        include "../js/fb_order_req.js"
            ?>
    </script>

</body>

</html>