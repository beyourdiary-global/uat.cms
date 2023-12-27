<?php
$pageTitle = "System Setting";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = PROJ;

$pinAccess = checkCurrentPin($connect, $pageTitle);

$viewOnly = (!isActionAllowed("Edit", $pinAccess)) ? 'readonly disabled' : '';

$redirect_page = $SITEURL . '/dashboard.php';

// to display data to input
$query = "SELECT * from " . $tblName . " WHERE id = 1 ";
$result = mysqli_query($connect, $query);

if (!$result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}

$row = $result->fetch_assoc();

$action = post('actionBtn');

if ($action) {

    $company_name = postSpaceFilter('company_name');
    $company_business_no = postSpaceFilter('company_business_no');
    $finance_year = postSpaceFilter('finance_year');
    $company_address = postSpaceFilter('company_address');
    $meta = postSpaceFilter('meta');
    $barcode_prefix = postSpaceFilter('barcode_prefix');
    $barcode_next_number = postSpaceFilter('barcode_next_number');
    $invoice_prefix = postSpaceFilter('invoice_prefix');
    $invoice_next_number = postSpaceFilter('invoice_next_number');

    switch ($action) {
        case 'save':
            $query = "UPDATE $tblName SET company_name='$company_name', company_business_no='$company_business_no', finance_year='$finance_year', company_address='$company_address', meta='$meta', barcode_prefix='$barcode_prefix', barcode_next_number='$barcode_next_number', invoice_prefix='$invoice_prefix', invoice_next_number='$invoice_next_number' WHERE id = '1'";

            if (isset($errCount)) {
                break;
            }

            $oldvalarr = $chgvalarr = array();

            $fields = ['company_name', 'company_business_no', 'finance_year', 'company_address', 'meta', 'barcode_prefix', 'barcode_next_number', 'invoice_prefix', 'invoice_next_number'];

            foreach ($fields as $field) {

                $postValue = postSpaceFilter($field);
            
                if ($row[$field] != $postValue) {
                    array_push($oldvalarr, $row[$field]);
                    array_push($chgvalarr, $postValue);
                }
            }
            
            $_SESSION['tempValConfirmBox'] = true;

            if ($oldvalarr && $chgvalarr) {
                $returnData = mysqli_query($connect, $query);
                $act = 'E';
                generateDBData($tblName, $connect);

                $log = [
                    'log_act'      => 'edit',
                    'cdate'        => $cdate,
                    'ctime'        => $ctime,
                    'uid'          => USER_ID,
                    'cby'          => USER_ID,
                    'query_rec'    => $query,
                    'query_table'  => $tblName,
                    'oldval'       => implodeWithComma($oldvalarr),
                    'changes'      => implodeWithComma($chgvalarr),
                    'act_msg'      => actMsgLog($oldvalarr, $chgvalarr, $tblName, (isset($returnData) ? '' : $errorMsg)),
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];
                audit_log($log);
            } else {
                $act = 'NC';
            }
            break;
    }
}

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
</head>

<body>

    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?= $pageTitle ?></p>
    </div>

    <div id="Container" class="container-fluid d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="form" method="post" action="" enctype="multipart/form-data">

                <div class="form-group mb-3">
                    <div class="col-12 col-md-10">
                        <div class="form-group mb-5">
                            <h2>
                                <?= $pageTitle ?>
                            </h2>
                        </div>
                    </div>
                </div>


                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-sm">
                            <label class="form-label" for="company_name">Company Name</label>
                            <input class="form-control" type="text" name="company_name" id="company_name" value="<?php if (isset($row['company_name'])) echo $row['company_name'] ?>" <?= $viewOnly ?>>
                        </div>

                        <div class="col-sm">
                            <label class="form-label" for="company_business_no">Company Business No</label>
                            <input class="form-control" type="text" name="company_business_no" id="company_business_no" value="<?php if (isset($row['company_business_no'])) echo $row['company_business_no'] ?>" <?= $viewOnly ?>>
                        </div>

                        <div class="col-sm">
                            <label class="form-label" for="finance_year">Finance Year</label>
                            <input class="form-control" type="date" name="finance_year" id="finance_year" value="<?php if (isset($row['finance_year'])) echo $row['finance_year'] ?>" <?= $viewOnly ?>>
                        </div>

                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-sm">
                            <label class="form-label" for="company_address">Company Address</label>
                            <textarea class="form-control" name="company_address" id="company_address" rows="3" <?= $viewOnly ?>><?php if (isset($row['company_address'])) echo $row['company_address'] ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">

                        <div class="col-sm">
                            <label class="form-label" for="meta">Meta</label>
                            <input class="form-control" type="number" step="any" name="meta" id="meta" value="<?php if (isset($row['meta'])) echo $row['meta'] ?>" <?= $viewOnly ?>>
                        </div>

                        <div class="col-sm">
                            <label class="form-label" for="barcode_prefix">Barcode Perfix</label>
                            <input class="form-control" type="text" name="barcode_prefix" id="barcode_prefix" value="<?php if (isset($row['barcode_prefix'])) echo $row['barcode_prefix'] ?>" <?= $viewOnly ?>> 
                        </div>

                        <div class="col-sm">
                            <label class="form-label" for="barcode_next_number">Barcode Next Number</label>
                            <input class="form-control" type="number" step="any" name="barcode_next_number" id="barcode_next_number" value="<?php if (isset($row['barcode_next_number'])) echo $row['barcode_next_number'] ?>" <?= $viewOnly ?>>
                        </div>

                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-sm">
                            <label class="form-label" for="invoice_prefix">Invoice Perfix</label>
                            <input class="form-control" type="text" name="invoice_prefix" id="invoice_prefix" value="<?php if (isset($row['invoice_prefix'])) echo $row['invoice_prefix'] ?>" <?= $viewOnly ?>>
                        </div>

                        <div class="col-sm">
                            <label class="form-label" for="invoice_next_number">Invoice Next Number</label>
                            <input class="form-control" type="number" step="any" name="invoice_next_number" id="invoice_next_number" value="<?php if (isset($row['invoice_next_number'])) echo $row['invoice_next_number'] ?>" <?= $viewOnly ?>>
                        </div>

                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center flex-md-row flex-column">
                            <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                <button style="background-color: <?= $row['buttonColor'] ?>;" class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="save">Save</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

<script>
    setAutofocus('E');

    centerAlignment('Container');
</script>

</html>