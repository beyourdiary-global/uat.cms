<?php
$pageTitle = "Credit Notes (Invoice)";
$isFinance = 1;

include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$tblName = CRED_NOTES_INV;
//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');

//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/cred_notes_inv_table.php';
$edit_page = $SITEURL . '/finance/cred_notes_inv.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Checking The Page ID , Action , Pin Access Exist Or Not
if (!($dataID))
    echo $redirectLink;

//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $finance_connect);

//Checking Data Error When Retrieved From Database
if (!$rst || !($row = $rst->fetch_assoc())) {
    $errorExist = 1;
    $_SESSION['tempValConfirmBox'] = true;
    $act = "F";
}

$defaultDate = date('Y-m-d');
$logo_path = $SITEURL . '/' . img_server . 'themes/';

$proj_result = getData('*', "id = '" . $row['projectID'] . "'", '', PROJ, $connect);
$curr_result = getData('*', "id = '" . $row['projectID'] . "'", '', PROJ, $connect);
$mrcht_result = getData('*', "id = '" . $row['bill_nameID'] . "'", '', MERCHANT, $finance_connect);
$pay_result = getData('*', "id = '" . $row['pay_method'] . "'", '', FIN_PAY_METH, $finance_connect);
$pic_result = getData('*', "id = '" . $row['sales_pic'] . "'", '', USR_USER, $connect);


if (!$proj_result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo $redirectLink;
}

$proj_row = $proj_result->fetch_assoc();
$curr_row = $curr_result->fetch_assoc();
$mrcht_row = $mrcht_result->fetch_assoc();
$pay_row = $pay_result->fetch_assoc();
$pic_row = $pic_result->fetch_assoc();
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
    <link rel="stylesheet" href="./css/package.css">
</head>

<body style="background-color: rgb(240, 241, 247);">
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>">
                    <?= $pageTitle ?>
                </a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                Create Invoice
            </p>
        </div>

        <div id="formContainer" class="container-fluid mt-2">
            <div class="col-12 col-md-12 formWidthAdjust">
                <form id="form" method="post" enctype="multipart/form-data">
                    <div class="form-group mb-5 hide">
                        <h2>
                            Create Invoice
                        </h2>
                    </div>
                    <div class="container-xxl flex-grow-1">
                        <div class="row invoice-add" id="invoiceArea">
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
                                        </div>
                                        <div class="col-md-5">
                                            <dl class="row mb-2">
                                                <p class="mb-2">
                                                    <span class="form_lbl">Company Business No:</span>
                                                    <?php echo $proj_row['company_business_no']; ?>
                                                </p>
                                                <p class="mb-2">
                                                    <span class="form_lbl">Email:</span>
                                                    <?php echo $proj_row['company_email']; ?>
                                                </p>
                                                <p class="mb-2">
                                                    <span class="form_lbl">Contact Number:</span>
                                                    <?php echo $proj_row['company_contact']; ?>
                                                </p>
                                            </dl>
                                        </div>
                                    </div>
                                    <hr class="my-3" />
                                    <div class="row  m-sm-4 m-0">
                                        <div class="col-md-3 mb-md-0 mb-2">
                                            <div class="col-12">
                                                <h6 class="mb-2 text-uppercase">Invoice No</h6>
                                            </div>
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($row['invoice'])) {
                                                            echo $row['invoice'];
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <h6 class="mb-2 text-uppercase">Date</h6>
                                                </div>
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($row['date'])) {
                                                            echo $row['date'];
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <h6 class="mb-2 text-uppercase">Payment Status</h6>
                                                </div>
                                                <div class='col-6'>
                                                    <button class="roundedSelectionBtn" style="font-size: 12px;">
                                                        <span class="mdi mdi-record-circle-outline" style="<?php
                                                        if ($row['payment_status'] == 'Paid') {
                                                            echo 'color:#008000;';
                                                        } else if ($row['payment_status'] == 'Cancelled') {
                                                            echo 'color:#ff0000;';
                                                        } else {
                                                            echo 'color:#F17FB5;';
                                                        } ?>"></span>
                                                        <?php
                                                        switch ($row['payment_status']) {
                                                            case 'Paid':
                                                                echo 'Paid';
                                                                break;
                                                            case 'Cancelled':
                                                                echo 'Cancelled';
                                                                break;
                                                            default:
                                                                echo 'Pending';
                                                        }
                                                        ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <h6 class="mb-2 text-uppercase">Due Date</h6>
                                                </div>
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($row['due_date'])) {
                                                            echo $row['due_date'];
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-3" />
                                    <div class="row  m-sm-4 m-0">
                                        <h6 class="mb-2 text-uppercase">Billing To:</h6>
                                        <div class="col-md-6 mb-md-0 mb-2">

                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($mrcht_row['name'])) {
                                                            echo $mrcht_row['name'];
                                                        } ?>
                                                    </p>
                                                </div>
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($row['bill_add'])) {
                                                            echo $row['bill_add'];
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="row gy-2">
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($row['bill_contact'])) {
                                                            echo $row['bill_contact'];
                                                        } ?>
                                                    </p>
                                                </div>
                                                <div class="col-12">
                                                    <p class="mb-2">
                                                        <?php if (isset($row['bill_email'])) {
                                                            echo $row['bill_email'];
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-3" />
                                    <div class="row m-sm-4 m-0">
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
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td scope="col" colspan="5" style="text-align:right">Total
                                                                Barcode
                                                            </td>
                                                            <td scope="col" id="barcode_slot_total"
                                                                style="text-align:center">
                                                                <?php
                                                                if (isset($barcode_slot_total) && $barcode_slot_total != '')
                                                                    echo $barcode_slot_total;
                                                                else {
                                                                    if (isset($dataExisted) && isset($row['barcode_slot_total']))
                                                                        echo $row['barcode_slot_total'];
                                                                    else
                                                                        echo '0';
                                                                }
                                                                ?><input name="barcode_slot_total_hidden"
                                                                    id="barcode_slot_total_hidden" type="hidden"
                                                                    value="<?php echo (isset($row['barcode_slot_total'])) ? $row['barcode_slot_total'] : ''; ?>">
                                                            </td>
                                                            <td scope="col"></td>
                                                        </tr>
                                                    </tfoot>
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
                                                                <p class="mb-2">
                                                                    <?php if (isset($pic_row['name'])) {
                                                                        echo $pic_row['name'];
                                                                    } ?>
                                                                </p>
                                                            </dd>

                                                        </dl>
                                                        <div class="form-group mb-3">
                                                            <p class="form_lbl">Remark:</p>
                                                            <p class="mb-2">
                                                                <?php if (isset($row['remark'])) {
                                                                    echo $row['remark'];
                                                                } ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-auto mb-auto col-12 col-md-4 justify-content-end">
                                                        <div class="invoice-calculations">
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Subtotal:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Discount:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="w-px-100">Tax:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                            <hr />
                                                            <div class="d-flex justify-content-between">
                                                                <span class="w-px-100">Total:</span>
                                                                <span class="fw-medium">$00.00</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <div class="form-group mb-3">
                                                <p class="form_lbl">Notes:</p>
                                                <p class="mb-2">
                                                    <?php if (isset($row['notes'])) {
                                                        echo $row['notes'];
                                                    } ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-12 invoice-actions hide mb-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <button class="btn btn-primary d-grid w-100 mb-2" data-bs-toggle="offcanvas"
                                            data-bs-target="#sendInvoiceOffcanvas">
                                            <span
                                                class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                    class="ti ti-send ti-xs me-2"></i>Send Invoice</span>
                                        </button>

                                        <a href="generate_pdf.php<?= "?id=" . $dataID . '&act=' . $act_2 ?>"
                                            class="btn btn-primary d-grid w-100 mb-2 download" name="actionBtn"
                                            id="actionBtn"><span>Print/Download</span>
                                        </a>
                                        <a href="<?= $edit_page . "?id=" . $dataID . '&act=' . $act_2 ?>"
                                            class="btn btn-primary d-grid w-100 mb-2 cancel" name="actionBtn"
                                            id="actionBtn"><span>Edit Invoice</span>
                                        </a>
                                        <a href="<?= $redirect_page ?>"
                                            class="btn btn-primary d-grid w-100 mb-2 cancel" name="actionBtn"
                                            id="actionBtn"><span>Back</span>
                                        </a>

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
    <?php include '../js/cred_inv.js'; ?>
</script>

</html>