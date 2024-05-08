<?php
$pageTitle = "Debit Notes (Invoice)";
$isFinance = 1;
$redirectToCreateInvoicePage = 0; // Default value

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = DEBIT_NOTES_INV;
//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/debit_notes_inv_table.php';
$create_page = $SITEURL . '/finance/debit_inv_create.php';
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
$rst = getData('*', "id = '$dataID'", '', $tblName, $finance_connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc()) && $act != 'I') {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, '', $dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

//View Data
if ($dataID && !$act && USER_ID && !$_SESSION['viewChk'] && !$_SESSION['delChk']) {

    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $row['invoice'] . "</b> from <b><i>$tblName Table</i></b>.";
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

$logo_path = $SITEURL . '/' . img_server . 'themes/';
$defaultDate = date('Y-m-d');

//dropdown list for currency
$pay_list_result = getData('*', '', '', FIN_PAY_METH, $finance_connect);
$pay_terms_result = getData('*', '', '', FIN_PAY_TERMS, $finance_connect);
$proj_result = getData('*', "id = '1'", '', PROJ, $connect);

if (!$proj_result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo $redirectLink;
}

$proj_row = $proj_result->fetch_assoc();
$inv_num = $proj_row['invoice_prefix_debit'] . $proj_row['invoice_next_number_debit'];
$redirectToCreateInvoicePage = postSpaceFilter('createInvoice');
//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':
            $redirectToCreateInvoicePage = postSpaceFilter('createInvoice');
            $inv_id = postSpaceFilter('invID');
            $date = postSpaceFilter('dni_date');
            $due = postSpaceFilter('dni_due');
            $dni_curr = postSpaceFilter('dni_curr_hidden');
            $mName = postSpaceFilter('dni_name_hidden');
            $mEmail = postSpaceFilter('dni_email');
            $mAdd = postSpaceFilter('dni_address');
            $mCtc = postSpaceFilter('dni_ctc');
            $dni_pic = postSpaceFilter('dni_pic_hidden');
            $dni_remark = postSpaceFilter('dni_remark');
            $dni_sub = postSpaceFilter('dni_sub');
            $dni_disc = postSpaceFilter('dni_disc');
            $dni_tax = postSpaceFilter('dni_tax');
            $dni_total = postSpaceFilter('dni_total_input');
            $dni_notes = postSpaceFilter('internal_notes');
            $dni_pay = postSpaceFilter('dni_pay');
            $dni_pay_details = postSpaceFilter('dni_pay_details');

            $descriptions = $_POST["prod_desc"];
            $prices = $_POST["price"];
            $quantities = $_POST["quantity"];
            $amounts = $_POST["amount"];

            $pay_terms = postSpaceFilter('pay_terms');

            $productIDs = array();

            foreach ($_POST['prod_desc'] as $index => $description) {
                // Prepare values for SQL query
                $price = $_POST['price'][$index];
                $quantity = $_POST['quantity'][$index];
                $amount = $_POST['amount'][$index];

                // Check if a row already exists for the current invoice_row and description
                $queryCheck = "SELECT id FROM " . DEBIT_INV_PROD . " WHERE invoice_row = '$inv_id' AND description = '$description'";
                $resultCheck = mysqli_query($finance_connect, $queryCheck);

                if (mysqli_num_rows($resultCheck) > 0) {
                    // Row already exists, update its details
                    $rowCheck = mysqli_fetch_assoc($resultCheck);
                    $productID = $rowCheck['id'];

                    // Update product details
                    $queryUpdate = "UPDATE " . DEBIT_INV_PROD . " 
                                    SET price = '$price', quantity = '$quantity', amount = '$amount' 
                                    WHERE id = '$productID'";
                    $update_prod = mysqli_query($finance_connect, $queryUpdate);
                } else {
                    // Row doesn't exist, insert a new row
                    $queryInsert = "INSERT INTO " . DEBIT_INV_PROD . " 
                                    (invoice_row, description, price, quantity, amount, create_by, create_date, create_time) 
                                    VALUES ('$inv_id', '$description', '$price', '$quantity', '$amount', '" . USER_ID . "', curdate(), curtime())";
                    $insert_prod = mysqli_query($finance_connect, $queryInsert);

                    // Get the ID of the inserted product
                    $productID = mysqli_insert_id($finance_connect);
                }

                $productIDs[] = $productID;
            }

            // Combine product IDs into a comma-separated string
            $productIDString = implode(',', $productIDs);

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;
                    if ($inv_id) {
                        array_push($newvalarr, $inv_id);
                        array_push($datafield, 'invoice ID');
                    }

                    if ($date) {
                        array_push($newvalarr, $date);
                        array_push($datafield, 'date');
                    }

                    if ($due) {
                        array_push($newvalarr, $due);
                        array_push($datafield, 'due date');
                    }

                    if ($dni_curr) {
                        array_push($newvalarr, $dni_curr);
                        array_push($datafield, 'currency');
                    }

                    if ($mName) {
                        array_push($newvalarr, $mName);
                        array_push($datafield, 'name');
                    }

                    if ($mEmail) {
                        array_push($newvalarr, $mEmail);
                        array_push($datafield, 'email');
                    }

                    if ($mAdd) {
                        array_push($newvalarr, $mAdd);
                        array_push($datafield, 'address');
                    }

                    if ($mCtc) {
                        array_push($newvalarr, $mCtc);
                        array_push($datafield, 'contact');
                    }

                    if ($dni_pic) {
                        array_push($newvalarr, $dni_pic);
                        array_push($datafield, 'PIC');
                    }

                    if ($dni_remark) {
                        array_push($newvalarr, $dni_remark);
                        array_push($datafield, 'remark');
                    }

                    if ($dni_sub) {
                        array_push($newvalarr, $dni_sub);
                        array_push($datafield, 'subtotal');
                    }

                    if ($dni_disc) {
                        array_push($newvalarr, $dni_disc);
                        array_push($datafield, 'discount');
                    }

                    if ($dni_tax) {
                        array_push($newvalarr, $dni_tax);
                        array_push($datafield, 'tax');
                    }

                    if ($dni_total) {
                        array_push($newvalarr, $dni_total);
                        array_push($datafield, 'total');
                    }

                    if ($dni_notes) {
                        array_push($newvalarr, $dni_notes);
                        array_push($datafield, 'internal notes');
                    }

                    if ($dni_pay) {
                        array_push($newvalarr, $dni_pay);
                        array_push($datafield, 'pay method');
                    }

                    if ($dni_pay_details) {
                        array_push($newvalarr, $dni_pay_details);
                        array_push($datafield, 'payment details');
                    }

                    if ($productIDString) {
                        array_push($newvalarr, $productIDString);
                        array_push($datafield, 'products');
                    }

                    $query2 = "UPDATE " . PROJ . " SET invoice_next_number_debit = invoice_next_number_debit + 1 WHERE id = 1;";
                    $update_inv_no = mysqli_query($connect, $query2);

                    $query = "INSERT INTO " . $tblName . "(projectID, invoice, date, due_date, currency, bill_nameID, bill_add, bill_email, bill_contact, products, pay_method, pay_terms, pay_details, sales_pic, remark, subtotal, discount, tax, total, inv_note, create_by, create_date, create_time) VALUES ('1','$inv_id','$date','$due','$dni_curr','$mName','$mAdd','$mEmail','$mCtc','$productIDString','$dni_pay','$pay_terms','$dni_pay_details','$dni_pic','$dni_remark','$dni_sub','$dni_disc','$dni_tax','$dni_total','$dni_notes','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $finance_connect->insert_id;

                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if ($row['date'] != $date) {
                        array_push($oldvalarr, $row['date']);
                        array_push($chgvalarr, $date);
                        array_push($datafield, 'date');
                    }
                    if ($row['due_date'] != $due) {
                        array_push($oldvalarr, $row['due_date']);
                        array_push($chgvalarr, $due);
                        array_push($datafield, 'due date');
                    }
                    if ($row['currency'] != $dni_curr) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $dni_curr);
                        array_push($datafield, 'currency');
                    }
                    if ($row['bill_nameID'] != $mName) {
                        array_push($oldvalarr, $row['bill_nameID']);
                        array_push($chgvalarr, $mName);
                        array_push($datafield, 'billing name');
                    }
                    if ($row['bill_add'] != $mAdd) {
                        array_push($oldvalarr, $row['bill_add']);
                        array_push($chgvalarr, $mAdd);
                        array_push($datafield, 'billing add');
                    }
                    if ($row['bill_email'] != $mEmail) {
                        array_push($oldvalarr, $row['bill_email']);
                        array_push($chgvalarr, $mEmail);
                        array_push($datafield, 'billing email');
                    }
                    if ($row['bill_contact'] != $mCtc) {
                        array_push($oldvalarr, $row['bill_contact']);
                        array_push($chgvalarr, $mCtc);
                        array_push($datafield, 'billing contact');
                    }
                    if ($row['pay_method'] != $dni_pay) {
                        array_push($oldvalarr, $row['pay_method']);
                        array_push($chgvalarr, $dni_pay);
                        array_push($datafield, 'pay_method');
                    }
                    if ($row['pay_details'] != $dni_pay_details) {
                        array_push($oldvalarr, $row['pay_details']);
                        array_push($chgvalarr, $dni_pay_details);
                        array_push($datafield, 'pay_details');
                    }
                    if ($row['pay_terms'] != $pay_terms) {
                        array_push($oldvalarr, $row['pay_terms']);
                        array_push($chgvalarr, $pay_terms);
                        array_push($datafield, 'payment terms');
                    }
                    if ($row['sales_pic'] != $dni_pic) {
                        array_push($oldvalarr, $row['sales_pic']);
                        array_push($chgvalarr, $dni_pic);
                        array_push($datafield, 'sales_pic');
                    }
                    if ($row['remark'] != $dni_remark) {
                        array_push($oldvalarr, $row['remark']);
                        array_push($chgvalarr, $dni_remark);
                        array_push($datafield, 'remark');
                    }
                    if ($row['subtotal'] != $dni_sub) {
                        array_push($oldvalarr, $row['subtotal']);
                        array_push($chgvalarr, $dni_sub);
                        array_push($datafield, 'subtotal');
                    }
                    if ($row['discount'] != $dni_disc) {
                        array_push($oldvalarr, $row['discount']);
                        array_push($chgvalarr, $dni_disc);
                        array_push($datafield, 'discount');
                    }
                    if ($row['tax'] != $dni_tax) {
                        array_push($oldvalarr, $row['tax']);
                        array_push($chgvalarr, $dni_tax);
                        array_push($datafield, 'tax');
                    }
                    if ($row['total'] != $dni_total) {
                        array_push($oldvalarr, $row['total']);
                        array_push($chgvalarr, $dni_total);
                        array_push($datafield, 'total');
                    }
                    if ($row['inv_note'] != $dni_notes) {
                        array_push($oldvalarr, $row['inv_note']);
                        array_push($chgvalarr, $dni_notes);
                        array_push($datafield, 'notes');
                    }
                    if ($row['products'] != $productIDString) {
                        array_push($oldvalarr, $row['products']);
                        array_push($chgvalarr, $productIDString);
                        array_push($datafield, 'products');
                    }

                    $_SESSION['tempValConfirmBox'] = true;

                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE " . $tblName . " SET invoice = '$inv_id ',date='$date',due_date='$due',currency='$dni_curr',bill_nameID='$mName',bill_add='$mAdd',bill_email='$mEmail',bill_contact='$mCtc', products='$productIDString',pay_method='$dni_pay',pay_details='$dni_pay_details',pay_terms='$pay_terms',sales_pic='$dni_pic',remark='$dni_remark',subtotal='$dni_sub',discount='$dni_disc',tax='$dni_tax',total='$dni_total',inv_note='$dni_notes', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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

//Function(title, subtitle, page name, ajax url path, redirect path, action)
//To show action dialog after finish certain action (eg. edit)

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    if ($redirectToCreateInvoicePage == 1) {
        $url = $create_page . "?id=" . $dataID;
        echo "<script>location.href = '$url';</script>";
    } else {
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
}

if ($redirectToCreateInvoicePage == 1) {
    $url = $create_page . "?id=" . $dataID;
    echo "<script>location.href = '$url';</script>";
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
    <link rel="stylesheet" href="./css/package.css">
</head>
<style>
span.input-group-text{
    border:none;
}
</style>
<body style="background-color: rgb(240, 241, 247);">
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>">
                    <?= $pageTitle ?>
                </a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>

        <div id="formContainer" class="container-fluid mt-2">
            <div class="col-12 col-md-12 formWidthAdjust">
                <form id="form" method="post" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>
                    <div class="container-xxl flex-grow-1">
                        <div class="row invoice-add">
                            <div class="col-lg-9 col-12 mb-lg-0 mb-4">
                                <div class="card invoice-preview-card p-4">
                                    <div class="row m-sm-4 m-0">
                                        <div class="col-7 mb-md-0 mb-3">
                                            <div class="d-flex mb-2 gap-2 align-items-center">
                                                <img id="logo" style="min-height:45px; max-height : 45px; width : auto;"
                                                    src="<?php echo (isset($proj_row['logo'])) ? $logo_path . $proj_row['logo'] : $SITEURL . '/image/logo2.png'; ?>">
                                                <span class="fw-bold fs-4">
                                                    <?php echo $proj_row['company_name']; ?>
                                                </span>
                                            </div>
                                            <p class="mb-2">
                                                <?php echo $proj_row['company_address']; ?>
                                            </p>
                                            <p class="mb-2">
                                                <?php echo $proj_row['company_business_no']; ?>
                                            </p>
                                            <p class="mb-3">
                                                <?php echo $proj_row['company_contact'] . " | " . $proj_row['company_email']; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-5">
                                            <dl class="row mb-2">
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end">
                                                    <span class="h4 text-capitalize mb-0 text-nowrap">Invoice</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-sm-2">
                                                    <div class="input-group input-group-merge disabled w-px-150">
                                                        <span class="input-group-text">#</span>
                                                        <input type="text" class="form-control" value="<?php
                                                        if (isset($dataExisted) && isset($row['invoice']) && !isset($inv_id)) {
                                                            echo $row['invoice'];
                                                        } else if (isset($inv_id)) {
                                                            echo $inv_id;
                                                        } else {
                                                            echo $proj_row['invoice_prefix_debit'] . $proj_row['invoice_next_number_debit'];
                                                        } ?>" name="invID" id="invID" <?php if ($act == '')
                                                        echo 'disabled' ?>readonly/>
                                                    </div>
                                                </dd>
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end">
                                                    <span class="fw-normal">Date:</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-sm-2">
                                                    <input type="text" class="form-control w-px-150 date-picker"
                                                        name="dni_date" id="dni_date" placeholder="YYYY-MM-DD" value="<?php
                                                        if (isset($dataExisted) && isset($row['date']) && !isset($dni_date)) {
                                                            echo $row['date'];
                                                        } else if (isset($dni_date)) {
                                                            echo $dni_date;
                                                        } else {
                                                            echo $defaultDate;
                                                        }
                                                        ?>" <?php if ($act == '')
                                                            echo 'disabled' ?>>
                                                    <?php if (isset($date_err)) { ?>
                                                        <div id="err_msg">
                                                            <span class="mt-n1">
                                                                <?php echo $date_err; ?>
                                                            </span>
                                                        </div>
                                                    <?php } ?>
                                                </dd>
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end">
                                                    <span class="fw-normal">Due Date:</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-sm-2">
                                                    <input type="text" class="form-control w-px-150 date-picker"
                                                        name="dni_due" id="dni_due" placeholder="YYYY-MM-DD" value="<?php
                                                        if (isset($dataExisted) && isset($row['due_date']) && !isset($dni_due)) {
                                                            echo $row['due_date'];
                                                        } else if (isset($dni_due)) {
                                                            echo $dni_due;
                                                        }
                                                        ?>" <?php if ($act == '')
                                                            echo 'disabled' ?>>
                                                    <?php if (isset($date_err)) { ?>
                                                        <div id="err_msg">
                                                            <span class="mt-n1">
                                                                <?php echo $date_err; ?>
                                                            </span>
                                                        </div>
                                                    <?php } ?>
                                                </dd>
                                                <dt class="col-sm-6 mb-2 mb-sm-0 text-md-end">
                                                    <span class="fw-normal">Currency:</span>
                                                </dt>
                                                <dd class="col-sm-6 d-flex justify-content-md-end pe-0 ps-sm-2">
                                                    <div class="col-12 autocomplete">
                                                        <?php
                                                        unset($echoVal);

                                                        if (isset($row['currency']))
                                                            $echoVal = $row['currency'];

                                                        if (isset($echoVal)) {
                                                            $curr_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                                                            if (!$curr_rst) {
                                                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                                            }
                                                            $curr_row = $curr_rst->fetch_assoc();
                                                        }
                                                        ?>
                                                        <input class="form-control" type="text" name="dni_curr"
                                                            id="dni_curr" <?php if ($act == '')
                                                                echo 'disabled' ?>
                                                                value="<?php echo !empty($echoVal) ? $curr_row['unit'] : '' ?>">
                                                        <input type="hidden" name="dni_curr_hidden" id="dni_curr_hidden"
                                                            value="<?php echo (isset($row['currency'])) ? $row['currency'] : ''; ?>">

                                                        <?php if (isset($curr_err)) { ?>
                                                            <div id="err_msg">
                                                                <span class="mt-n1">
                                                                    <?php echo $curr_err; ?>
                                                                </span>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                            </dl>
                                        </div>
                                    </div>
                                    <div class="row  m-sm-4 m-0">
                                        <h6 class="mb-2">Billing To:</h6>
                                        <div class="col-md-6 mb-md-0 mb-2">

                                            <div class="row gy-2">
                                                <div class="col-12 autocomplete">
                                                    <?php
                                                    unset($echoVal);

                                                    if (isset($row['bill_nameID']))
                                                        $echoVal = $row['bill_nameID'];

                                                    if (isset($echoVal)) {
                                                        $mrcht_rst = getData('name', "id = '$echoVal'", '', MERCHANT, $finance_connect);
                                                        if (!$mrcht_rst) {
                                                            echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                                            echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                                        }
                                                        $mrcht_row = $mrcht_rst->fetch_assoc();
                                                    }
                                                    ?>
                                                    <input class="form-control" type="text" placeholder="Customer Name"
                                                        name="dni_name" id="dni_name" <?php if ($act == '')
                                                            echo 'disabled' ?>
                                                            value="<?php echo !empty($echoVal) ? $mrcht_row['name'] : '' ?>">
                                                    <input type="hidden" name="dni_name_hidden" id="dni_name_hidden"
                                                        value="<?php echo (isset($echoVal)) ? $echoVal : ''; ?>">
                                                    <?php if (isset($name_err)) { ?>
                                                        <div id="err_msg">
                                                            <span class="mt-n1">
                                                                <?php echo $name_err; ?>
                                                            </span>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-12">
                                                    <textarea class="form-control" name="dni_address" id="dni_address"
                                                        rows="3" placeholder="Enter Address" <?php if ($act == '')
                                                            echo 'disabled' ?>><?php
                                                        if (isset($dataExisted) && isset($row['bill_add']) && !isset($dni_address)) {
                                                            echo $row['bill_add'];
                                                        } else if (isset($dataExisted) && isset($row['bill_add']) && isset($dni_address)) {
                                                            echo $dni_address;
                                                        } ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <input class="form-control" type="text" placeholder="Customer Email"
                                                        name="dni_email" id="dni_email" value="<?php
                                                        if (isset($dataExisted) && isset($row['bill_email']) && !isset($dni_email)) {
                                                            echo $row['bill_email'];
                                                        } else if (isset($dataExisted) && isset($row['bill_email']) && isset($dni_email)) {
                                                            echo $dni_email;
                                                        } ?>" <?php if ($act == '')
                                                             echo 'disabled' ?>>
                                                    <?php if (isset($email_err)) { ?>
                                                        <div id="err_msg">
                                                            <span class="mt-n1">
                                                                <?php echo $email_err; ?>
                                                            </span>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-12">
                                                    <input class="form-control" type="text" placeholder="Phone Number"
                                                        name="dni_ctc" id="dni_ctc" value="<?php
                                                        if (isset($dataExisted) && isset($row['bill_contact']) && !isset($dni_ctc)) {
                                                            echo $row['bill_contact'];
                                                        } else if (isset($dataExisted) && isset($row['bill_contact']) && isset($dni_ctc)) {
                                                            echo $dni_ctc;
                                                        } ?>" <?php if ($act == '')
                                                             echo 'disabled' ?>>
                                                    <?php if (isset($ctc_err)) { ?>
                                                        <div id="err_msg">
                                                            <span class="mt-n1">
                                                                <?php echo $ctc_err; ?>
                                                            </span>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row m-sm-4 m-0">

                                        <hr class="my-3" />

                                        <div class="row">
                                            <div class="table-responsive mb-3">
                                                <table class="table table-striped" id="productList">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Description</th>
                                                            <th scope="col">Price</th>
                                                            <th scope="col">Quantity</th>
                                                            <th scope="col">Amount</th>
                                                            <th scope="col" id="action_col"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php
                                                        // check act
                                                        if ($act != '')
                                                            $readonly = '';
                                                        else
                                                            $readonly = ' readonly';

                                                        // get value
                                                        unset($echoVal);

                                                        if (isset($row['products']))
                                                            $echoVal = $row['products'];

                                                        // echo
                                                        if (isset($echoVal)) {
                                                            $num = 1; // numbering
                                                            $echoVal = explode(',', $echoVal);
                                                            foreach ($echoVal as $prod_id) {
                                                                // product info
                                                                $product_info_result = getData('*', "id = '$prod_id'", '', DEBIT_INV_PROD, $finance_connect);
                                                                $product_info_row = $product_info_result->fetch_assoc();

                                                                $pid = $product_info_row['id'];
                                                                $pdesc = $product_info_row['description'];
                                                                $pp = $product_info_row['price'];
                                                                $pqty = $product_info_row['quantity'];
                                                                $pamt = $product_info_row['amount'];
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <?= $num ?>
                                                                    </td>
                                                                    <td class="autocomplete"><input type="text"
                                                                            name="prod_desc[]" id="prod_desc_<?= $num ?>"
                                                                            value="<?= $pdesc ?>" onkeyup="prodInfo(this)"
                                                                            <?= $readonly ?>><input type="hidden"
                                                                            name="prod_val[]" id="prod_val_<?= $num ?>"
                                                                            value="<?= $pid ?>">
                                                                        <div id="err_msg">
                                                                            <span class="mt-n1">
                                                                                <?php if (isset($err4))
                                                                                    echo $err4; ?>
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td><input class="readonlyInput" type="text" name="price[]"
                                                                            id="price_<?= $num ?>" value="<?= $pp ?>"
                                                                            onchange="calculateAmount(<?= $num ?>)">
                                                                    </td>
                                                                    <td><input class="readonlyInput" type="text"
                                                                            name="quantity[]" id="quantity_<?= $num ?>"
                                                                            value="<?= $pqty ?>" onchange="calculateAmount(<?= $num ?>)">
                                                                    </td>
                                                                    <td><input class="readonlyInput" type="text" name="amount[]"
                                                                            id="amount_<?= $num ?>" value="<?= $pamt ?>">
                                                                    </td>
                                                                    <?php
                                                                    if ($act != '') {
                                                                        if ($num == 1) {
                                                                            ?>
                                                                            <td><button class="mt-1" id="action_menu_btn" type="button"
                                                                                    onclick="Add()"><i
                                                                                        class="fa-regular fa-square-plus fa-xl"
                                                                                        style="color:#37c22e"></i></button></td>
                                                                            <?php
                                                                        } else {
                                                                            ?>
                                                                            <td><button class="mt-1" id="action_menu_btn" type="button"
                                                                                    onclick="Remove(this)"><i
                                                                                        class="fa-regular fa-trash-can fa-xl"
                                                                                        style="color:#ff0000"
                                                                                        value="Remove"></i></button>
                                                                            </td>
                                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </tr>
                                                                <?php
                                                                $num++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td>1</td>
                                                                <td class="autocomplete"><input type="text"
                                                                        name="prod_desc[]" id="prod_desc_1" value=""
                                                                        onkeyup="prodInfo(this)"><input type="hidden"
                                                                        name="prod_val[]" id="prod_val_1" value=""
                                                                        oninput="prodInfoAutoFill(this)">
                                                                    <div id="err_msg">
                                                                        <span class="mt-n1">
                                                                            <?php if (isset($err4))
                                                                                echo $err4; ?>
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                                <td><input type="number" name="price[]" id="price_1"
                                                                        value=""></td>
                                                                <td><input type="number" name="quantity[]" id="quantity_1"
                                                                        value="">
                                                                </td>
                                                                <td><input class="readonlyInput" type="text" name="amount[]"
                                                                        id="amount_1" value=""></td>

                                                                <td><button class="mt-1" id="action_menu_btn" type="button"
                                                                        onclick="Add()"><i
                                                                            class="fa-regular fa-square-plus fa-xl"
                                                                            style="color:#37c22e"></i></button></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between flex-wrap">
                                                    <div class="col-12 col-md-6">
                                                        <dl class="row mb-2 form-group autocomplete">

                                                            <dt class="col-sm-4 mb-2 mb-sm-0">
                                                                <span class="form_lbl">Salesperson:</span>
                                                            </dt>
                                                            <dd class="col-sm-8 d-flex ps-sm-2 ">
                                                                <?php
                                                                unset($echoVal);

                                                                if (isset($row['sales_pic']))
                                                                    $echoVal = $row['sales_pic'];

                                                                if (isset($echoVal)) {
                                                                    $pic_result = getData('name', "id = '$echoVal'", '', USR_USER, $connect);

                                                                    $pic_row = $pic_result->fetch_assoc();
                                                                }
                                                                ?>
                                                                <input class="form-control" type="text" name="dni_pic"
                                                                    id="dni_pic"
                                                                    value="<?php echo !empty($echoVal) ? $pic_row['name'] : '' ?>" <?php if ($act == '')
                                                                               echo 'readonly' ?>>
                                                                    <input type="hidden" name="dni_pic_hidden"
                                                                        id="dni_pic_hidden"
                                                                        value="<?php echo (isset($echoVal)) ? $echoVal : ''; ?>">
                                                                <div id="err_msg">
                                                                    <span class="mt-n1">
                                                                        <?php if (isset($pic_err))
                                                                            echo $pic_err; ?>
                                                                    </span>
                                                                </div>
                                                            </dd>

                                                        </dl>
                                                        <div class="form-group mb-3">
                                                            <label class="form-label form_lbl"
                                                                for="dni_remark">Remark:</label>
                                                            <textarea class="form-control" name="dni_remark"
                                                                id="dni_remark" rows="3" <?php if ($act == '')
                                                                    echo 'disabled' ?>><?php if (isset($row['remark']))
                                                                    echo $row['remark'] ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="mt-auto mb-auto col-12 col-md-4 justify-content-end">
                                                            <div class="invoice-calculations">
                                                                <div class="d-flex justify-content-between mb-2">
                                                                    <span class="w-px-100">Subtotal:</span>
                                                                    <div class="col-6">
                                                                        <input class="form-control text-end" type="number"
                                                                            step="0.01" name="dni_sub" id="dni_sub" value="<?php
                                                                if (isset($dataExisted) && isset($row['subtotal']) && !isset($dni_sub)) {
                                                                    echo $row['subtotal'];
                                                                } else if (isset($dataExisted) && isset($row['subtotal']) && isset($dni_sub)) {
                                                                    echo $dni_sub;
                                                                } else {
                                                                    echo '';
                                                                } ?>" <?php if ($act == '')
                                                                     echo 'disabled' ?> onchange="calculateTotal()">
                                                                    <?php if (isset($sub_err)) { ?>
                                                                        <div id="err_msg">
                                                                            <span class="mt-n1">
                                                                                <?php echo $sub_err; ?>
                                                                            </span>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Discount:</span>
                                                                <div class="col-6">
                                                                    <input class="form-control text-end" type="number"
                                                                        step="0.01" name="dni_disc" id="dni_disc" value="<?php
                                                                        if (isset($dataExisted) && isset($row['discount']) && !isset($dni_disc)) {
                                                                            echo $row['discount'];
                                                                        } else if (isset($dataExisted) && isset($row['discount']) && isset($dni_disc)) {
                                                                            echo $dni_disc;
                                                                        } else {
                                                                            echo '';
                                                                        } ?>" <?php if ($act == '')
                                                                             echo 'disabled' ?>  onchange="calculateTotal()">
                                                                    <?php if (isset($disc_err)) { ?>
                                                                        <div id="err_msg">
                                                                            <span class="mt-n1">
                                                                                <?php echo $disc_err; ?>
                                                                            </span>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Tax:</span>
                                                                <div class="col-6">
                                                                    <input class="form-control text-end" type="number"
                                                                        step="0.01" name="dni_tax" id="dni_tax" value="<?php
                                                                        if (isset($dataExisted) && isset($row['tax']) && !isset($dni_tax)) {
                                                                            echo $row['tax'];
                                                                        } else if (isset($dataExisted) && isset($row['tax']) && isset($dni_tax)) {
                                                                            echo $dni_tax;
                                                                        } else {
                                                                            echo '';
                                                                        } ?>" <?php if ($act == '')
                                                                             echo 'disabled' ?> onchange="calculateTotal()">
                                                                    <?php if (isset($tax_err)) { ?>
                                                                        <div id="err_msg">
                                                                            <span class="mt-n1">
                                                                                <?php echo $tax_err; ?>
                                                                            </span>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <hr />
                                                            <div class="d-flex justify-content-between">
                                                                <span class="w-px-100">Total:</span>
                                                                <span class="fw-medium" id="dni_total">00.00</span>
                                                                <input type="hidden" name="dni_total_input"
                                                                    id="dni_total_input" value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <div class="form-group mb-3">
                                                <label class="form-label form_lbl" for="internal_notes">Notes:</label>
                                                <textarea class="form-control" name="internal_notes" id="internal_notes"
                                                    rows="3" <?php if ($act == '')
                                                        echo 'disabled' ?>><?php if (isset($row['inv_note']))
                                                        echo $row['inv_note'] ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-12 invoice-actions  mb-4">
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <?php
                                                    // Determine the value based on $act
                                                    switch ($act) {
                                                        case 'I':
                                                            $actionValue = 'addData';
                                                            break;
                                                        case 'E':
                                                            $actionValue = 'updData';
                                                            break;
                                                        default:
                                                            $actionValue = ''; // You may want to handle this case differently based on your logic
                                                    }
                                                    ?>
                                        <input type="hidden" name="createInvoice" id="createInvoice" value="0">
                                        <button class="btn btn-primary d-grid w-100 mb-2 submitBtn createInvoiceButton"
                                            name="actionBtn" id="actionBtn" onclick="createInvoice();"
                                            value="<?= $actionValue ?>">Create Invoice</button>
                                        <?php if ($act == 'I' || $act == 'E') { ?>
                                            <button class="btn btn-primary d-grid w-100 mb-2 submitBtn" name="actionBtn"
                                                id="actionBtn" value="<?= $actionValue ?>">Save As Draft</button>
                                        <?php } ?>
                                        <button class="btn btn-primary d-grid w-100 mb-2 cancel" name="actionBtn"
                                            id="actionBtn" value="back"><span><i
                                                    class="ti ti-send ti-xs me-2"></i>Back</span></button>
                                    </div>
                                </div>
                                <div>
                                    <p class="mb-2">Accept payments via</p>
                                    <select class="form-select mb-2" id="dni_pay" name="dni_pay" <?php if ($act == '')
                                        echo 'disabled' ?>>
                                            <option value="0" disabled selected>Select Payment Method</option>
                                            <?php
                                    if ($pay_list_result->num_rows >= 1) {
                                        $pay_list_result->data_seek(0);
                                        while ($row2 = $pay_list_result->fetch_assoc()) {
                                            $selected = "";
                                            if (isset($dataExisted, $row['pay_method']) && (!isset($dni_pay))) {
                                                $selected = $row['pay_method'] == $row2['id'] ? "selected" : "";
                                            } else if (isset($dni_pay)) {
                                                $selected = $dni_pay == $row2['id'] ? "selected" : "";
                                            }
                                            echo "<option value=\"" . $row2['id'] . "\" $selected>" . $row2['name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=\"0\">None</option>";
                                    }
                                    ?>
                                    </select>

                                    <div class="d-flex justify-content-between mb-2">
                                        <textarea class="form-control" name="dni_pay_details" id="dni_pay_details"
                                            rows="2" placeholder="Payment Details" <?php if ($act == '')
                                                echo 'disabled' ?>><?php
                                            if (isset($dataExisted) && isset($row['pay_details']) && !isset($dni_pay_details)) {
                                                echo $row['pay_details'];
                                            } else if (isset($dataExisted) && isset($row['pay_details']) && isset($dni_pay_details)) {
                                                echo $dni_pay_details;
                                            } ?></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <label for="payment-terms" class="mb-0">Payment Terms</label>
                                        <label class="me-0">
                                            <input type="checkbox" id="payment-terms" checked <?php if ($act == '')
                                                echo 'disabled' ?>>

                                        </label>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <select class="form-select mb-2" id="pay_terms" name="pay_terms" <?php if ($act == '')
                                            echo 'disabled' ?>>
                                                <option value="0" disabled selected>Select Payment Terms</option>
                                                <?php
                                        if ($pay_terms_result->num_rows >= 1) {
                                            $pay_terms_result->data_seek(0);
                                            while ($row3 = $pay_terms_result->fetch_assoc()) {
                                                $selected = "";
                                                if (isset($dataExisted, $row['pay_terms']) && (!isset($pay_terms))) {
                                                    $selected = $row['pay_terms'] == $row3['id'] ? "selected" : "";
                                                } else if (isset($pay_terms)) {
                                                    $selected = $pay_terms == $row3['id'] ? "selected" : "";
                                                }
                                                echo "<option value=\"" . $row3['id'] . "\" $selected>" . $row3['name'] . "</option>";
                                            }
                                        } else {
                                            echo "<option value=\"0\">None</option>";
                                        }
                                        ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
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
    </script>

</body>

<script>
    <?php include '../js/debit_inv.js'; ?>
</script>

</html>