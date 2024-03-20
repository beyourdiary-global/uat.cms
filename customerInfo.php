<?php
$pageTitle = "Customer Info";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = CUS_INFO;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/customerInfoTable.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID) && !($act) || !isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, '', $dataID, $row['name'] ." ". $row['last_name'], $connect, $connect, $cdate, $ctime, $pageTitle); 
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" .  $row['name'] . " " . $row['last_name'] . "</b> from <b><i>$tblName Table</i></b>.";
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

            $cusFirstName = postSpaceFilter('cusFirstName');
            $cusLastName = postSpaceFilter('cusLastName');
            $cusGender = postSpaceFilter('cusGender');
            $cusEmail = postSpaceFilter('cusEmail');
            $cusBirthday = (!empty(postSpaceFilter('cusBirthday'))) ? postSpaceFilter('cusBirthday') : '0000-00-00';
            $cusPhoneCode = postSpaceFilter('cusPhoneCode');
            $cusPhoneNum = postSpaceFilter('cusPhoneNum');
            $shippingFirstName = postSpaceFilter('shippingFirstName');
            $shippingLastName = postSpaceFilter('shippingLastName');
            $shippingContactNum = postSpaceFilter('shippingContactNum');
            $company = postSpaceFilter('company');
            $address1 = postSpaceFilter('address1');
            $address2 = postSpaceFilter('address2');
            $country = postSpaceFilter('country');
            $city = postSpaceFilter('city');
            $state = postSpaceFilter('state');
            $zipcode = postSpaceFilter('zipcode');
            $curSegmentation = postSpaceFilter('curSegmentation');
            $tag = postSpaceFilter('tag');
            $personIncharges = postSpaceFilter('personIncharges');

            $variables = [
                'name' => $cusFirstName,
                'last_name' => $cusLastName,
                'gender' => $cusGender,
                'email' => $cusEmail,
                'birthday' => $cusBirthday,
                'phone_country' => $cusPhoneCode,
                'phone_number' => $cusPhoneNum,
                'shipping_name' => $shippingFirstName,
                'shipping_last_name' => $shippingLastName,
                'shipping_contact_number' => $shippingContactNum,
                'shipping_company' => $company,
                'shipping_address_1' => $address1,
                'shipping_address_2' => $address2,
                'shipping_country_region' => $country,
                'shipping_city' => $city,
                'shipping_state_province' => $state,
                'shipping_zip_code' => $zipcode,
                'default_segmentation' => $curSegmentation,
                'tags' => $tag,
                'person_in_charges' => $personIncharges
            ];

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("name", $cusFirstName, $tblName, $connect, $dataID) && isDuplicateRecord("last_name", $cusLastName, $tblName, $connect, $dataID) && isDuplicateRecord("email", $cusEmail, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle;
                break;
            }

            if (isDuplicateRecord("name", $cusFirstName, $tblName, $connect, $dataID) && isDuplicateRecord("last_name", $cusLastName, $tblName, $connect, $dataID) && isDuplicateRecord("phone_number", $cusPhoneNum, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle;
                break;
            }

            if (isDuplicateRecord("name", $cusFirstName, $tblName, $connect, $dataID) && isDuplicateRecord("last_name", $cusLastName, $tblName, $connect, $dataID) && isDuplicateRecord("email", $cusEmail, $tblName, $connect, $dataID) && isDuplicateRecord("phone_number", $cusPhoneNum, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle;
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    foreach ($variables as $variable => $value) {
                        if ($value) {
                            array_push($newvalarr, $value);
                            array_push($datafield, $variable);
                        }
                    }

                    $query = "INSERT INTO " . $tblName . "(name,last_name,gender,email,birthday,phone_country,phone_number,shipping_name,shipping_last_name,shipping_contact_number,shipping_company,shipping_address_1,shipping_address_2,shipping_country_region,shipping_city,shipping_state_province,shipping_zip_code,default_segmentation,tags,person_in_charges,create_by,create_date,create_time) VALUES ('$cusFirstName','$cusLastName','$cusGender','$cusEmail','$cusBirthday','$cusPhoneCode','$cusPhoneNum','$shippingFirstName','$shippingLastName','$shippingContactNum','$company','$address1','$address2','$country','$city','$state','$zipcode','$curSegmentation','$tag','$personIncharges','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);
                    $dataID = $connect->insert_id;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    foreach ($variables as $variable => $value) {
                        if ($row[$variable] != $value) {
                            array_push($oldvalarr, $row[$variable]);
                            array_push($chgvalarr, $value);
                            array_push($datafield, $variable);
                        }
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE $tblName SET name = '$cusFirstName', last_name = '$cusLastName', gender = '$cusGender', email = '$cusEmail', birthday = '$cusBirthday', phone_country = '$cusPhoneCode', phone_number = '$cusPhoneNum', shipping_name = '$shippingFirstName', shipping_last_name = '$shippingLastName', shipping_contact_number = '$shippingContactNum', shipping_company = '$company', shipping_address_1 = '$address1', shipping_address_2 = '$address2', shipping_country_region = '$country', shipping_city = '$city', shipping_state_province = '$state', shipping_zip_code = '$zipcode', default_segmentation = '$curSegmentation', tags = '$tag', person_in_charges = '$personIncharges', update_date = CURDATE(), update_time = CURTIME(), update_by = '" . USER_ID . "' WHERE id = '$dataID'";
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
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-8 col-md-6 formWidthAdjust">
                <form id="myForm" method="post" novalidate>
                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="text-center h5">
                        <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                    </div>

                    <div class="step" id="personalInfo">

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Basic information</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="cusFirstName">First Name </label>
                                        <input class="form-control " type="text" name="cusFirstName" id="cusFirstName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" required <?php if ($act == '') echo 'readonly' ?>>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="cusLastName">Last Name</label>
                                        <input class="form-control " type="text" name="cusLastName" id="cusLastName" value="<?php if (isset($row['last_name'])) echo $row['last_name'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group mb-3">
                                <div class="row">

                                    <div class="col-sm-3">
                                        <label class="form-label" for="cusGender">Gender </label>
                                        <select class="form-select" aria-label="Default select example" name="cusGender" id="cusGender" required <?php if ($act == '') echo 'disabled' ?>>
                                            <option value="" disabled selected>Select customer gender</option>
                                            <option value="Female" <?php echo isset($row['gender']) && $row['gender'] == 'Female' ? "selected" : ""; ?>>Female</option>
                                            <option value="Male" <?php echo isset($row['gender']) && $row['gender'] == 'Male' ? "selected" : ""; ?>>Male</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-5">
                                        <label class="form-label" for="cusEmail">Email </label>
                                        <input class="form-control " type="email" name="cusEmail" id="cusEmail" value="<?php if (isset($row['email'])) echo $row['email'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                        <span id="emailMsg"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="cusBirthday">Birthday</label>
                                        <input class="form-control" type="date" name="cusBirthday" id="cusBirthday" value="<?php if (isset($row['birthday'])) echo $row['birthday'] ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group mb-3">
                                <div class="row">

                                    <div class="col-sm-3">
                                        <label class="form-label" for="cusPhoneCode">Phone Code </label>
                                        <select class="form-select" aria-label="Default select example" name="cusPhoneCode" id="cusPhoneCode" required <?php if ($act == '') echo 'disabled' ?>>
                                            <?php
                                            $resultPhoneCode = getData('*', '', '', 'countries', $connect);

                                            if (!$resultPhoneCode) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }

                                            echo "<option value='' disabled selected>Select Phone Code</option>";

                                            while ($rowPhoneCode = $resultPhoneCode->fetch_assoc()) {
                                                $selected = isset($row['phone_country']) && $rowPhoneCode['id'] == $row['phone_country'] ? "selected" : "";
                                                echo "<option value='{$rowPhoneCode['id']}' $selected>+{$rowPhoneCode['phonecode']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-9">
                                        <label class="form-label" for="cusPhoneNum">Phone Number </label>
                                        <input type="text" name="cusPhoneNum" id="cusPhoneNum" class="form-control" style="height: 40px;" required value="<?php if (isset($row['phone_number'])) echo $row['phone_number'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Shipping Info</legend>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="shippingFirstName">First Name </label>
                                        <input class="form-control " type="text" name="shippingFirstName" id="shippingFirstName" value="<?php if (isset($row['shipping_name'])) echo $row['shipping_name'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="shippingLastName">Last Name</label>
                                        <input class="form-control " type="text" name="shippingLastName" id="shippingLastName" value="<?php if (isset($row['shipping_last_name'])) echo $row['shipping_last_name'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="shippingContactNum">Contact Number</label>
                                        <input class="form-control " type="text" name="shippingContactNum" id="shippingContactNum" value="<?php if (isset($row['shipping_contact_number'])) echo $row['shipping_contact_number'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="company">Company</label>
                                        <input class="form-control " type="text" name="company" id="company" value="<?php if (isset($row['shipping_company'])) echo $row['shipping_company'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="address1">Address Line 1</label>
                                        <input class="form-control " type="text" name="address1" id="address1" value="<?php if (isset($row['shipping_address_1'])) echo $row['shipping_address_1'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>


                                    <div class="col-sm-6">
                                        <label class="form-label" for="address2">Address Line 2</label>
                                        <input class="form-control " type="text" name="address2" id="address2" value="<?php if (isset($row['shipping_address_2'])) echo $row['shipping_address_2'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label class="form-label" for="country">Country/Region</label>
                                        <select class="form-select" aria-label="Default select example" name="country" id="country" <?php if ($act == '') echo 'disabled' ?>>
                                            <?php
                                            $resultCountry = getData('*', '', '', 'countries', $connect);

                                            if (!$resultCountry) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }

                                            echo "<option disabled selected>Select Country</option>";

                                            while ($rowCountry = $resultCountry->fetch_assoc()) {
                                                $selected = isset($row['shipping_country_region']) && $rowCountry['id'] == $row['shipping_country_region'] ? "selected" : "";
                                                echo "<option value='{$rowCountry['id']}' $selected>{$rowCountry['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-3">
                                        <label class="form-label" for="city">City</label>
                                        <input class="form-control " type="text" name="city" id="city" value="<?php if (isset($row['shipping_city'])) echo $row['shipping_city'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>

                                    <div class="col-sm-3">
                                        <label class="form-label" for="state">State/province</label>
                                        <input class="form-control " type="text" name="state" id="state" value="<?php if (isset($row['shipping_state_province'])) echo $row['shipping_state_province'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>

                                    <div class="col-sm-3">
                                        <label class="form-label" for="zipcode">ZIP Code</label>
                                        <input class="form-control " type="number" name="zipcode" id="zipcode" value="<?php if (isset($row['shipping_zip_code'])) echo $row['shipping_zip_code'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Other </legend>
                            <div class="form-group mb-3">

                                <div class="row">
                                    <div class="col-sm-4">
                                        <label class="form-label" for="curSegmentation">Customer Segmentation </label>
                                        <select class="form-select" aria-label="Default select example" name="curSegmentation" id="curSegmentation" <?php if ($act == '') echo 'disabled' ?>>
                                            <?php
                                            $resultCusSegmentation = getData('*', '', '', CUR_SEGMENTATION, $connect);

                                            if (!$resultCusSegmentation) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }

                                            echo "<option value='' disabled selected>Select customer sgementation</option>";

                                            while ($rowCurSegmentation = $resultCusSegmentation->fetch_assoc()) {
                                                $selected = isset($row['default_segmentation']) && $rowCurSegmentation['id'] == $row['default_segmentation'] ? "selected" : "";
                                                echo "<option value='{$rowCurSegmentation['id']}' $selected>{$rowCurSegmentation['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="tag">Tags </label>
                                        <select class="form-select" aria-label="Default select example" name="tag" id="tag" <?php if ($act == '') echo 'disabled' ?>>
                                            <?php
                                            $resultTag = getData('*', '', '', TAG, $connect);

                                            if (!$resultTag) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }

                                            echo "<option value='' disabled selected>Select customer tags</option>";

                                            while ($rowTag = $resultTag->fetch_assoc()) {
                                                $selected = isset($row['tags']) && $rowTag['id'] == $row['tags'] ? "selected" : "";
                                                echo "<option value='{$rowTag['id']}' $selected>{$rowTag['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-4">
                                        <label class="form-label" for="personIncharges">Person in charges </label>
                                        <select class="form-select" aria-label="Default select example" name="personIncharges" id="personIncharges" required <?php if ($act == '') echo 'disabled' ?>>
                                            <?php
                                            $resultUser = getData('*', '', '', USR_USER, $connect);

                                            if (!$resultUser) {
                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                            }

                                            echo "<option value='' disabled selected>Select person in charges</option>";

                                            while ($rowUser = $resultUser->fetch_assoc()) {
                                                $selected = isset($row['person_in_charges']) && $rowUser['id'] == $row['person_in_charges'] ? "selected" : "";
                                                echo "<option value='{$rowUser['id']}' $selected>{$rowUser['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                            <?php echo ($act) ? '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="' . $actionBtnValue . '">' . $pageActionTitle . '</button>' : ''; ?>
                            <button class="btn btn-rounded btn-primary mx-2 mb-2 backBtn" name="actionBtn" id="actionBtn" value="back">Back</button>
                        </div>
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

        $(document).ready(function() {
            $("#cusEmail").on("input", function() {
                if (!$("#cusEmail").val()) {
                    $("#emailMsg").html("<p style='color:red;margin-bottom:0'>Email is required!</p>");
                } else if (!validateEmail()) {
                    $("#emailMsg").html("<p style='color:red;margin-bottom:0'>Invalid Email Format</p>");
                } else {
                    $("#emailMsg").html("");
                }
            });

            $("#actionBtn").on("click", function(event) {
                if (!validateEmail()) {
                    $("#emailMsg").html("<p style='color:red;margin-bottom:0'>Invalid Email Format</p>");
                    event.preventDefault();
                }

                if (!$("#cusEmail").val()) {
                    $("#emailMsg").html("<p style='color:red;margin-bottom:0'>Email is required!</p>");
                    event.preventDefault();
                }
            });
        });

        function validateEmail() {
            // get value of input email
            var email = $("#cusEmail").val();
            // use reular expression
            var reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/
            if (reg.test(email)) {
                return true;
            } else {
                return false;
            }
        }
    </script>

</body>

</html>