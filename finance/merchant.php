<?php
$pageTitle = "Merchant";
$isFinance = 1;
include '../menuHeader.php';

$merchant_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/finance/merchant_table.php';

// to display data to input
if ($merchant_id) {
    
    $rst = getData('*', "id = '$merchant_id'", '', MERCHANT, $finance_connect);
    //$rst = false; //testing script
    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } else {
        // If $rst is false or no data found ($act==null)
        echo '<script>
                alert("Data not found or an error occurred.");
                window.location.href = "' . $redirect_page . '"; // Redirect to previous page
              </script>';
        exit(); // Stop script execution
    }
}

if (!($merchant_id) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addMerchant':
        case 'updMerchant':
            $merchant_name = post("merchant_name");
            $mrcht_business_no = postSpaceFilter("mrcht_business_no");
            $mrcht_email = postSpaceFilter("mrcht_email");
            $mrcht_contact = postSpaceFilter('mrcht_contact');
            $mrcht_address = postSpaceFilter('mrcht_address');
            $mrcht_pic = postSpaceFilter('mrcht_pic');
            $mrcht_pic_contact = postSpaceFilter('mrcht_pic_contact');
            $merchant_remark = postSpaceFilter('merchant_remark');

            if (!$merchant_name) {
                $name_err = "Merchant name cannot be empty.";
                break;
            } else if (isDuplicateRecord("name", $merchant_name, MERCHANT, $finance_connect, $merchant_id)) {
                $name_err = "Duplicate record found for Merchant name.";
                break;
            } else if ($mrcht_email && !isEmail($mrcht_email)) {
                $email_err = "Wrong email format!";
                break;
            } else if ($action == 'addMerchant') {
                try {
                    $query = "INSERT INTO " . MERCHANT . "(name,business_no,contact,email,address,person_in_charges,person_in_charges_contact,remark,create_by,create_date,create_time) VALUES ('$merchant_name','$mrcht_business_no','$mrcht_contact','$mrcht_email','$mrcht_address','$mrcht_pic','$mrcht_pic_contact','$mrcht_business_no','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $queryResult = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                    
                    if ($queryResult) {

                        $newvalarr = array();

                        // check value
                        if ($merchant_name != '')
                            array_push($newvalarr, $merchant_name);

                        if ($mrcht_business_no != '')
                            array_push($newvalarr, $mrcht_business_no);

                        if ($mrcht_email != '')
                            array_push($newvalarr, $mrcht_email);

                        if ($mrcht_contact != '')
                            array_push($newvalarr, $mrcht_contact);

                        if ($mrcht_address != '')
                            array_push($newvalarr, $mrcht_address);

                        if ($mrcht_pic != '')
                            array_push($newvalarr, $mrcht_pic);
                        
                        if ($mrcht_pic_contact != '')
                            array_push($newvalarr, $mrcht_pic_contact);

                        if ($merchant_remark != '')
                            array_push($newvalarr, $merchant_remark);

                        $newval = implode(",", $newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$merchant_name</b> into <b><i>Merchant Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = MERCHANT;
                        $log['page'] = 'Merchant';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } else{ // Query failed
                        $act = 'F';
                    }
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$merchant_id'", '', MERCHANT, $finance_connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if ($row['name'] != $merchant_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $merchant_name);
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

                    if ($row['remark'] != $merchant_remark) {
                        if ($row['remark'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remark'];

                        array_push($oldvalarr, $old_remark);

                        if ($merchant_remark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $merchant_remark;

                        array_push($chgvalarr, $new_remark);
                    }
                    
                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;
                    error_log("Old Values Array: " . print_r($oldvalarr, true));

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        // edit
                        $query = "UPDATE " . MERCHANT . " SET name = '$merchant_name',business_no = '$mrcht_business_no',email = '$mrcht_email', contact = '$mrcht_contact', address ='$mrcht_address', person_in_charges ='$mrcht_pic', person_in_charges_contact ='$mrcht_pic_contact', remark ='$merchant_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$merchant_id'";
                        $queryResult = mysqli_query($finance_connect, $query);

                        if ($queryResult) {
                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = USER_ID;

                            $log['act_msg'] = USER_NAME . " edited the data";
                            for ($i = 0; $i < sizeof($oldvalarr); $i++) {
                                if ($i == 0)
                                    $log['act_msg'] .= " from <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
                            }
                            $log['act_msg'] .= "  from <b><i>Merchant Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = MERCHANT;
                            $log['page'] = 'Merchant';
                            $log['oldval'] = $oldval;
                            $log['changes'] = $chgval;
                            $log['connect'] = $connect;
                            audit_log($log);
                        }else{
                            $act = 'F';
                        }
                    } else $act = 'NC';
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            }
            break;
        case 'back':
            echo ("<script>location.href = '$redirect_page';</script>");
            break;
    }
}

if (post('act') == 'D') {
    $id = post('id');
    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", '', MERCHANT, $finance_connect);
            $row = $rst->fetch_assoc();

            $merchant_id = $row['id'];
            $merchant_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord(MERCHANT, $id, $merchant_name, $finance_connect, $cdate, $ctime, $pageTitle);
            
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if (!($merchant_id) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $merchant_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$merchant_name</b> from <b><i>Merchant Table</i></b>.";
    $log['page'] = 'Merchant';
    $log['connect'] = $connect;
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
        <p><a href="<?= $redirect_page ?>">Merchant</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
        echo displayPageAction($act, 'Merchant');
        ?></p>

    </div>


    <div id="merchantFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="merchantForm" method="post" action="">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                            echo displayPageAction($act, 'Merchant');
                        ?>
                    </h2>
                </div>


                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="merchant_name_lbl" for="merchant_name">Merchant
                                Name</label>
                            <input class="form-control" type="text" name="merchant_name" id="merchant_name"
                                value="<?php 
                                if (isset($dataExisted) && isset($row['name']) && !isset($merchant_name)){
                                    echo $row['name'];
                                }else if (isset($merchant_name)) {
                                    echo $merchant_name;
                                } else {
                                    echo '';
                                }
                                ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                            <?php if (isset($name_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $name_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_business_no_lbl"
                                for="mrcht_business_no">Merchant Business No</label>
                            <input class="form-control" type="text" name="mrcht_business_no" id="mrcht_business_no"
                                value="<?php 
                                if (isset($dataExisted) && isset($row['business_no']) && !isset($mrcht_business_no)){
                                    echo $row['business_no'];
                                }else if (isset($mrcht_business_no)) {
                                    echo $mrcht_business_no;
                                } else {
                                    echo '';
                                } ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_contact_lbl" for="mrcht_contact">Merchant
                                Contact</label>
                            <input class="form-control" type="number" step="any" name="mrcht_contact" id="mrcht_contact"
                                value="<?php 
                                if (isset($dataExisted) && isset($row['contact']) && !isset($mrcht_contact)){
                                    echo $row['contact'];
                                }else if (isset($mrcht_contact)) {
                                    echo $mrcht_contact;
                                } else {
                                    echo '';
                                }?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_email_lbl" for="mrcht_email">Merchant
                                Email</label>
                            <input class="form-control" type="text" name="mrcht_email" id="mrcht_email"
                                value="<?php 
                                if (isset($dataExisted) && isset($row['email']) && !isset($mrcht_email)){
                                    echo $row['email'];
                                } else if (isset($mrcht_email)) {
                                    echo $mrcht_email;
                                } else {
                                    echo '';
                                }
                                 ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                            <?php if (isset($email_err)) {?>
                            <div id="err_msg">
                                <span class="mt-n1"><?php echo $email_err; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="mrcht_address_lbl" for="mrcht_address">Merchant
                        Address</label>
                    <input class="form-control" type="text" name="mrcht_address" id="mrcht_address"
                        value="<?php 
                        if (isset($dataExisted) && isset($row['address']) && !isset($mrcht_address)){
                            echo $row['address'];
                        }else if (isset($mrcht_address)) {
                            echo $mrcht_address;
                        } else {
                            echo '';
                        } ?>"
                        <?php if ($act == '') echo 'readonly' ?>>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_pic_lbl" for="mrcht_pic">Person In
                                Charge</label>
                            <input class="form-control" type="text" name="mrcht_pic" id="mrcht_pic"
                                value="<?php 
                                    if (isset($dataExisted) && isset($row['person_in_charges']) && !isset($mrcht_pic)){
                                        echo $row['person_in_charges'];
                                    }else if (isset($mrcht_pic)) {
                                        echo $mrcht_pic;
                                    } else {
                                        echo '';
                                    }
                                ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form_lbl" id="mrcht_pic_contact_lbl" for="mrcht_pic_contact">Person
                                In Charge Contact</label>
                            <input class="form-control" type="number" step="any" name="mrcht_pic_contact"
                                id="mrcht_pic_contact"
                                value="<?php 
                                    if (isset($dataExisted) && isset($row['person_in_charges_contact']) && !isset($mrcht_pic_contact)){
                                        echo $row['person_in_charges_contact'];
                                    }else if (isset($mrcht_pic_contact)) {
                                        echo $mrcht_pic_contact;
                                    } else {
                                        echo '';
                                    }
                                ?>"
                                <?php if ($act == '') echo 'readonly' ?>>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label form_lbl" id="merchant_remark_lbl" for="merchant_remark">Merchant
                        Remark</label>
                    <textarea class="form-control" name="merchant_remark" id="merchant_remark" rows="3"
                        <?php if ($act == '') echo 'readonly' ?>><?php 
                                    if (isset($dataExisted) && isset($row['remark']) && !isset($merchant_remark)){
                                        echo $row['remark'];
                                    } else if (isset($merchant_remark)) {
                                        echo $merchant_remark;
                                    } else {
                                        echo '';
                                    } ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addMerchant">Add Merchant</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updMerchant">Edit Merchant</button>';
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
        echo '<script>confirmationDialog("","","Merchant","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
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