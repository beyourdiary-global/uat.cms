<?php
$pageTitle = "Agent";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = AGENT;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/finance/agent_table.php';
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

if (post('actionBtn')) {
    $action = post('actionBtn');

    $name = postSpaceFilter("name");
    $brand = postSpaceFilter("brand");
    $pic = postSpaceFilter("pic_hidden");
    $contact = postSpaceFilter('contact');
    $email = postSpaceFilter('email');
    $country = postSpaceFilter('country');
    $remark = postSpaceFilter('remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addTransaction':
        case 'updTransaction':
        

             if (!$name) {
                $name_err = "Please specify the name.";
                break;
            } else if (!$brand && $brand < 1) {
                $brand_err = "Please specify the brand.";
                break;
            } else if (!$pic && $pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if (!$contact) {
                $ontact_err = "Please specify the contact.";
                break;
            } else if (!$email) {
                $email_err = "Please specify the email.";
                break;
            } else if (!$country && $country < 1) {
                $country_err = "Please specify the country.";
                break;
            } else if ($action == 'addTransaction') {
                try {
                    
                    //check values

                    if ($name) {
                        array_push($newvalarr, $name);
                        array_push($datafield, 'name');
                    }

                    if ($brand) {
                        array_push($newvalarr, $brand);
                        array_push($datafield, 'brand');
                    }

                    if ($pic) {
                        array_push($newvalarr, $pic);
                        array_push($datafield, 'pic');
                    }

                    if ($contact) {
                        array_push($newvalarr, $contact);
                        array_push($datafield, 'contact');
                    }

                    if ($email) {
                        array_push($newvalarr, $email);
                        array_push($datafield, 'email');
                    }

                    if ($country) {
                        array_push($newvalarr, $country);
                        array_push($datafield, 'country');
                    }

                    if ($remark) {
                        array_push($newvalarr, $remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(name,brand,pic,contact,email,country,remark,attachment,create_by,create_date,create_time) VALUES ('$name','$brand','$pic','$contact','$email','$country','$remark','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['name'] != $name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $name);
                        array_push($datafield, 'name');
                    }
                    if ($row['brand'] != $brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['pic'] != $pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $pic);
                        array_push($datafield, 'pic');
                    }

                    if ($row['contact'] != $contact) {
                        array_push($oldvalarr, $row['contact']);
                        array_push($chgvalarr, $contact);
                        array_push($datafield, 'contact');
                    }

                    if ($row['email'] != $email) {
                        array_push($oldvalarr, $row['email']);
                        array_push($chgvalarr, $email);
                        array_push($datafield, 'email');
                    }

                    if ($row['country'] != $country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $country);
                        array_push($datafield, 'country');
                    }

                    if ($row['remark'] != $remark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $remark == '' ? 'Empty Value' : $remark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                      
                        $query = "UPDATE " . $tblName  . " SET name = '$name', brand = '$brand', pic = '$pic', contact = '$cotact', email = '$email', country = '$country', remark ='$remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
            deleteRecord($tblName, $dataID, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
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
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
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
    <link rel="stylesheet" href="./css/package.css">
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

        <div id="formContainer" class="container-fluid mt-2">
            <div class="col-12 col-md-12 formWidthAdjust">
                <form id="form" method="post" novalidate>
                    <div class="form-group mb-5">
                        <h2>
                            <?php  echo displayPageAction($act, $pageTitle); ?>
                        </h2>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label form_lbl" for="name"><?php echo $pageTitle ?> Name<span class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="name" id="name"
                                    value="<?php if (isset($row['name'])) echo $row['name'] ?>"
                                    <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                                <div id="err_msg">
                                    <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label form_lbl" id="brand_lbl" for="brand">Brand<span class="requireRed">*</span></label>
                                <?php
                                unset($echoVal);
                                if (isset($row['brand'])) $echoVal = $row['brand'];
                                if (isset($echoVal)) {
                                    $brand_result = getData('name', "id = '$echoVal'", '', BRAND, $connect);
                                    $brand_row = $brand_result->fetch_assoc();
                                }
                                ?>
                                <input class="form-control" type="text" name="brand" id="brand"
                                    value="<?php echo !empty($echoVal) ? $brand_row['name'] : ''  ?>"
                                    <?php if ($act == '') echo 'readonly' ?> required>
                                <input type="hidden" name="brand_hidden" id="brand_hidden"
                                    value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($brand_err)) echo $brand_err; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3 autocomplete">
            <label class="form-label form_lbl" id="=pic_lbl" for="=pic">Person-In-Charge<span class="requireRed">*</span></label>
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
            <input class="form-control" type="text" name="=pic" id="=pic" <?php if ($act == '') echo 'readonly' ?> required>
            <input type="hidden" name="=pic_hidden" id="=pic_hidden" value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">

            <?php if (isset($pic_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $pic_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="contact" for="contact">Contact</label>
            <input class="form-control" type="number" name="contact" id="contact" value="<?php echo (isset($row['contact'])) ? $row['contact'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
            <div id="err_msg">
                <span class="mt-n1"><?php if (isset($contact_err)) echo $contact_err; ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="email" for="email">Email</label>
            <input class="form-control" type="text" name="email" id="email" value="<?php echo (isset($row['email'])) ? $row['email'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
            <div id="err_msg">
                <span class="mt-n1"><?php if (isset($email_err)) echo $email_err; ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group autocomplete mb-3">
            <label class="form-label form_lbl" id="country_lbl" for="country">Country<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['country']))
                $echoVal = $row['country'];

            if (isset($echoVal)) {
                $country_result = getData('name', "id = '$echoVal'", '', COUNTRIES, $connect);

                $country_row = $country_result->fetch_assoc();
            }
            ?>
            <input class="form-control" type="text" name="country" id="country" value="<?php echo !empty($echoVal) ? $country_row['name'] : ''  ?>" <?php if ($act == '') echo 'readonly' ?> required>
            <input type="hidden" name="country_hidden" id="country_hidden" value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">
            <div id="err_msg">
                <span class="mt-n1"><?php if (isset($country_err)) echo $country_err; ?></span>
            </div>
        </div>
    </div>
</div>    
                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" for="remark_form_lbl"><?php echo $pageTitle ?> Remark</label>
                        <textarea class="form-control" name="remark" id="remark" rows="3"
                            <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addPaymentTerms">Add Agent</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updPaymentTerms">Edit Agent</button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn"
                        value="back">Back</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ''; ?>";

    checkCurrentPage(page, action);
    setButtonColor();
    preloader(300, action);
    </script>

</body>

</html>