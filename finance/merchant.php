<?php
$pageTitle = "Merchant";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = MERCHANT;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/merchant_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';
$errorMsgAlert = "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName,  $finance_connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $row['name'],  $finance_connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data ";
    } else {
        $viewActMsg = USER_NAME . " viewed the data <b>" . $row['name'] . "</b> from <b><i>$tblName Table</i></b>.";
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

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $currentDataName = postSpaceFilter('currentDataName');
            $mrcht_business_no = postSpaceFilter("mrcht_business_no");
            $mrcht_email = postSpaceFilter("mrcht_email");
            $mrcht_contact = postSpaceFilter('mrcht_contact');
            $mrcht_address = postSpaceFilter('mrcht_address');
            $mrcht_pic = postSpaceFilter('mrcht_pic');
            $mrcht_pic_contact = postSpaceFilter('mrcht_pic_contact');
            $dataRemark = postSpaceFilter('currentDataRemark');

            $oldvalarr = $chgvalarr = $newvalarr = array();

            if ($mrcht_email && !isEmail($mrcht_email)) {
                $email_err = "Wrong email format!";
                $error = 1;
            }

            if (isDuplicateRecord("name", $currentDataName, $tblName,  $finance_connect, $dataID)) {
                $err1 = "Duplicate record found for " . $pageTitle . " name.";
                $error = 1;
            }

            if (isset($error)) {
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($currentDataName)
                        array_push($newvalarr, $currentDataName);

                    if ($mrcht_business_no)
                        array_push($newvalarr, $mrcht_business_no);

                    if ($mrcht_email)
                        array_push($newvalarr, $mrcht_email);

                    if ($mrcht_contact)
                        array_push($newvalarr, $mrcht_contact);

                    if ($mrcht_address)
                        array_push($newvalarr, $mrcht_address);

                    if ($mrcht_pic)
                        array_push($newvalarr, $mrcht_pic);

                    if ($mrcht_pic_contact)
                        array_push($newvalarr, $mrcht_pic_contact);

                    if ($dataRemark)
                        array_push($newvalarr, $dataRemark);

                    $query = "INSERT INTO " . $tblName . "(name,business_no,contact,email,address,person_in_charges,person_in_charges_contact,remark,create_by,create_date,create_time) VALUES ('$currentDataName','$mrcht_business_no','$mrcht_contact','$mrcht_email','$mrcht_address','$mrcht_pic','$mrcht_pic_contact','$dataRemark','" . USER_ID . "',curdate(),curtime())";

                    $returnData = mysqli_query($finance_connect, $query);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            } else {
                try {
                    if ($row['name'] != $currentDataName) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $currentDataName);
                    }

                    if ($row['business_no'] != $mrcht_business_no) {
                        array_push($oldvalarr, $row['business_no']);
                        array_push($chgvalarr, $mrcht_business_no);
                    }

                    if ($row['contact'] != $mrcht_contact) {
                        array_push($oldvalarr, $row['contact']);
                        array_push($chgvalarr, $mrcht_contact);
                    }

                    if ($row['email'] != $mrcht_email) {
                        array_push($oldvalarr, $row['email']);
                        array_push($chgvalarr, $mrcht_email);
                    }

                    if ($row['address'] != $mrcht_address) {
                        array_push($oldvalarr, $row['address']);
                        array_push($chgvalarr, $mrcht_address);
                    }

                    if ($row['person_in_charges'] != $mrcht_pic) {
                        array_push($oldvalarr, $row['person_in_charges']);
                        array_push($chgvalarr, $mrcht_pic);
                    }

                    if ($row['person_in_charges_contact'] != $mrcht_pic_contact) {
                        array_push($oldvalarr, $row['person_in_charges_contact']);
                        array_push($chgvalarr, $mrcht_pic_contact);
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET name ='$currentDataName',business_no = '$mrcht_business_no',email = '$mrcht_email', contact = '$mrcht_contact', address ='$mrcht_address', person_in_charges ='$mrcht_pic', person_in_charges_contact ='$mrcht_pic_contact', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                }
            }

            if (isset($errorMsg)) {
                $act = "F";
                $errorMsg = str_replace('\'', '', $errorMsg);
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

                    if (isset($returnData)) {
                        $log['act_msg'] = USER_NAME . " added <b>$currentDataName</b> into <b><i>$tblName Table</i></b>.";
                    } else {
                        $log['act_msg'] = USER_NAME . " fail to insert <b>$currentDataName</b> into <b><i>$tblName Table</i></b> ( $errorMsg )";
                    }
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($oldvalarr, $chgvalarr, $tblName, (isset($returnData) ? '' : $errorMsg));
                }

                audit_log($log);
            }

            break;

        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
    }
}

//Function(title, subtitle, page name, ajax url path, redirect path, action)
//To show action dialog after finish certain action (eg. edit)

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<body>

    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php echo $pageActionTitle ?>
        </p>
    </div>

    <div id="formContainer" class="container d-flex justify-content-center">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="form" method="post" novalidate>
                <div class="form-group mb-5">
                    <h2>
                        <?php echo $pageActionTitle ?>
                    </h2>
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">

                            <label class="form-label" for="currentDataName"><?php echo $pageTitle ?> Name</label>
                            <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                            <?php if (isset($name_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $name_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_business_no_lbl" for="mrcht_business_no"><?php echo $pageTitle ?> Business No</label>
                            <input class="form-control" type="text" name="mrcht_business_no" id="mrcht_business_no" value="<?php
                                                                                                                            if (isset($dataExisted) && isset($row['business_no']) && !isset($mrcht_business_no)) {
                                                                                                                                echo $row['business_no'];
                                                                                                                            } else if (isset($dataExisted) && isset($row['business_no']) && isset($mrcht_business_no)) {
                                                                                                                                echo $mrcht_business_no;
                                                                                                                            } else {
                                                                                                                                echo '';
                                                                                                                            } ?>" <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">

                            <label class="form-label form_lbl" id="mrcht_contact_lbl" for="mrcht_contact"><?php echo $pageTitle ?> Contact</label>
                            <input class="form-control" type="number" step="any" name="mrcht_contact" id="mrcht_contact" value="<?php
                                                                                                                                if (isset($dataExisted) && isset($row['contact']) && !isset($mrcht_contact)) {
                                                                                                                                    echo $row['contact'];
                                                                                                                                } else if (isset($dataExisted) && isset($row['contact']) && isset($mrcht_contact)) {
                                                                                                                                    echo $mrcht_contact;
                                                                                                                                } else {
                                                                                                                                    echo '';
                                                                                                                                } ?>" <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_email_lbl" for="mrcht_email"><?php echo $pageTitle ?> Email</label>
                            <input class="form-control" type="text" name="mrcht_email" id="mrcht_email" value="<?php
                                                                                                                if (isset($dataExisted) && isset($row['email']) && !isset($mrcht_email)) {
                                                                                                                    echo $row['email'];
                                                                                                                } else if (isset($dataExisted) && isset($row['email']) && isset($mrcht_email)) {
                                                                                                                    echo $mrcht_email;
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
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="mrcht_address_lbl" for="mrcht_address"><?php echo $pageTitle ?> Address</label>
                    <input class="form-control" type="text" name="mrcht_address" id="mrcht_address" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['address']) && !isset($mrcht_address)) {
                                                                                                                echo $row['address'];
                                                                                                            } else if (isset($dataExisted) && isset($row['address']) && isset($mrcht_address)) {
                                                                                                                echo $mrcht_address;
                                                                                                            } else {
                                                                                                                echo '';
                                                                                                            } ?>" <?php if ($act == '') echo 'readonly' ?>>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_pic_lbl" for="mrcht_pic">Person In Charge</label>
                            <input class="form-control" type="text" name="mrcht_pic" id="mrcht_pic" value="<?php
                                                                                                            if (isset($dataExisted) && isset($row['person_in_charges']) && !isset($mrcht_pic)) {
                                                                                                                echo $row['person_in_charges'];
                                                                                                            } else if (isset($dataExisted) && isset($row['person_in_charges']) && isset($mrcht_pic)) {
                                                                                                                echo $mrcht_pic;
                                                                                                            } else {
                                                                                                                echo '';
                                                                                                            }
                                                                                                            ?>" <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_pic_contact_lbl" for="mrcht_pic_contact">Person In Charge Contact</label>
                            <input class="form-control" type="number" step="any" name="mrcht_pic_contact" id="mrcht_pic_contact" value="<?php
                                                                                                                                        if (isset($dataExisted) && isset($row['person_in_charges_contact']) && !isset($mrcht_pic_contact)) {
                                                                                                                                            echo $row['person_in_charges_contact'];
                                                                                                                                        } else if (isset($dataExisted) && isset($row['person_in_charges_contact']) && isset($mrcht_pic_contact)) {
                                                                                                                                            echo $mrcht_pic_contact;
                                                                                                                                        } else {
                                                                                                                                            echo '';
                                                                                                                                        }
                                                                                                                                        ?>" <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="currentDataRemark"><?php echo $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                    <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        centerAlignment("formContainer");
        setButtonColor();
        setAutofocus(action);

        $("#merchant_name").on("input", function() {
            $(".mrcht-name-err").remove();
        });

        $("#mrcht_email").on("input", function() {
            $(".mrcht-email-err").remove();
        });

        $('.submitBtn').on('click', () => {
            $(".error-message").remove();
            //event.preventDefault();
            var name_chk = 0;
            var email_chk = 0;

            if (($('#merchant_name').val() === '' || $('#merchant_name').val() === null || $('#merchant_name')
                    .val() === undefined)) {
                name_chk = 0;
                $("#merchant_name").after(
                    '<span class="error-message mrcht-name-err">Merchant name is required!</span>');
            } else {
                $(".error-message").remove();
                name_chk = 1;
            }

            if (!($('#mrcht_email').val() === '' || $('#mrcht_email').val() === null || $('#mrcht_email').val() ===
                    undefined) && !(isEmail($('#mrcht_email').val()))) {
                email_chk = 0;
                $("#mrcht_email").after('<span class="error-message mrcht-email-err">Wrong email format!</span>');
            } else {
                email_chk = 1;
                $(".mrcht-email-err").remove();
            }

            if (name_chk == 1 && email_chk == 1)
                $(this).closest('form').submit();
            else
                return false;

        })
    </script>

</body>

</html>