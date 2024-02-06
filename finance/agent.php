<?php
$pageTitle = "Agent";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = AGENT;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';


$redirect_page = $SITEURL . '/finance/agent_table.php';
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



if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

    $name = postSpaceFilter("name");
    $agt_brand = postSpaceFilter("agt_brand_hidden");
    $agt_pic = postSpaceFilter("agt_pic_hidden");
    $contact = postSpaceFilter('contact');
    $email = postSpaceFilter('email');
    $agt_country = postSpaceFilter('agt_country_hidden');
    $remark = postSpaceFilter('remark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    if ($email && !isEmail($email)) {
        $email_err = "Wrong email format!";
        $error = 1;
        break;
    }

    if (isDuplicateRecord("name", $name, $tblName,  $finance_connect, $dataID)) {
        $name_err = "Duplicate record found for " . $pageTitle . " name.";
        break;
    }
             if (!$name) {
                $name_err = "Please specify the name.";
                break;
            } else if (!$agt_brand && $agt_brand < 1) {
                $brand_err = "Please specify the brand.";
                break;
            } else if (!$agt_pic && $agt_pic < 1) {
                $pic_err = "Please specify the person-in-charge.";
                break;
            } else if (!$agt_country) {
                $country_err = "Please specify the country.";
                break;
            } else if ($action == 'addData') {
                try {
                 
                    //check values

                    if ($name) {
                        array_push($newvalarr, $name);
                        array_push($datafield, 'name');
                    }

                    if ($agt_brand) {
                        array_push($newvalarr, $agt_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($agt_pic) {
                        array_push($newvalarr, $agt_pic);
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

                    if ($agt_country) {
                        array_push($newvalarr, $agt_country);
                        array_push($datafield, 'country');
                    }

                    if ($remark) {
                        array_push($newvalarr, $remark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName  . "(name,brand,pic,contact,email,country,remark,create_by,create_date,create_time) VALUES ('$name','$agt_brand','$agt_pic','$contact','$email','$agt_country','$remark','" . USER_ID . "',curdate(),curtime())";
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
                    if ($row['name'] != $name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $name);
                        array_push($datafield, 'name');
                    }
                    if ($row['brand'] != $agt_brand) {
                        array_push($oldvalarr, $row['brand']);
                        array_push($chgvalarr, $agt_brand);
                        array_push($datafield, 'brand');
                    }

                    if ($row['pic'] != $agt_pic) {
                        array_push($oldvalarr, $row['pic']);
                        array_push($chgvalarr, $agt_pic);
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

                    if ($row['country'] != $agt_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $agt_country);
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
                        $query = "UPDATE " . $tblName  . " SET name = '$name', brand = '$agt_brand', pic = '$agt_pic', contact = '$contact', email = '$email', country = '$agt_country', remark ='$remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                if ($action == 'addData' || $action == 'updData') {
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
            deleteRecord($tblName, $dataID, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }


//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $name . "</b> from <b><i>$tblName Table</i></b>.";
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

        <div id="AGTformContainer" class="container-fluid mt-2">
            <div class="col-12 col-md-12 formWidthAdjust">
                <form id="AGTform" method="post" novalidate>
                    <div class="form-group mb-5">
                        <h2>
                            <?php  echo displayPageAction($act, $pageTitle); ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                     <div class="form-group mb-3">
    <div class="row">
        <div class="form-group mb-3 col-md-6">
            <label class="form-label form_lbl" id="name_lbl" for="name">Name*</span></label>
            <input class="form-control" type="text" name="name" id="name" value="<?php 
                    if (isset($dataExisted) && isset($row['name']) && !isset($name)) {
                        echo $row['name'];
                    } else if (isset($dataExisted) && isset($row['name']) && isset($name)) {
                        echo $name;
                    } else {
                        echo '';
                    } ?>" <?php if ($act == '') echo 'disabled' ?>>

            <?php if (isset($name_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $name_err; ?></span>
                </div>
            <?php } ?>
        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
            <label class="form-label form_lbl" id="agt_brand_lbl" for="agt_brand">Brand*</span></label>
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

            <input class="form-control" type="text" name="agt_brand" id="agt_brand" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $brand_row['name'] : ''  ?>">

            <input type="hidden" name="agt_brand_hidden" id="agt_brand_hidden" value="<?php echo (isset($row['brand'])) ? $row['brand'] : ''; ?>">

            <?php if (isset($brand_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $brand_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    </div>

                    <div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3 autocomplete">
            <label class="form-label form_lbl" id="agt_pic_lbl" for="agt_pic">Person-In-Charge*</span></label>
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
                $pic_row = $user_row;
            }
            ?>
            <input class="form-control" type="text" name="agt_pic" id="agt_pic" <?php if ($act == '') echo 'readonly' ?>value="<?php echo !empty($echoVal) ? $pic_row['pic'] : ''  ?>">
            <input type="hidden" name="agt_pic_hidden" id="agt_pic_hidden" value="<?php echo (isset($row['pic'])) ? $row['pic'] : ''; ?>">

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
            <input class="form-control" type="number" name="contact" id="contact" value="<?php echo (isset($row['contact'])) ? $row['contact'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?>>
            <div id="err_msg">
                <span class="mt-n1"><?php if (isset($contact_err)) echo $contact_err; ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group mb-3">
            <label class="form-label form_lbl" id="email_lbl" for="email">Email</label>
            <input class="form-control" type="text" name="email" id="email" value="<?php
                if (isset($dataExisted) && isset($row['email']) && !isset($email)) {
                    echo $row['email'];
                } else if (isset($dataExisted) && isset($row['email']) && isset($mrcht_email)) {
                    echo $email;
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
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group autocomplete">
            <label class="form-label form_lbl" id="agt_country_lbl" for="agt_country">Country*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['country']))
                $echoVal = $row['country'];

            if (isset($echoVal)) {
                $country_rst = getData('name', "id = '$echoVal'", '', COUNTRIES, $connect);
                if (!$country_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $country_row = $country_rst->fetch_assoc();
                echo $country_row['name'];
            }
            ?>

            <input class="form-control" type="text" name="agt_country" id="agt_country" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $country_row['name'] : ''  ?>">

            <input type="hidden" name="agt_country_hidden" id="agt_country_hidden" value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">

            <?php if (isset($country_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $country_err; ?></span>
                </div>
            <?php } ?>
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
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addData">Add Agent</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updData">Edit Agent</button>';
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
        <?php include "../js/agent.js" ?>

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