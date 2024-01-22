<?php
$pageTitle = "Employee Details";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';
include 'employeeLeave.php';

$tblnameOne = EMPPERSONALINFO;
$tblnameTwo = EMPINFO;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addEmpDetails' : 'updEmpDetails';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/employeeDetailsTable.php';
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
if ($dataID) {
    $rstOne = getData('*', "id = $dataID", '', $tblnameOne, $connect);
    $rstTwo = getData('*', "employee_id = $dataID", '', $tblnameTwo, $connect);

    if (!$rstOne || !$rstTwo) {
        echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
    }

    $row = $rstOne->fetch_assoc();
    $row2 = $rstTwo->fetch_assoc();
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblnameOne, $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
    deleteRecord($tblnameTwo, $dataID, $row['name'], $connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblnameOne & $tblnameTwo Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['name'] . "</b> from <b><i>$tblnameOne & $tblnameTwo Table</i></b>.";
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
        case 'addEmpDetails':
        case 'updEmpDetails':

            //employee personal info table
            $employeeName = postSpaceFilter('employeeName');
            $employeeEmail = postSpaceFilter('employeeEmail');
            $idType = postSpaceFilter('identityType');
            $idNumber = postSpaceFilter('identityNum');
            $gender = postSpaceFilter('employeeGender');
            $dateOfBirth = postSpaceFilter('employeeBirthday');
            $residenceStatus = postSpaceFilter('employeeResidenceStatus');
            $nationality = (postSpaceFilter('nationality') ? postSpaceFilter('nationality') : postSpaceFilter('employeeNationality'));
            $maritalStatus = postSpaceFilter('maritalStatus');
            $noOfChildren = (postSpaceFilter('noOfChild')) ? postSpaceFilter('noOfChild') : '0';
            $race = postSpaceFilter('employeeRace');
            $addressLine1 = postSpaceFilter('employeeAddress1');
            $addressLine2 = postSpaceFilter('employeeAddress2');
            $city = postSpaceFilter('employeeCity');
            $state = postSpaceFilter('employeeState');
            $postcode = postSpaceFilter('employeePostcode');
            $phoneNum = postSpaceFilter('employeePhone');
            $alternatePhoneNum = postSpaceFilter('employeeAlternatePhone');
            $emergName = postSpaceFilter('emergencyContactName');
            $emeryPhone = postSpaceFilter('emergencyContactNum');
            $emeryRelationship = postSpaceFilter('emergencyRelationship');
            $paymentMeth = postSpaceFilter('paymentMethod');
            $bank = postSpaceFilter('bankName');
            $accName = postSpaceFilter('accHolderName');
            $accNum = postSpaceFilter('accNum');

            //employee info
            $joinDate = postSpaceFilter('joinDate');
            $position = postSpaceFilter('position');
            $employeeStatus = postSpaceFilter('employmentStatus');
            $department = postSpaceFilter('department');
            $salaryFrequency = postSpaceFilter('salaryFrequency');
            $salary = postSpaceFilter('salary');
            $currencyUnit = postSpaceFilter('currencyUnit');
            $allowance = postSpaceFilter('allowance');
            $manager = implode(',', postSpaceFilter('managerApproveLeave'));
            $remark = postSpaceFilter('remark');
            $contributingEpf = postSpaceFilter('epfOption');
            $contributingEpfNo = postSpaceFilter('epfNo');
            $employeeEpf = (postSpaceFilter('employeeEpfRate')) ? postSpaceFilter('employeeEpfRate') : '0';
            $employerEpf = (postSpaceFilter('employerEpfRate')) ? postSpaceFilter('employerEpfRate') : '0';
            $employeeTaxNum = postSpaceFilter('empTaxNum');
            $socsoCategory = postSpaceFilter('socsoCtr');
            $eis = postSpaceFilter('eis');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if (isDuplicateRecord("id_number", $idNumber, $tblnameOne, $connect, $dataID) && isDuplicateRecord("name", $employeeName, $tblnameOne, $connect, $dataID)) {
                $err = "Duplicate Identity Number And Name found for This Employee Record";
                break;
            }

            if ($action == 'addEmpDetails') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    $variables = [
                        'name' => 'employeeName',
                        'email' => 'employeeEmail',
                        'id_type' => 'identityType',
                        'id_number' => 'identityNum',
                        'gender' => 'employeeGender',
                        'date_of_birth' => 'employeeBirthday',
                        'residence_status' => 'employeeResidenceStatus',
                        'nationality' => 'employeeNationality',
                        'marital_status' => 'maritalStatus',
                        'no_of_children' => 'noOfChild',
                        'race_id' => 'employeeRace',
                        'address_line_1' => 'employeeAddress1',
                        'address_line_2' => 'employeeAddress2',
                        'city' => 'employeeCity',
                        'state' => 'employeeState',
                        'postcode' => 'employeePostcode',
                        'phone_number' => 'employeePhone',
                        'alternate_phone_number' => 'employeeAlternatePhone',
                        'emergency_contact_name' => 'emergencyContactName',
                        'emergency_contact_phone' => 'emergencyContactNum',
                        'emergency_relationship' => 'emergencyRelationship',
                        'preferred_payment_method' => 'paymentMethod',
                        'bank_id' => 'bankName',
                        'account_holders_name' => 'accHolderName',
                        'account_number' => 'accNum',
                        'join_date' => 'joinDate',
                        'position' => 'position',
                        'employment_status_id' => 'employmentStatus',
                        'department_id' => 'department',
                        'salary_frequency' => 'salaryFrequency',
                        'salary' => 'salary',
                        'currency_unit_id' => 'currencyUnit',
                        'allowance' => 'allowance',
                        'managers_for_leave_approval' => 'managerApproveLeave',
                        'remark' => 'remark',
                        'contributing_epf' => 'epfOption',
                        'contributing_epf_no' => 'epfNo',
                        'employee_epf_rate_id' => 'employeeEpfRate',
                        'employer_epf_rate_id' => 'employerEpfRate',
                        'employee_tax_number' => 'empTaxNum',
                        'socso_category_id' => 'socsoCtr',
                        'eis' => 'eis',
                    ];

                    foreach ($variables as $variable => $fieldName) {
                        // Get the value from the form field
                        $value = postSpaceFilter($fieldName);

                        if (is_array($value)) {
                            $value = implodeWithComma($value);
                        }

                        // Check if the value is not empty before pushing it to the array
                        if ($value) {
                            array_push($newvalarr, $value);
                            array_push($datafield, $variable);
                        }
                    }

                    $query = "INSERT INTO $tblnameOne (name,email,id_type,id_number,gender,date_of_birth,residence_status,nationality,marital_status,no_of_children,race_id,address_line_1,address_line_2,city,state,postcode,phone_number,alternate_phone_number,emergency_contact_name,emergency_contact_phone,emergency_relationship,preferred_payment_method,bank_id,account_holders_name,account_number,create_by,create_date,create_time) VALUES ('$employeeName','$employeeEmail','$idType','$idNumber','$gender','$dateOfBirth','$residenceStatus','$nationality','$maritalStatus','$noOfChildren','$race','$addressLine1','$addressLine2','$city','$state','$postcode','$phoneNum','$alternatePhoneNum','$emergName','$emeryPhone','$emeryRelationship','$paymentMeth','$bank','$accName','$accNum','" . USER_ID . "',curdate(),curtime());";
                    $returnData = mysqli_query($connect, $query);

                    if ($returnData) {
                        $empIDQuery = "SELECT id FROM $tblnameOne WHERE id_number = '$idNumber'";
                        $empIDResult =  mysqli_query($connect, $empIDQuery);

                        if (!$empIDResult) {
                            echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                            echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                        }

                        $empIDRow = mysqli_fetch_assoc($empIDResult);

                        $query = "INSERT INTO $tblnameTwo(employee_id, join_date, position, employment_status_id, department_id, salary_frequency, salary, currency_unit_id, allowance, managers_for_leave_approval, remark, contributing_epf, contributing_epf_no, employee_epf_rate_id, employer_epf_rate_id, employee_tax_number, socso_category_id, eis, create_by, create_date, create_time) VALUES ('{$empIDRow['id']}', '$joinDate', '$position', '$employeeStatus', '$department', '$salaryFrequency', '$salary', '$currencyUnit', '$allowance', '$manager', '$remark', '$contributingEpf', '$contributingEpfNo', '$employeeEpf', '$employerEpf', '$employeeTaxNum', '$socsoCategory', '$eis', '" . USER_ID . "', curdate(), curtime());";
                        $returnData = mysqli_query($connect, $query);
                        $dataID = $connect->insert_id;

                        //Assign Leave Days To New Employee
                        employeeLeaveCheckColumn($connect, $empIDRow['id']);
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {

                    $fieldsOne = array(
                        'name' => $employeeName,
                        'email' => $employeeEmail,
                        'id_type' => $idType,
                        'id_number' => $idNumber,
                        'gender' => $gender,
                        'date_of_birth' => $dateOfBirth,
                        'residence_status' => $residenceStatus,
                        'nationality' => $nationality,
                        'marital_status' => $maritalStatus,
                        'no_of_children' => $noOfChildren,
                        'race_id' => $race,
                        'address_line_1' => $addressLine1,
                        'address_line_2' => $addressLine2,
                        'city' => $city,
                        'state' => $state,
                        'postcode' => $postcode,
                        'phone_number' => $phoneNum,
                        'alternate_phone_number' => $alternatePhoneNum,
                        'emergency_contact_name' => $emergName,
                        'emergency_contact_phone' => $emeryPhone,
                        'emergency_relationship' => $emeryRelationship,
                        'preferred_payment_method' => $paymentMeth,
                        'bank_id' => $bank,
                        'account_holders_name' => $accName,
                        'account_number' => $accNum
                    );

                    $fieldsTwo = array(
                        'join_date' => $joinDate,
                        'position' => $position,
                        'employment_status_id' => $employeeStatus,
                        'department_id' => $department,
                        'salary_frequency' => $salaryFrequency,
                        'salary' => $salary,
                        'currency_unit_id' => $currencyUnit,
                        'allowance' => $allowance,
                        'managers_for_leave_approval' => $manager,
                        'remark' => $remark,
                        'contributing_epf' => $contributingEpf,
                        'contributing_epf_no' => $contributingEpfNo,
                        'employee_epf_rate_id' => $employeeEpf,
                        'employer_epf_rate_id' => $employerEpf,
                        'employee_tax_number' => $employeeTaxNum,
                        'socso_category_id' => $socsoCategory,
                        'eis' => $eis
                    );

                    // Check and push changed values into arrays
                    foreach ($fieldsOne as $variable => $value) {
                        if ($row[$variable] != $value) {
                            array_push($oldvalarr, $row[$variable]);
                            array_push($chgvalarr, $value);
                            array_push($datafield, $variable);
                        }
                    }

                    // Check and push changed values into arrays
                    foreach ($fieldsTwo as $variable => $value) {
                        if ($row2[$variable] != $value) {
                            array_push($oldvalarr, $row2[$variable]);
                            array_push($chgvalarr, $value);
                            array_push($datafield, $variable);
                        }
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE $tblnameOne SET name='$employeeName', email='$employeeEmail', id_type='$idType', id_number='$idNumber', gender='$gender', date_of_birth='$dateOfBirth', residence_status='$residenceStatus', nationality='$nationality', marital_status='$maritalStatus', no_of_children='$noOfChildren', race_id='$race', address_line_1='$addressLine1', address_line_2='$addressLine2', city='$city', state='$state', postcode='$postcode', phone_number='$phoneNum', alternate_phone_number='$alternatePhoneNum', emergency_contact_name='$emergName', emergency_contact_phone='$emeryPhone', emergency_relationship='$emeryRelationship', preferred_payment_method='$paymentMeth', bank_id='$bank', account_holders_name='$accName', account_number='$accNum', update_date=CURDATE(), update_time=CURTIME(), update_by='" . USER_ID . "' WHERE id='$dataID';";
                        $returnData = mysqli_query($connect, $query);
                        $query = "UPDATE $tblnameTwo SET join_date='$joinDate', position='$position', employment_status_id='$employeeStatus', department_id='$department', salary_frequency='$salaryFrequency', salary='$salary', currency_unit_id='$currencyUnit', allowance='$allowance', managers_for_leave_approval='$manager', remark='$remark', contributing_epf='$contributingEpf', contributing_epf_no='$contributingEpfNo', employee_epf_rate_id='$employeeEpf', employer_epf_rate_id='$employerEpf', employee_tax_number='$employeeTaxNum', socso_category_id='$socsoCategory', eis='$eis', update_date=CURDATE(), update_time=CURTIME(), update_by='" . USER_ID . "' WHERE employee_id='$dataID';";
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
                    'query_table'  => $tblnameOne . " & " . $tblnameTwo,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblnameOne . " & " . $tblnameTwo, $pageAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($pageAction == 'Edit') {
                    $log['oldval']  = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblnameOne . " & " . $tblnameTwo, $pageAction, (isset($returnData) ? '' : $errorMsg));
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
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/employeeDetails.css">
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

        <div id="employeeDetailsFormContainer" class="container d-flex justify-content-center">
            <div class="col-8 col-md-6 formWidthAdjust">
                <form id="employeeDetailsForm" method="post" novalidate>
                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>
                    <!-- start step indicators -->
                    <div class="form-header d-flex mb-4 py-4">
                        <span class="stepIndicator">Personal Information</span>
                        <span class="stepIndicator">Emergency Information</span>
                        <span class="stepIndicator">Bank Information</span>
                        <span class="stepIndicator">Employment Information</span>
                        <span class="stepIndicator">Statutory Requirements</span>
                    </div>
                    <!-- end step indicators -->

                    <div id="err_msg" class="text-center h5">
                        <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                    </div>

                    <!-- step one -->
                    <div class="step" id="personalInfo">
                        <p class="text-center mb-3 h3">Personal Information</p>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Employee Identity</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="identityTypeLbl" for="identityType">Identity type </label>
                                        <select class="form-select" aria-label="Default select example" name="identityType" id="identityType" required>
                                            <?php
                                            $result = getData('*', '', '', ID_TYPE, $connect);

                                            echo "<option value disabled selected>Select identity type</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowIDType = $result->fetch_assoc()) {
                                                $selected = isset($row['id_type']) && $rowIDType['id'] == $row['id_type'] ? "selected" : "";
                                                echo "<option value='{$rowIDType['id']}' $selected>{$rowIDType['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="identityNumberLbl" for="identityNum">Identity Number <span class="requireRed">*</span></label>
                                        <input class="form-control" type="tel" name="identityNum" id="identityNum" value="<?php if (isset($row['id_number'])) echo $row['id_number'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Personal Details</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-4 mb-2">
                                        <label class="form-label" id="nameLbl" for="employeeName">Full Name <span class="requireRed">*</span></label>
                                        <input class="form-control " type="text" name="employeeName" id="employeeName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" required>
                                    </div>

                                    <div class="col-sm-4 mb-2">
                                        <div>
                                            <label class="form-label" id="emailLbl" for="employeeEmail">Email <span class="requireRed">*</span></label>
                                            <input class="form-control" type="text" name="employeeEmail" id="employeeEmail" value="<?php if (isset($row['email'])) echo $row['email'] ?>" >
                                        </div>
                                        <span id="emailMsg1"></span>
                                    </div>

                                    <div class="col-sm-4 mb-2">
                                        <label class="form-label" id="genderLbl" for="employeeGender">Gender <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="employeeGender" id="employeeGender" required>
                                            <option value disabled selected>Select your gender</option>
                                            <option value="Female" <?php echo isset($row['gender']) && $row['gender'] == 'Female' ? "selected" : ""; ?>>Female</option>
                                            <option value="Male" <?php echo isset($row['gender']) && $row['gender'] == 'Male' ? "selected" : ""; ?>>Male</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">

                                    <div class="col-sm-3 mb-2">
                                        <label class="form-label" id="birthdayLbl" for="employeeBirthday">Birthday <span class="requireRed">*</span></label>
                                        <input class="form-control" type="date" name="employeeBirthday" id="employeeBirthday" min='1500-01-01' max='3000-12-31' value="<?php if (isset($row['date_of_birth'])) echo $row['date_of_birth'] ?>" required>
                                    </div>
 
                                    <div class="col-sm-3 mb-2">
                                        <label class="form-label" id="raceLbl" for="employeeRace">Race</label>
                                        <select class="form-select" aria-label="Default select example" name="employeeRace" id="employeeRace" required>
                                            <?php
                                            $result = getData('*', '', '', RACE, $connect);

                                            echo "<option value disabled selected>Select employee race</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowRace = $result->fetch_assoc()) {
                                                $selected = isset($row['race_id']) && $rowRace['id'] == $row['race_id'] ? "selected" : "";
                                                echo "<option value='{$rowRace['id']}' $selected>{$rowRace['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-3 mb-2">
                                        <label class="form-label" id="residenceStatusLbl" for="employeeResidenceStatus">Residence <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="employeeResidenceStatus" id="employeeResidenceStatus" required>
                                            <option value disabled selected>Select employee residence status</option>
                                            <option value="Resident" <?php echo isset($row['residence_status']) && $row['residence_status'] == 'Resident' ? "selected" : ""; ?>>Resident</option>
                                            <option value="Non-Resident" <?php echo isset($row['residence_status']) && $row['residence_status'] == 'Non-Resident' ? "selected" : ""; ?>>Non-Resident</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-3 mb-2">
                                        <label class="form-label" id="nationalityLbl" for="employeeNationality">Nationality <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="employeeNationality" id="employeeNationality" required>
                                            <?php
                                            $result = getData('*', '', '', 'countries', $connect);

                                            echo "<option value disabled selected>Select employee nationality</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowNationality = $result->fetch_assoc()) {
                                                $phoneCode = $rowNationality['phonecode'];
                                                $selected = isset($row['nationality']) && $rowNationality['id'] == $row['nationality'] ? "selected" : "";
                                                echo "<option value='{$rowNationality['id']}' data-phone-code='{$phoneCode}' $selected>{$rowNationality['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <input class="form-control" type="hidden" name="nationality" id="nationality" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employeePhoneLbl" for="employeePhone">Phone Number<span class="requireRed">*</span></label>
                                        <div class="row">
                                            <div class="col-auto" style=" padding-right: 0; border-radius:3px 0 0 3px">
                                                <span class="input-group-text" style="height: 40px;">+<span id="phoneCodeSpan">00</span></span>
                                            </div>
                                            <div class="col" style=" padding-left: 0;">
                                                <input type="text" name="employeePhone" id="employeePhone" class="form-control" style="height: 40px;" required value="<?php if (isset($row['phone_number'])) echo $row['phone_number'] ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employeeAlternatePhoneLbl" for="employeeAlternatePhone">Alternate Phone Number</label>
                                        <div class="row">
                                            <div class="col-auto" style=" padding-right: 0; border-radius:3px 0 0 3px">
                                                <span class="input-group-text" style="height: 40px;">+<span id="alternatePhoneCodeSpan">00</span></span>
                                            </div>
                                            <div class="col" style=" padding-left: 0;">
                                                <input type="text" name="employeeAlternatePhone" id="employeeAlternatePhone" class="form-control" style="height: 40px;" value="<?php if (isset($row['alternate_phone_number'])) echo $row['alternate_phone_number'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Address</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employeeAddressOneLbl" for="employeeAddress1">Address Line 1</label>
                                        <input class="form-control " type="text" name="employeeAddress1" id="employeeAddress1" value="<?php if (isset($row['address_line_1'])) echo $row['address_line_1'] ?>">
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employeeAddress2Lbl" for="employeeAddress2">Address Line 2</label>
                                        <input class="form-control " type="text" name="employeeAddress2" id="employeeAddress2" value="<?php if (isset($row['address_line_2'])) echo $row['address_line_2'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-5 mb-2">
                                        <label class="form-label" id="employeeCityLbl" for="employeeCity">City</label>
                                        <input class="form-control " type="text" name="employeeCity" id="employeeCity" value="<?php if (isset($row['city'])) echo $row['city'] ?>">
                                    </div>

                                    <div class="col-sm-5 mb-2">
                                        <label class="form-label" id="employeeStateLbl" for="employeeState">State</label>
                                        <input class="form-control " type="text" name="employeeState" id="employeeState" value="<?php if (isset($row['state'])) echo $row['state'] ?>" <>
                                    </div>

                                    <div class="col-sm-2 mb-2">
                                        <label class="form-label" id="employeePostcodeLbl" for="employeePostcode">Postcode</label>
                                        <input class="form-control " type="number" name="employeePostcode" id="employeePostcode" value="<?php if (isset($row['postcode'])) echo $row['postcode'] ?>">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Marital status </legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="maritalStatusLbl" for="maritalStatus">Marital status <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="maritalStatus" id="maritalStatus" required>
                                            <?php
                                            $result = getData('*', '', '', MRTL_STATUS, $connect);

                                            echo "<option value disabled selected>Select employee race</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowMaritalSts = $result->fetch_assoc()) {
                                                $selected = isset($row['marital_status']) && $rowMaritalSts['id'] == $row['marital_status'] ? "selected" : "";
                                                echo "<option value='{$rowMaritalSts['id']}' $selected>{$rowMaritalSts['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="noOfChildLbl" for="noOfChild">No of Children</label>
                                        <input class="form-control " type="number" name="noOfChild" id="noOfChild" value="<?php if (isset($row['no_of_children'])) echo $row['no_of_children'] ?>">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- step two -->
                    <div class="step" id="emergencyInfo">
                        <p class="text-center mb-3 h3">Emergency Information</p>
                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Emergency Information</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="form-label" id="emergencyContactNameLbl" for="emergencyContactName">Emergency Contact Name <span class="requireRed">*</span></label>
                                        <input class="form-control " type="text" name="emergencyContactName" id="emergencyContactName" value="<?php if (isset($row['emergency_contact_name'])) echo $row['emergency_contact_name'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="emergencyRelationshipLbl" for="emergencyRelationship">Relationship <span class="requireRed">*</span></label>
                                        <input class="form-control" type="text" name="emergencyRelationship" id="emergencyRelationship" value="<?php if (isset($row['emergency_relationship'])) echo $row['emergency_relationship'] ?>" required>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="emergencyContactNumLbl" for="emergencyContactNum">Emergency Contact Number <span class="requireRed">*</span></label>
                                        <div class="row">
                                            <div class="col-auto" style=" padding-right: 0; border-radius:3px 0 0 3px">
                                                <span class="input-group-text" style="height: 40px;">+<span id="emergencyContactNumSpan">00</span></span>
                                            </div>
                                            <div class="col" style=" padding-left: 0;">
                                                <input type="text" name="emergencyContactNum" id="emergencyContactNum" class="form-control" style="height: 40px;" value="<?php if (isset($row['emergency_contact_phone'])) echo $row['emergency_contact_phone'] ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- step three -->
                    <div class="step" id="bankInfo">

                        <p class="text-center mb-3 h3">BANK Information</p>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">BANK Information</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="paymentMethodLbl" for="paymentMethod">Payment Method <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="paymentMethod" id="paymentMethod" required>
                                            <?php
                                            $result = getData('*', '', '', PAY_METH, $connect);
                                            echo "<option value disabled selected>Select employee preferred payment method</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowPayMeth = $result->fetch_assoc()) {
                                                $selected = isset($row['preferred_payment_method']) && $rowPayMeth['id'] == $row['preferred_payment_method'] ? "selected" : "";
                                                echo "<option value='{$rowPayMeth['id']}' $selected>{$rowPayMeth['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="bankLbl" for="bankName">BANK<span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="bankName" id="bankName" required>
                                            <?php
                                            $result = getData('*', '', '', BANK, $connect);
                                            echo "<option value disabled selected>Select preferred bank</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowPayMeth = $result->fetch_assoc()) {
                                                $selected = isset($row['bank_id']) && $rowPayMeth['id'] == $row['bank_id'] ? "selected" : "";
                                                echo "<option value='{$rowPayMeth['id']}' $selected>{$rowPayMeth['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="accHolderNameLbl" for="accHolderName">Account Holder's Name <span class="requireRed">*</span></label>
                                        <input class="form-control" type="text" name="accHolderName" id="accHolderName" value="<?php if (isset($row['account_holders_name'])) echo $row['account_holders_name'] ?>" required>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="accNumLbl" for="accNum">Account Number <span class="requireRed">*</span></label>
                                        <input class="form-control" type="number" name="accNum" id="accNum" value="<?php if (isset($row['account_number'])) echo $row['account_number'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- step four -->
                    <div class="step" id="employmentInfo">

                        <p class="text-center mb-3 h3">Employment Information</p>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Employment Information</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="joinDateLbl" for="joinDate">Join Date <span class="requireRed">*</span></label>
                                        <input class="form-control" type="date" min='1500-01-01' max='3000-12-31' name="joinDate" id="joinDate" value="<?php if (isset($row2['join_date'])) echo $row2['join_date'] ?>" required>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="departmentLbl" for="department">Department <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="department" id="department" required>
                                            <?php
                                            $result = getData('*', '', '', DEPT, $connect);

                                            echo "<option value disabled selected>Select employee department</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowDepartment = $result->fetch_assoc()) {
                                                $selected = isset($row2['department_id']) && $rowDepartment['id'] == $row2['department_id'] ? "selected" : "";
                                                echo "<option value='{$rowDepartment['id']}' $selected>{$rowDepartment['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="positionLbl" for="position">Position </label>
                                        <input class="form-control" type="text" name="position" id="position" value="<?php if (isset($row2['position'])) echo $row2['position'] ?>">
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employmentStatusLbl" for="employmentStatus">Employment Status <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="employmentStatus" id="employmentStatus" required>
                                            <?php
                                            $result = getData('*', '', '', EM_TYPE_STATUS, $connect);

                                            echo "<option value disabled selected>Select employee status</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowEmpSts = $result->fetch_assoc()) {
                                                $selected = isset($row2['employment_status_id']) && $rowEmpSts['id'] == $row2['employment_status_id'] ? "selected" : "";
                                                echo "<option value='{$rowEmpSts['id']}' $selected>{$rowEmpSts['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-12 mb-2">
                                        <label class="form-label" id="managerAprroveLeaveLbl" for="managerApproveLeave">Manager Approval For Leave <span class="requireRed">*</span></label><br>
                                        <div>
                                            <select onchange="setManagerRequiredAttribute();" class="multiple_select form-control" name="managerApproveLeave[]" id="managerApproveLeave" multiple style="width: 100%;">
                                                <?php
                                                $result = getData('*', '', '', USR_USER, $connect);

                                                if (!$result) {
                                                    echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                                }

                                                while ($rowUser = $result->fetch_assoc()) {
                                                    if (isset($row2['managers_for_leave_approval'])) {
                                                        $manageAssign = explode(',', $row2['managers_for_leave_approval']);
                                                        $managerAssignJSON = json_encode($manageAssign);
                                                    }
                                                    echo "<option value='{$rowUser['id']}'>{$rowUser['name']}</option>";
                                                }
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-12 mb-2">
                                        <label class="form-label" id="remarkLbl" for="remark">Remark</label>
                                        <textarea class="form-control" name="remark" id="remark" rows="3"><?php if (isset($row2['remark'])) echo $row2['remark'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Salary Information</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="salaryFrequencyLbl" for="salaryFrequency">Salary Frequency <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="salaryFrequency" id="salaryFrequency" required>
                                            <option value disabled selected>Select salary payment frequency</option>
                                            <option value="monthly" <?php echo isset($row2['salary_frequency']) && $row2['salary_frequency'] == 'monthly' ? "selected" : ""; ?>>Monthly</option>
                                            <option value="daily" <?php echo isset($row2['salary_frequency']) && $row2['salary_frequency'] == 'daily'   ? "selected" : ""; ?>>Daily</option>
                                            <option value="hourly" <?php echo isset($row2['salary_frequency']) && $row2['salary_frequency'] == 'hourly'  ? "selected" : ""; ?>>Hourly</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="currencyUnitLbl" for="currencyUnit">Currency Unit <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="currencyUnit" id="currencyUnit" required>
                                            <?php
                                            $result = getData('*', '', '', CUR_UNIT, $connect);

                                            echo "<option value disabled selected>Select currency unit</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowCurUnit = $result->fetch_assoc()) {
                                                $selected = isset($row2['currency_unit_id']) && $rowCurUnit['id'] == $row2['currency_unit_id'] ? "selected" : "";
                                                echo "<option value='{$rowCurUnit['id']}' $selected>{$rowCurUnit['unit']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="salaryLbl" for="salary">Salary <span class="requireRed">*</span></label>
                                        <input class="form-control" type="number" step="any" name="salary" id="salary" value="<?php if (isset($row2['salary'])) echo $row2['salary'] ?>" required>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="allowanceLbl" for="allowance">Allowance </label>
                                        <input class="form-control" type="number" step="any" name="allowance" id="allowance" value="<?php if (isset($row2['allowance'])) echo $row2['allowance'] ?>">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- step five -->
                    <div class="step" id="statutoryRequirements">

                        <p class="text-center mb-3 h3">Statutory Requirements</p>

                        <fieldset class="border p-2" style="border-radius: 3px;">
                            <legend class="float-none w-auto p-2">Statutory Requirements</legend>
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="epfOptionLbl" for="epfOption">Contributing EPF <span class="requireRed">*</span></label>
                                        <select onselect="updateEpfNoField();" class="form-select" aria-label="Default select example" name="epfOption" id="epfOption" required>
                                            <option value disabled selected>Select contributing EPF option</option>
                                            <option value="Yes" <?php echo isset($row2['contributing_epf']) && $row2['contributing_epf'] == 'Yes' ? "selected" : ""; ?>>Yes</option>
                                            <option value="No" <?php echo isset($row2['contributing_epf']) && $row2['contributing_epf'] == 'No'   ? "selected" : ""; ?>>No</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="epfNoLbl" for="epfNo">Contributing EPF No </label>
                                        <input class="form-control" type="number" name="epfNo" id="epfNo" value="<?php if (isset($row2['contributing_epf_no'])) echo $row2['contributing_epf_no'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employeeEpfRateLbl" for="employeeEpfRate">Employee EPF Rate</label>
                                        <select class="form-select" aria-label="Default select example" name="employeeEpfRate" id="employeeEpfRate">
                                            <?php
                                            $result = getData('*', '', '', EMPLOYEE_EPF, $connect);

                                            echo "<option value disabled selected>Select employee epf rate</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowEmpEpfRate = $result->fetch_assoc()) {
                                                $selected = isset($row2['employee_epf_rate_id']) && $rowEmpEpfRate['id'] == $row2['employee_epf_rate_id'] ? "selected" : "";
                                                echo "<option value='{$rowEmpEpfRate['id']}' $selected>{$rowEmpEpfRate['epf_rate']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label" id="employerEpfRateLbl" for="employerEpfRate">Employer EPF Rate</label>
                                        <select class="form-select" aria-label="Default select example" name="employerEpfRate" id="employerEpfRate">
                                            <?php
                                            $result = getData('*', '', '', EMPLOYER_EPF, $connect);

                                            echo "<option value disabled selected>Select employer epf rate</option>";

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowEmrEpfRate = $result->fetch_assoc()) {
                                                $selected = isset($row2['employer_epf_rate_id']) && $rowEmrEpfRate['id'] == $row2['employer_epf_rate_id'] ? "selected" : "";
                                                echo "<option value='{$rowEmrEpfRate['id']}' $selected>{$rowEmrEpfRate['epf_rate']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="row">

                                    <div class="col-sm-4 mb-2">
                                        <label class="form-label" id="empTaxNumLbl" for="empTaxNum">Employee's Tax Number <span class="requireRed">*</span></label>
                                        <input class="form-control" type="number" name="empTaxNum" id="empTaxNum" value="<?php if (isset($row2['employee_tax_number'])) echo $row2['employee_tax_number'] ?>" required>
                                    </div>

                                    <div class="col-sm-4 mb-2">
                                        <label class="form-label" id="socsoCtrLbl" for="socsoCtr">SOCSO Category<span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="socsoCtr" id="socsoCtr" required>
                                            <?php
                                            $result = getData('*', '', '', SOCSO_CATH, $connect);

                                            echo "<option value disabled selected>Select employee socso category</option>";;

                                            if (!$result) {
                                                echo $errorMsgAlert . $clearLocalStorage . $redirectLink;
                                            }

                                            while ($rowSocsoCth = $result->fetch_assoc()) {
                                                $selected = isset($row2['socso_category_id']) && $rowSocsoCth['id'] == $row2['socso_category_id'] ? "selected" : "";
                                                echo "<option value='{$rowSocsoCth['id']}' $selected>{$rowSocsoCth['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-4 mb-2">
                                        <label class="form-label" id="eisLbl" for="eis">EIS <span class="requireRed">*</span></label>
                                        <select class="form-select" aria-label="Default select example" name="eis" id="eis" required>
                                            <option value disabled selected>Select employee eis status</option>
                                            <option value="Yes" <?php echo isset($row2['eis']) && $row2['eis'] == 'Yes' ? "selected" : ""; ?>>Yes</option>
                                            <option value="No" <?php echo isset($row2['eis']) && $row2['eis'] == 'No'   ? "selected" : ""; ?>>No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="row" style="padding-bottom : 20px;">
                        <div class="col-sm-4 text-start button-bottom">
                            <button type="button" name="actionBtn" id="prevBtn" onclick="nextPrev(-1)" class="btn btn-outline-primary ml-auto mt-2 pull-right" style="font-size: 15px;" value="">Previous</button>
                        </div>

                        <div class="col-sm-4 text-center button-bottom">
                            <button type="button" name="actionBtn" class="btn btn-outline-primary ml-auto mt-2 pull-right" value="back" onclick="clearLocalStorageAndRedirect();" style="font-size: 15px;">Back</button>
                            <button type="submit" name="actionBtn" id="editButton" class="btn btn-outline-primary ml-auto mt-2 pull-right" value="updEmpDetails" style="font-size: 15px;">Edit</button>
                        </div>

                        <div class="col-sm-4 text-end button-bottom">
                            <button type="button"  name="actionBtn" id="nextBtn" onclick="nextPrev(1)" class="btn btn-outline-primary ml-auto mt-2 pull-right actionNextSubBtn" value="" style="font-size: 15px;">Next</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

<?php
switch ($act) {
    case 'I':
        $buttonText = 'Add ' . $pageTitle;
        $buttonValue = 'addEmpDetails';
        break;
    case 'E':
        $buttonText = 'Edit ' . $pageTitle;
        $buttonValue = 'updEmpDetails';
        break;
    case '':
        $buttonText = 'View ' . $pageTitle;
        $buttonValue = ' ';
        break;
}
?>

<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ' '; ?>";
    var managerAssignJSON = <?php echo isset($managerAssignJSON) ? $managerAssignJSON : '""' ?>;

    checkCurrentPage(page, action);
    centerAlignment("formContainer");
    setButtonColor();
    preloader(300, action);

    if (!action) {
        $('.multiple_select').select2({
            disabled: true
        });
    } else {
        $('.multiple_select').select2({
            disabled: false
        });
    }

    <?php include "./js/employeeDetails.js" ?>
</script>

</html>